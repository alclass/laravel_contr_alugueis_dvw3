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
increase_trails = []
class AmountIncreaseTrail:

  def __init__(self, montant_ini,  interest_rate,  corrmonet_in_month,
               monthrefdate, paydate,
               daysininterest, payapplied,
               was_applied_fine = False, finevalue = None):
    self.montant_ini        = montant_ini
    self.interest_rate      = interest_rate
    self.corrmonet_in_month = corrmonet_in_month
    self.monthrefdate       = monthrefdate
    self.paydate            = None
    if paydate is not None:
      self.paydate          = paydate
    self.daysininterest     = daysininterest
    self.payapplied         = payapplied
    self.was_applied_fine   = was_applied_fine
    self.finevalue          = finevalue

  @property
  def daysinmonth(self):
    return calendar.monthrange(self.monthrefdate.year, self.monthrefdate.month)[1]

  @property
  def increaseamount(self):
    interest_rate_plus_corrmonet = self.interest_rate + self.corrmonet_in_month
    monthfraction = self.daysininterest / self.daysinmonth
    increase      = self.montant_ini * (interest_rate_plus_corrmonet * monthfraction)
    return increase


  @property
  def updatedvalue(self):
    '''
    updatedvalue does not take into consideration 'fine'
    :return:
    '''
    return self.montant_ini + self.increaseamount

  @property
  def balance(self):
    '''
    balance was once called forwardvalue
    :return:
    '''
    multa = 0
    if self.was_applied_fine:
      multa = self.finevalue
    return self.updatedvalue + multa - self.payapplied

  def __str__(self):
    fieldlist = [
      'montant_ini', 'interest_rate', 'corrmonet_in_month',
      'monthrefdate', 'paydate',
      'daysininterest', 'daysinmonth',
      'increaseamount', 'updatedvalue', 'payapplied',
      'was_applied_fine', 'finevalue', 'balance',
    ]
    datadict = {}
    for f in fieldlist:
      exec("datadict['%s'] = self.%s" %(f, f))
    monthyearref = '{0}/{1}'.format(self.monthrefdate.month, self.monthrefdate.year)
    outstr = '''
    =================
    Mês ref.: {0}
    ================='''.format(monthyearref)
    outstr += '''
    -> montant_ini        = {montant_ini:.2f}
    -> daysininterest     = {daysininterest}
    -> daysinmonth        = {daysinmonth}
    -> interest_rate      = {interest_rate:.2f}
    -> corrmonet_in_month = {corrmonet_in_month:.4f}
    -> increaseamount     = {increaseamount:.2f}'''.format(**datadict)
    outstr += '''
    ----------------------------------
     + updatedvalue      = {updatedvalue:.2f}'''.format(**datadict)
    if self.payapplied > 0:
      outstr += '''
     - payapplied        = {payapplied}
       -> paydate        = {paydate}'''.format(**datadict)
    if self.was_applied_fine:
      outstr += '''
     + finevalue (incid) = {finevalue}'''.format(**datadict)
    outstr += '''
    ----------------------------------
    balance              = {balance:.2f}
    ----------------------------------
    '''.format(**datadict)
    return outstr

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
inmonthdue    = 3700
multa_account = inmonthdue * 0.1
debt_account  = 6700

add_multa_first_time = True
restart_mora_date = date(2017, 2, 1)
def process_payments():
  global debt_account
  if len(payments_list) == 0:
    print ('END')
    return
  paytuple = payments_list[0]
  del payments_list[0]
  outlist.append(paytuple)
  print ('paytuple => ', paytuple)
  print ('debt_account => ', debt_account)

  paydate = paytuple[0]
  paid_amount = paytuple[1]
  if paydate <= bill_dict['duedate']:
    debt_account -= paid_amount
    return process_payments()
  # at this point in program flow, all payments are late (this is because payments are ordered by date ascending)
  pay_recursively_consuming_either_month_or_pay(paydate, paid_amount)

  # recurse from here
  return process_payments()


def are_restart_mora_date_and_paydate_in_the_same_month(restart_mora_date, paydate):
  if restart_mora_date.year == paydate.year:
    if restart_mora_date.month == paydate.month:
      return True
  return False

seq = 0
def pay_recursively_consuming_either_month_or_pay(paydate, paid_amount):
  global debt_account
  global add_multa_first_time
  global restart_mora_date
  global seq

  if paydate < restart_mora_date:
    raise ValueError('paydate_as_monthref_with_day1 < restart_mora_date_monthref')

  if are_restart_mora_date_and_paydate_in_the_same_month(restart_mora_date, paydate):
    daysinmonth = calendar.monthrange(paydate.year, paydate.month)[1]
    daysininterest = paydate.day - restart_mora_date.day + 1
    monthfraction = daysininterest / daysinmonth
    corrmonet_monthrefdate = paydate - relativedelta(months=+1)
    corrmonet_monthrefdate = corrmonet_monthrefdate.replace(day=1)
    corrmonet = corr_monet_month_dict[corrmonet_monthrefdate]
    montant_ini = debt_account
    debt_increase = debt_account * ((interest_rate + corrmonet) * monthfraction)
    debt_account += debt_increase
    restart_mora_date = paydate + relativedelta(days=+1)
    seq += 1
    print('seq =>', seq)
    multa_was_applied = False; multa_trail = None
    if add_multa_first_time:
      debt_account += multa_account
      multa_trail = multa_account
      add_multa_first_time = False
      multa_was_applied = True
      print(' :: multa =>', multa_account)
    print(' :: restart_mora_date =>', restart_mora_date)
    debt_account -= paid_amount
    paid_amount_trail = paid_amount
    monthrefdate = corrmonet_monthrefdate + relativedelta(months=+1)
    print('monthref =>', monthrefdate, ' days =>', daysininterest)
    print('debt_account => ', debt_account, 'debt_increase =>', debt_increase)
    ait = AmountIncreaseTrail(
      montant_ini        = montant_ini,
      interest_rate      = interest_rate,
      corrmonet_in_month = corrmonet,
      monthrefdate       = monthrefdate,
      paydate            = paydate,
      daysininterest     = daysininterest,
      payapplied         = paid_amount_trail,
      was_applied_fine   = multa_was_applied,
      finevalue          = multa_trail
    )
    increase_trails.append(ait)
    return

  daysinmonth = calendar.monthrange(restart_mora_date.year, restart_mora_date.month)[1]
  # remaining days in month
  daysininterest = daysinmonth - restart_mora_date.day + 1
  monthfraction  = daysininterest / daysinmonth
  monthrefdate   = restart_mora_date.replace(day=1)
  corrmonet_monthrefdate = restart_mora_date - relativedelta(months=+1)
  corrmonet_monthrefdate = corrmonet_monthrefdate.replace(day=1)
  corrmonet   = corr_monet_month_dict[corrmonet_monthrefdate]
  montant_ini = debt_account
  debt_increase = debt_account * ((interest_rate + corrmonet) * monthfraction)
  debt_account += debt_increase
  paid_amount_trail = 0
  restart_mora_date = restart_mora_date + relativedelta(months=+1)
  restart_mora_date = restart_mora_date.replace(day=1)
  multa_was_applied = False; multa_trail = None
  if add_multa_first_time:
    debt_account += multa_account
    multa_trail   = multa_account
    add_multa_first_time = False
    multa_was_applied    = True
    print(' :: multa =>', multa_account)
  print (' :: restart_mora_date =>', restart_mora_date)
  # payment not yet counterplaced, so recurse on until pay's month is the same as restart_mora_date's month
  seq += 1
  print('seq =>', seq)
  print('monthref =>', monthrefdate, ' days =>', daysininterest)
  print('debt_account => ', debt_account, 'debt_increase =>', debt_increase)
  ait = AmountIncreaseTrail(
    montant_ini        = montant_ini,
    interest_rate      = interest_rate,
    corrmonet_in_month = corrmonet,
    monthrefdate       = monthrefdate,
    paydate            = None,
    daysininterest     = daysininterest,
    payapplied         = paid_amount_trail,
    was_applied_fine   = multa_was_applied,
    finevalue          = multa_trail
  )
  increase_trails.append(ait)
  return pay_recursively_consuming_either_month_or_pay(paydate, paid_amount)

def calculate_end_of_month_debt(restart_mora_date, balance):
  daysinmonth = calendar.monthrange(restart_mora_date.year, restart_mora_date.month)[1]
  # remaining days in month
  daysininterest = daysinmonth - restart_mora_date.day + 1
  monthfraction  = daysininterest / daysinmonth
  monthrefdate   = restart_mora_date.replace(day=1)
  corrmonet_monthrefdate = restart_mora_date - relativedelta(months=+1)
  corrmonet_monthrefdate = corrmonet_monthrefdate.replace(day=1)
  corrmonet   = corr_monet_month_dict[corrmonet_monthrefdate]
  debt_account = balance
  montant_ini = debt_account
  debt_increase = debt_account * ((interest_rate + corrmonet) * monthfraction)
  debt_account += debt_increase
  monthsenddate = restart_mora_date.replace(day=daysinmonth)
  print('debt_account =>', debt_account, 'on', monthsenddate)



if __name__ == '__main__':
  process_payments()
  print (outlist)
  for ait in increase_trails:
    print (ait)
  print ('restart_mora_date =>', restart_mora_date)
  calculate_end_of_month_debt(restart_mora_date, ait.balance)