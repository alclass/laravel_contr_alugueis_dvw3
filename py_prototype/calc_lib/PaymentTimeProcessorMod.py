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

'''
data for a unit test
'''


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
  date(2017, 1, 1) : 0.01,
  date(2017, 2, 1) : 0.003,
  date(2017, 3, 1) : 0.002,
  date(2017, 4, 1) : 0.003,
  date(2017, 5, 1) : 0.004,
}

interest_rate = 0.01

payments_list = [
  (date(2017, 4, 10) , 3000),
  (date(2017, 5, 10) , 1000),
  (date(2017, 6,  3) ,  500),
#  (date(2017, 6, 30),    2951),

]

#print (bill_dict)
#print (payments_list)

outlist = []


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
    self.multa_account = self.inmonthdue * 0.1

  def recursively_process_payments(self):
    if len(self.payments_list) == 0:
      print ('END')
      return
    paytuple = self.payments_list[0]
    del self.payments_list[0]
    outlist.append(paytuple)
    print ('paytuple => ', paytuple)
    print ('debt_account => ', self.debt_account)

    paydate = paytuple[0]
    paid_amount = paytuple[1]
    if paydate <= self.bill_dict['duedate']:
      self.debt_account -= paid_amount
      return self.recursively_process_payments()
    # at this point in program flow, all payments are late (this is because payments are ordered by date ascending)
    self.pay_recursively_consuming_either_month_or_pay(paydate, paid_amount)

    # recurse from here
    return self.recursively_process_payments()


  def are_restart_mora_date_and_paydate_in_the_same_month(self, restart_mora_date, paydate):
    if restart_mora_date.year == paydate.year:
      if restart_mora_date.month == paydate.month:
        return True
    return False

  seq = 0
  def pay_recursively_consuming_either_month_or_pay(self, paydate, paid_amount):

    if paydate < self.restart_mora_date:
      raise ValueError('paydate_as_monthref_with_day1 < restart_mora_date_monthref')

    if self.are_restart_mora_date_and_paydate_in_the_same_month(
        self.restart_mora_date,
        paydate
      ):
      daysinmonth = calendar.monthrange(paydate.year, paydate.month)[1]
      daysininterest = paydate.day - self.restart_mora_date.day + 1
      monthfraction = daysininterest / daysinmonth
      corrmonet_monthrefdate = paydate - relativedelta(months=+1)
      corrmonet_monthrefdate = corrmonet_monthrefdate.replace(day=1)
      corrmonet = corr_monet_month_dict[corrmonet_monthrefdate]
      montant_ini = self.debt_account
      debt_increase = self.debt_account * ((interest_rate + corrmonet) * monthfraction)
      self.debt_account += debt_increase
      self.restart_mora_date = paydate + relativedelta(days=+1)
      self.seq += 1
      print('seq =>', self.seq)
      multa_value_for_trail = None
      if self.add_multa_first_time:
        self.debt_account += self.multa_account
        multa_value_for_trail = self.multa_account
        self.add_multa_first_time = False
        print(' :: multa =>', self.multa_account)
      print(' :: restart_mora_date =>', self.restart_mora_date)
      self.debt_account -= paid_amount
      paid_amount_for_trail = paid_amount
      monthrefdate = corrmonet_monthrefdate + relativedelta(months=+1)
      print('monthref =>', monthrefdate, ' days =>', daysininterest)
      print('debt_account => ', self.debt_account, 'debt_increase =>', debt_increase)
      ait = AmountIncreaseTrail(
        montant_ini        = montant_ini,
        interest_rate      = interest_rate,
        corrmonet_in_month = corrmonet,
        monthrefdate       = monthrefdate,
        paydate            = paydate,
        daysininterest     = daysininterest,
        payapplied         = paid_amount_for_trail,
        finevalue          = multa_value_for_trail
      )
      self.increase_trails.append(ait)
      return

    daysinmonth = calendar.monthrange(self.restart_mora_date.year, self.restart_mora_date.month)[1]
    # remaining days in month
    daysininterest = daysinmonth - self.restart_mora_date.day + 1
    monthfraction  = daysininterest / daysinmonth
    monthrefdate   = self.restart_mora_date.replace(day=1)
    corrmonet_monthrefdate = self.restart_mora_date - relativedelta(months=+1)
    corrmonet_monthrefdate = corrmonet_monthrefdate.replace(day=1)
    corrmonet   = corr_monet_month_dict[corrmonet_monthrefdate]
    montant_ini = self.debt_account
    debt_increase = self.debt_account * ((interest_rate + corrmonet) * monthfraction)
    self.debt_account += debt_increase
    paid_amount_for_trail = 0
    self.restart_mora_date = self.restart_mora_date + relativedelta(months=+1)
    self.restart_mora_date = self.restart_mora_date.replace(day=1)
    multa_value_for_trail = None
    if self.add_multa_first_time:
      self.debt_account += self.multa_account
      multa_value_for_trail = self.multa_account
      add_multa_first_time = False
      print(' :: multa =>', self.multa_account)
    print (' :: restart_mora_date =>', self.restart_mora_date)
    # payment not yet counterplaced, so recurse on until pay's month is the same as restart_mora_date's month
    self.seq += 1
    print('seq =>', self.seq)
    print('monthref =>', monthrefdate, ' days =>', daysininterest)
    print('debt_account => ', self.debt_account, 'debt_increase =>', debt_increase)
    ait = AmountIncreaseTrail(
      montant_ini        = montant_ini,
      interest_rate      = interest_rate,
      corrmonet_in_month = corrmonet,
      monthrefdate       = monthrefdate,
      paydate            = None,
      daysininterest     = daysininterest,
      payapplied         = paid_amount_for_trail,
      finevalue          = multa_value_for_trail
    )
    self.increase_trails.append(ait)
    return self.pay_recursively_consuming_either_month_or_pay(paydate, paid_amount)

  def calculate_end_of_month_debt(self):
    '''
        fieldlist = [
      'montant_ini', 'interest_rate', 'corrmonet_in_month',
      'monthrefdate', 'paydate',
      'daysininterest', 'daysinmonth',
      'increaseamount', 'updatedvalue', 'payapplied',
      'was_fine_applied', 'finevalue', 'balance',
    ]
    :return:
    '''
    last_increase_trail = self.increase_trails[-1]
    restart_mora_date = last_increase_trail
    daysinmonth = calendar.monthrange(self.restart_mora_date.year, self.restart_mora_date.month)[1]
    # remaining days in month
    daysininterest = daysinmonth - self.restart_mora_date.day + 1
    monthfraction  = daysininterest / daysinmonth
    monthrefdate   = self.restart_mora_date.replace(day=1)
    corrmonet_monthrefdate = self.restart_mora_date - relativedelta(months=+1)
    corrmonet_monthrefdate = corrmonet_monthrefdate.replace(day=1)
    corrmonet   = corr_monet_month_dict[corrmonet_monthrefdate]
    projected_debt_account = balance
    montant_ini = projected_debt_account
    debt_increase = debt_account * ((interest_rate + corrmonet) * monthfraction)
    debt_account += debt_increase
    monthsenddate = self.restart_mora_date.replace(day=daysinmonth)
    print('debt_account =>', debt_account, 'on', monthsenddate)


  def print_increase_trails(self):
    for ait in self.increase_trails:
      print (ait)


if __name__ == '__main__':
  ptp = PaymentTimeProcessor()
  ptp.set_bill_dict(bill_dict)
  ptp.set_payment_tuplelist(payments_list)
  ptp.recursively_process_payments()
  # print (outlist)
  ptp.print_increase_trails()
  ptp.calculate_end_of_month_debt()
