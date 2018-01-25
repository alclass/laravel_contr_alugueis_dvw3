#!/usr/bin/env python3
from copy import copy
from datetime import date
from datetime import timedelta
from dateutil.relativedelta import relativedelta
import unittest
#import calendar

import sys

try:
  from .BillMod import Bill
  from .BillMod import create_billingitems_list_for_invoicebill
  from .PaymentMod import Payment
  from .juros_calculator import Juros
except SystemError:
  sys.path.insert(0, '.')
  from BillMod import Bill
  from BillMod import create_billingitems_list_for_invoicebill
  from PaymentMod import Payment
  from juros_calculator import Juros

def test_bill_payment_1():
  monthrefdate = date(2017, 12, 1)
  #duedate          = date(2018, 1, 10)
  duedate = None
  billingitems = []
  billingitem  = {Bill.REFTYPE_KEY:'ALUG','value':3000}
  billingitems.append(billingitem)
  billingitem  = {Bill.REFTYPE_KEY:'COND','value':1300}
  billingitems.append(billingitem)
  billingitem  = {Bill.REFTYPE_KEY:'IPTU','value':300}
  billingitems.append(billingitem)

  bill_obj = Bill(monthrefdate=monthrefdate, duedate=duedate, billingitems=billingitems)
  payments = []
  paydate = date(2018, 1, 27)
  paydate_frozen = copy(paydate)
  payment_obj = Payment(paid_amount=3000, paydate=paydate)
  payments.append(payment_obj)
  #paydate = date(2018, 1, 27)
  payment_obj = Payment(paid_amount=1600, paydate=paydate)
  payments.append(payment_obj)
  bill_obj.setPayments(payments)
  bill_obj.process_payment()

  print(bill_obj)

def test_bill_payment_2():
  monthrefdate = date(2017, 11, 1)
  # duedate          = date(2018, 1, 10)
  duedate = None
  alug = 3000;
  cond = 1300;
  iptu = 300
  inmonth_due = alug + cond + iptu
  billingitems = []
  billingitem = {Bill.REFTYPE_KEY: 'ALUG', 'value': alug}
  billingitems.append(billingitem)
  billingitem = {Bill.REFTYPE_KEY: 'COND', 'value': cond}
  billingitems.append(billingitem)
  billingitem = {Bill.REFTYPE_KEY: 'IPTU', 'value': iptu}
  billingitems.append(billingitem)

  bill_obj = Bill(monthrefdate=monthrefdate, duedate=duedate, billingitems=billingitems)
  previousmonthsdebts = 1255.00
  bill_obj.set_previousmonthsdebts(previousmonthsdebts=previousmonthsdebts)
  payments = []
  paydate = date(2017, 12, 10)
  # paydate_frozen = copy(paydate)
  firstpayondateamount = inmonth_due + previousmonthsdebts
  payment_obj = Payment(paid_amount=firstpayondateamount, paydate=paydate)
  payments.append(payment_obj)
  paydatelate = date(2017, 12, 11)
  secondpaylateamount = 100
  payment_obj = Payment(paid_amount=secondpaylateamount, paydate=paydatelate)
  payments.append(payment_obj)
  bill_obj.setPayments(payments)
  bill_obj.process_payment()

  print(bill_obj)

def test_bill_payment_5():
  '''
  Here TWO payments are done on the same late date
  :return:
  '''
  monthrefdate = date(2017, 1, 1)
  # duedate          = date(2018, 1, 10)
  duedate = None
  alug = 2500;
  cond = 950;
  iptu = 250
  inmonth_due = alug + cond + iptu
  billingitems = []
  billingitem = {Bill.REFTYPE_KEY: 'ALUG', 'value': alug}
  billingitems.append(billingitem)
  billingitem = {Bill.REFTYPE_KEY: 'COND', 'value': cond}
  billingitems.append(billingitem)
  billingitem = {Bill.REFTYPE_KEY: 'IPTU', 'value': iptu}
  billingitems.append(billingitem)

  bill_obj = Bill(monthrefdate=monthrefdate, duedate=duedate, billingitems=billingitems)
  previousmonthsdebts = 3000.00
  bill_obj.set_previousmonthsdebts(previousmonthsdebts=previousmonthsdebts)
  payments = []
  paid_amount = 0
  paydate1 = date(2017, 4, 10)
  payamount1 = 3000
  paid_amount += payamount1
  payment_obj = Payment(paid_amount=payamount1, paydate=paydate1)
  payments.append(payment_obj)
  paydate2 = date(2017, 5, 10)
  payamount2 = 1000
  paid_amount += payamount2
  payment_obj = Payment(paid_amount=payamount2, paydate=paydate2)
  payments.append(payment_obj)
  paydate3 = date(2017, 6, 3)
  payamount3 = 500
  paid_amount += payamount3
  payment_obj = Payment(paid_amount=payamount3, paydate=paydate3)
  payments.append(payment_obj)
  bill_obj.setPayments(payments)
  bill_obj.process_payment()

  print(bill_obj)


if __name__ == '__main__':
  test_bill_payment_5()