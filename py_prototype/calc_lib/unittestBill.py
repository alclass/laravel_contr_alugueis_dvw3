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
except SystemError:
  sys.path.insert(0, '.')
  from BillMod import Bill
  from BillMod import create_billingitems_list_for_invoicebill
  from PaymentMod import Payment


class TestBill(unittest.TestCase):

  def setUp(self):
    pass

  def test_bill_payment_1(self):
    monthyeardateref = date(2018, 1, 1)
    duedate          = date(2018, 2, 10)
    billingitems = []
    billingitem  = {Bill.REFTYPE_KEY:'ALUG','value':1000}
    billingitems.append(billingitem)
    billingitem  = {Bill.REFTYPE_KEY:'COND','value':600}
    billingitems.append(billingitem)
    billingitem  = {Bill.REFTYPE_KEY:'IPTU','value':200}
    billingitems.append(billingitem)

    bill_obj = Bill(monthyeardateref=monthyeardateref, duedate=duedate, billingitems=billingitems)
    payments = []
    paydate = date(2018, 2, 5)
    payment_obj = Payment(paid_amount=1000, paydate=paydate)
    payments.append(payment_obj)
    paydate = date(2018, 2, 15)
    payment_obj = Payment(paid_amount=500, paydate=paydate)
    payments.append(payment_obj)
    bill_obj.setPayments(payments)
    bill_obj.process_payment()
    self.assertEqual(bill_obj.months_due_amount, 1000+600+200)
    self.assertEqual(bill_obj.payment_account, 1000+500)
    amount_to_fine = (1000+600+200) - 1000  # 800
    self.assertEqual(bill_obj.multa_account, 80) # ie, 10% of 800
    debt_on_day_15 = 800 * (1 + 0.1 + (0.01 + 0.005)*(15/28)) # 0.005 must be fetched (TO-DO): for the time being, it's hardcoded
    payment_missing = debt_on_day_15 - 500
    self.assertEqual(bill_obj.debt_account, payment_missing) # ie, 10% of 800





if __name__ == '__main__':
  pass

unittest.main()
