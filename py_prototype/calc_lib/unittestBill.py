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


class TestBill(unittest.TestCase):

  def setUp(self):
    pass

  def test_bill_payment_1(self):
    monthrefdate = date(2018, 1, 1)
    duedate          = date(2018, 2, 10)
    billingitems = []
    billingitem  = {Bill.REFTYPE_KEY:'ALUG','value':1000}
    billingitems.append(billingitem)
    billingitem  = {Bill.REFTYPE_KEY:'COND','value':600}
    billingitems.append(billingitem)
    billingitem  = {Bill.REFTYPE_KEY:'IPTU','value':200}
    billingitems.append(billingitem)

    bill_obj = Bill(monthrefdate=monthrefdate, duedate=duedate, billingitems=billingitems)
    payments = []
    paydate = date(2018, 2, 5)
    payment_obj = Payment(paid_amount=1000, paydate=paydate)
    payments.append(payment_obj)
    paydate = date(2018, 2, 15)
    paydate_frozen = copy(paydate)
    payment_obj = Payment(paid_amount=500, paydate=paydate)
    payments.append(payment_obj)
    bill_obj.setPayments(payments)
    bill_obj.process_payment()

    # inmonth_due_amount
    self.assertEqual(bill_obj.inmonth_due_amount, 1000 + 600 + 200)
    # inmonthpluspreviousdebts
    self.assertEqual(bill_obj.inmonthpluspreviousdebts, 1000 + 600 + 200)
    # payment_account
    self.assertEqual(bill_obj.payment_account, 1000+500)
    # inmonthpluspreviousdebts_minus_payments
    expected_inmonthplusdebts_minus_payments = 1000 + 600 + 200 - (1000 + 500)
    self.assertEqual(bill_obj.inmonthpluspreviousdebts_minus_payments, expected_inmonthplusdebts_minus_payments)

    # inmonthpluspreviousdebts_minus_payments
    amount_to_fine = (1000+600+200) - 1000  # 800
    fine_value = amount_to_fine * 0.1
    self.assertEqual(bill_obj.multa_account, fine_value) # 80 ie 10% of 800

    # debt_account, after processing, is payment_missing
    # corr_monet considers 2018-jan not february

    paydate_minus_one_month = paydate_frozen - relativedelta(months=1)
    corrmonet = Juros.fetch_corrmonet_for_month(paydate_minus_one_month) # corr_monet considers 2018-jan not february
    # print ('corrmonet paydate_frozen', paydate_frozen, paydate_minus_one_month, corrmonet)
    monthdaysfraction = 15/28 # monthdaysfraction considers february not january
    interest_n_cm_on_day_15 = amount_to_fine * ((0.01 + corrmonet) * monthdaysfraction) # 0.005 must be fetched (TO-DO): for the time being, it's hardcoded
    payment_missing = amount_to_fine + fine_value + interest_n_cm_on_day_15 - 500
    self.assertEqual(bill_obj.debt_account, payment_missing)

  def test_bill_payment_2(self):
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

    # inmonth_due_amount
    inmonth_due = 3000 + 1300 + 300
    self.assertEqual(bill_obj.inmonth_due_amount, inmonth_due)
    # inmonthpluspreviousdebts
    self.assertEqual(bill_obj.inmonthpluspreviousdebts, inmonth_due)
    # payment_account
    paid_amount = 3000 + 1600
    self.assertEqual(bill_obj.payment_account, paid_amount)
    # inmonthpluspreviousdebts_minus_payments
    expected_inmonthplusdebts_minus_payments = inmonth_due - paid_amount
    self.assertEqual(bill_obj.inmonthpluspreviousdebts_minus_payments, expected_inmonthplusdebts_minus_payments)

    # inmonthpluspreviousdebts_minus_payments
    amount_to_fine = inmonth_due  # 800
    fine_value = amount_to_fine * 0.1
    self.assertEqual(bill_obj.multa_account, fine_value) # 80 ie 10% of 800

    # debt_account, after processing, is payment_missing
    paydate_minus_one_month = paydate_frozen - relativedelta(months=1)
    corrmonet = Juros.fetch_corrmonet_for_month(paydate_minus_one_month) # corr_monet considers 2018-jan not february
    print ('corrmonet paydate_frozen', paydate_frozen, paydate_minus_one_month, corrmonet)
    monthdaysfraction = 27/31 # monthdaysfraction considers february not january
    interest_n_cm_on_payday = amount_to_fine * ((0.01 + corrmonet) * monthdaysfraction) # 0.005 must be fetched (TO-DO): for the time being, it's hardcoded
    payment_missing = amount_to_fine + fine_value + interest_n_cm_on_payday - paid_amount
    print ('payment_missing', payment_missing)
    self.assertEqual(bill_obj.debt_account, payment_missing)



if __name__ == '__main__':
  pass

unittest.main()
