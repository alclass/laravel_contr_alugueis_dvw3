#!/usr/bin/env python3
'''
Iteration engine is a routine that is capable of processing 'iteratively'
 a fragmented payments set with various pays along various months.
'''

from copy import copy
from datetime import date
# from datetime import timedelta
from dateutil.relativedelta import relativedelta
import sys
import calendar # calendar.monthrange(year, month)
# from .DateBillCalculatorMod import DateBillCalculator

try:
  from .AmountIncreaseTrailMod import AmountIncreaseTrail
except SystemError:
  from AmountIncreaseTrailMod import AmountIncreaseTrail

# =======================
# DATA AREA
# =======================

bill_dict = {
  'inmonthdue'   : 3700,
  'previousdebts': 3000,
  'monthrefdate' : date(2017, 1, 1),
  'duedate'      : date(2017, 2, 10),
}
corr_monet_months = [0.01, 0.003, 0.002, 0.003, 0.004, ]
corr_monet_month_dict = {
  date(2016, 12, 1) : 0.01,
  date(2017, 1, 1) : 0.01,
  date(2017, 2, 1) : 0.003,
  date(2017, 3, 1) : 0.002,
  date(2017, 4, 1) : 0.003,
  date(2017, 5, 1) : 0.004,
  # -------------------------
  date(2017, 6, 1): 0.004,
  date(2017, 7, 1): 0.004,
}

interest_rate = 0.01

payments_list = [
  (date(2017, 4, 10) , 3000),
  (date(2017, 5, 10) , 1000),
  (date(2017, 6,  3) ,  500),
#  (date(2017, 6, 30),    2951),

]

def extract_lastmonthsdate_from(fromdate):
  if fromdate is None:
    return None
  daysinmonth = calendar.monthrange(fromdate.year, fromdate.month)[1]
  return fromdate.replace(day=daysinmonth)


class PaymentTimeProcessor:

  def __init__(self):
    self.debt_account      = 0
    self.restart_mora_date = None # its first value is monthrefdate + 1month
    self.add_multa_first_time = True
    self.seq = 0
    self.increase_trails = []

  def set_payment_tuplelist(self, payments_list):
    self.payments_list = payments_list

  def set_bill_dict(self, bill_dict):
    '''
      'inmonthdue': 3700,
      'previousdebts': 3000,
      'monthrefdate': date(2017, 1, 1),
      'duedate': date(2017, 2, 10),

    :param bill_dict:
    :return:
    '''
    self.bill_dict = bill_dict
    monthrefdate = self.bill_dict['monthrefdate']
    self.restart_mora_date = monthrefdate + relativedelta(months=1)
    self.duedate    = self.bill_dict['duedate']
    self.inmonthdue = self.bill_dict['inmonthdue']
    self.previousdebts = self.bill_dict['previousdebts']
    # the initialization of debt_account is the sum of inmonthdue plus previousdebts
    self.debt_account = self.inmonthdue + self.previousdebts
    # the multa_account is a buffer, it will only be passed to an 'ait' if fine applies
    self.multa_account = self.inmonthdue * 0.1

  @property
  def original_monthrefdate(self):
    if 'monthrefdate' in self.bill_dict:
      return self.bill_dict['monthrefdate']
    return None

  def process_payments(self):

    while len(self.payments_list) > 0:
      paytuple = self.payments_list[0]
      del self.payments_list[0]
      print('paytuple => ', paytuple)
      print('debt_account => ', self.debt_account)

      paydate = paytuple[0]
      paid_amount = paytuple[1]
      # paydates must loop in cronological ascending order or inconsistencies will arise
      if paydate <= self.bill_dict['duedate']:
        self.debt_account -= paid_amount
      else:
        break
    # at this point in program flow, all payments are late (this is because payments are ordered by date ascending)
    self.restart_mora_date = self.original_monthrefdate + relativedelta(months=+1)
    while len(self.payments_list) > 0:
      paytuple = self.payments_list[0]
      del self.payments_list[0]
      print('paytuple => ', paytuple)
      print('debt_account => ', self.debt_account)

      paydate = paytuple[0]
      paid_amount = paytuple[1]
      self.pay_late_generating_ait_records(paydate, paid_amount)
    print('END of process payments.')

  def are_dates_in_the_same_yearmonth(self, restart_mora_date, paydate):
    if restart_mora_date.year == paydate.year:
      if restart_mora_date.month == paydate.month:
        return True
    return False

  def project_debt_to_date_n_return_ait(self, untildate):
    print('seq =>', self.seq + 1)
    restart_timerange_date = copy(self.restart_mora_date)
    end_timerange_date = None # until next 'if'
    if untildate is not None:
      end_timerange_date = untildate
    else:
      lastdayinmonth = calendar.monthrange(restart_timerange_date.year, restart_timerange_date.month)[1]
      end_timerange_date = restart_timerange_date.replace(day=lastdayinmonth)
    ongoing_monthrefdate = end_timerange_date.replace(day=1)
    # the monetary correction index is the M-1 one
    corrmonet_monthrefdate = ongoing_monthrefdate - relativedelta(months=+1)
    corrmonet   = corr_monet_month_dict[corrmonet_monthrefdate]
    montant_ini = self.debt_account
    multa_value_for_trail = None
    if self.add_multa_first_time:
      multa_value_for_trail     = self.multa_account
      self.add_multa_first_time = False
      print(' :: multa =>', self.multa_account)
    print(' :: restart_mora_date =>', self.restart_mora_date)
    paid_amount_for_trail = 0
    ait = AmountIncreaseTrail(
      montant_ini=montant_ini,
      monthrefdate=self.original_monthrefdate,
      restart_timerange_date=restart_timerange_date,
      end_timerange_date=end_timerange_date,
      interest_rate=interest_rate,
      corrmonet_in_month=corrmonet,
      paid_amount=paid_amount_for_trail,
      finevalue=multa_value_for_trail
    )
    return ait


  def apply_interestcm_according_to_pay_or_monthend_n_return_ait(self, pay_or_end_date=None, paid_amount=0):

    self.seq += 1
    print('seq =>', self.seq)
    restart_timerange_date = copy(self.restart_mora_date)
    end_timerange_date = None # until next 'if'
    if pay_or_end_date is not None:
      end_timerange_date = pay_or_end_date
    else:
      lastdayinmonth = calendar.monthrange(restart_timerange_date.year, restart_timerange_date.month)[1]
      end_timerange_date = restart_timerange_date.replace(day=lastdayinmonth)
    ongoing_monthrefdate = end_timerange_date.replace(day=1)
    # the monetary correction index is the M-1 one
    corrmonet_monthrefdate = ongoing_monthrefdate - relativedelta(months=+1)
    corrmonet   = corr_monet_month_dict[corrmonet_monthrefdate]
    montant_ini = self.debt_account
    multa_value_for_trail = None
    if self.add_multa_first_time:
      multa_value_for_trail     = self.multa_account
      self.add_multa_first_time = False
      print(' :: multa =>', self.multa_account)
    print(' :: restart_mora_date =>', self.restart_mora_date)
    paid_amount_for_trail = paid_amount
    ait = AmountIncreaseTrail(
      montant_ini=montant_ini,
      monthrefdate=self.original_monthrefdate,
      restart_timerange_date=restart_timerange_date,
      end_timerange_date=end_timerange_date,
      interest_rate=interest_rate,
      corrmonet_in_month=corrmonet,
      paid_amount=paid_amount_for_trail,
      finevalue=multa_value_for_trail
    )
    self.debt_account = ait.balance
    self.restart_mora_date = end_timerange_date + relativedelta(days=+1)
    return ait

  def pay_late_generating_ait_records(self, paydate, paid_amount):

    if paydate < self.restart_mora_date:
      error_msg = 'paydate (%s) < restart_mora_date (%s) \n' %(paydate, self.restart_mora_date)
      error_msg += 'It is possible that paydates were unordered, an error in the input system elsewhere. Or a logical error exists in class PaymentTimeProcessor.'
      raise ValueError(error_msg)

    # This while below runs until 'restart_mora_date' and 'paydate' are in the same month
    while not self.are_dates_in_the_same_yearmonth(
        self.restart_mora_date,
        paydate
      ):
      ait = self.apply_interestcm_according_to_pay_or_monthend_n_return_ait()
      self.increase_trails.append(ait)

    if self.are_dates_in_the_same_yearmonth(
        self.restart_mora_date,
        paydate
      ):
      ait = self.apply_interestcm_according_to_pay_or_monthend_n_return_ait(paydate, paid_amount)
      self.increase_trails.append(ait)

  def check_trail_debt_account_n_raise_exception_if_inconsistent(self, last_increase_trail):
    '''
    Only TWO attributes need to be made equal
    :param last_increase_trail:
    :return:
    '''
    if self.debt_account != last_increase_trail.balance:
      error_msg = 'debt_account (%f) != last_increase_trail.debt_account (%f)' %(
        self.debt_account,
        last_increase_trail.debt_account
      )
      raise ValueError(error_msg)
    if self.restart_mora_date != last_increase_trail.restart_mora_date:
      error_msg = 'restart_mora_date (%s) != last_increase_trail.restart_mora_date (%s)' %(
        self.restart_mora_date,
        last_increase_trail.restart_mora_date
      )
      raise ValueError(error_msg)

  def set_payment_tuplelist(self, payments_list):
    self.payments_list = payments_list

  def calculate_debt_ondate_n_return_ait(self, untildate=None):
    '''

    :return:
    '''

    if len(self.increase_trails) == 0:
      print('Nothing to update in update_balance_from_increase_trails_end(); empty list self.increase_trails')
      return None

    last_increase_trail = self.increase_trails[-1]
    self.check_trail_debt_account_n_raise_exception_if_inconsistent(last_increase_trail)

    if untildate is None:
      untildate = last_increase_trail.extract_lastdayofmonth_of_enddate()
      if untildate == last_increase_trail.end_timerange_date:
        # no need to update trail, it's already at the required point
        return last_increase_trail

    # from here on, untildate exists and is not equal to or less than paydate_or_restart_date, ie, it's more than the latter
    ait = self.project_debt_to_date_n_return_ait(untildate)
    return ait

  def print_increase_trails(self):
    for ait in self.increase_trails:
      print (ait)


if __name__ == '__main__':
  ptp = PaymentTimeProcessor()
  ptp.set_bill_dict(bill_dict)
  ptp.set_payment_tuplelist(payments_list)
  ptp.process_payments()
  ptp.print_increase_trails()
  ait = ptp.calculate_debt_ondate_n_return_ait()
  print('projected debt =>', ait)
  ait = ptp.calculate_debt_ondate_n_return_ait(date(2017,6,15))
  print('projected debt =>', ait)
  outtext = ''
  for day in range(4, 31):
    ondate = date(2017,6,day)
    ait = ptp.calculate_debt_ondate_n_return_ait(ondate)
    outtext += '{0} => Saldo = {1:.2f}\n'.format(ondate, ait.balance)
  print(outtext)