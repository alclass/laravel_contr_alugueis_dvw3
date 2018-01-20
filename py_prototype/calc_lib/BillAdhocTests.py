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

def test_bill_payment_2():
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

if __name__ == '__main__':
  test_bill_payment_2()