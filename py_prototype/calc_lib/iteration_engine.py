#!/usr/bin/env python3
'''
Iteration engine is a routine that is capable of 'iteratively' process a fragmented payments with various pays.
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
]

#print (bill_dict)
#print (payments_list)

outlist = []
inmonthdue = 3700
multa_account = inmonthdue * 0.1
debt_account = 6700

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
  pay_exhausting_inbetween_time(paydate, paid_amount)

  # recurse from here
  return process_payments()


def are_restart_mora_date_and_paydate_in_the_same_month(restart_mora_date, paydate):
  if restart_mora_date.year == paydate.year:
    if restart_mora_date.month == paydate.month:
      return True
  return False

def pay_exhausting_inbetween_time(paydate, paid_amount):
  global debt_account
  global add_multa_first_time
  global restart_mora_date

  if paydate < restart_mora_date:
    raise ValueError('paydate_as_monthref_with_day1 < restart_mora_date_monthref')

  if are_restart_mora_date_and_paydate_in_the_same_month(restart_mora_date, paydate):
    daysinmonth = calendar.monthrange(paydate.year, paydate.month)[1]
    monthfraction = (paydate.day - restart_mora_date.day + 1) / daysinmonth
    corrmonet_monthrefdate = paydate - relativedelta(months=+1)
    corrmonet_monthrefdate = corrmonet_monthrefdate.replace(day=1)
    corrmonet = corr_monet_month_dict[corrmonet_monthrefdate]
    debt_account += debt_account * ((interest_rate+corrmonet)*monthfraction)
    restart_mora_date = paydate + relativedelta(days=+1)
    if add_multa_first_time:
      debt_account += multa_account
      add_multa_first_time = False
    debt_account -= paid_amount
    print('debt_account => ', debt_account)
    return

  daysinmonth = calendar.monthrange(restart_mora_date.year, restart_mora_date.month)[1]
  # remaining days in month
  daysininterest = daysinmonth - restart_mora_date.day + 1
  monthfraction = daysininterest / daysinmonth
  corrmonet_monthrefdate = restart_mora_date - relativedelta(months=+1)
  corrmonet_monthrefdate = corrmonet_monthrefdate.replace(day=1)
  corrmonet = corr_monet_month_dict[corrmonet_monthrefdate]
  debt_account += debt_account * ((interest_rate + corrmonet) * monthfraction)
  restart_mora_date = restart_mora_date + relativedelta(months=+1)
  restart_mora_date = restart_mora_date.replace(day=1)
  if add_multa_first_time:
    debt_account += multa_account
    add_multa_first_time = False
  # payment not yet counterplaced, so recurse on until pay's month is the same as restart_mora_date's month
  print('debt_account => ', debt_account)
  return pay_exhausting_inbetween_time(paydate, paid_amount)


if __name__ == '__main__':
  process_payments()
  print (outlist)