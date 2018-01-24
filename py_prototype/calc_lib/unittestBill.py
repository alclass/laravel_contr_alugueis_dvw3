#!/usr/bin/env python3
from copy import copy
from decimal import Decimal
from decimal import getcontext as getDecimalContext
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
    '''
    Here entire bill-value payment is on time
    :return:
    '''
    monthrefdate = date(2018, 1, 1)
    duedate          = date(2018, 2, 10)
    billingitems = []
    billingitem  = {Bill.REFTYPE_KEY:'ALUG','value':1900}
    billingitems.append(billingitem)
    billingitem  = {Bill.REFTYPE_KEY:'COND','value':600}
    billingitems.append(billingitem)
    billingitem  = {Bill.REFTYPE_KEY:'IPTU','value':250}
    billingitems.append(billingitem)

    bill_obj = Bill(monthrefdate=monthrefdate, duedate=duedate, billingitems=billingitems)
    payments = []
    paydate_ontime = date(2018, 2, 5)
    firstpaymentontime = 1900 + 600 + 250
    payment_obj = Payment(paid_amount=firstpaymentontime, paydate=paydate_ontime)
    payments.append(payment_obj)
    bill_obj.setPayments(payments)
    bill_obj.process_payment()

    # inmonth_due_amount
    inmonth_due = 1900 + 600 + 250
    self.assertEqual(bill_obj.inmonth_due_amount, inmonth_due)
    # inmonthpluspreviousdebts
    self.assertEqual(bill_obj.inmonthpluspreviousdebts, inmonth_due)
    # payment_account
    self.assertEqual(bill_obj.payment_account, firstpaymentontime)
    # inmonthpluspreviousdebts_minus_payments
    expected_inmonthplusdebts_minus_payments = inmonth_due - firstpaymentontime
    self.assertEqual(bill_obj.inmonthpluspreviousdebts_minus_payments, expected_inmonthplusdebts_minus_payments)

    # inmonthpluspreviousdebts_minus_payments
    amount_in_mora = inmonth_due - firstpaymentontime  # 800
    fine_value = amount_in_mora * 0.1
    self.assertEqual(bill_obj.multa_account, 0) # 80 ie 10% of 800

    # debt_account, after processing, is payment_missing
    # corr_monet considers 2018-jan not february

    paydate_minus_one_month = paydate_ontime - relativedelta(months=1)
    corrmonet = Juros.fetch_corrmonet_for_month(paydate_minus_one_month) # corr_monet considers 2018-jan not february
    # monthdaysfraction MIGHT be anything here, the important is amount_in_mora being ZERO (zero times anything is still zero)
    monthdaysfraction = 15/28 # monthdaysfraction considers february not january
    interest_n_cm_on_day_15 = amount_in_mora * ((0.01 + corrmonet) * monthdaysfraction) # 0.005 must be fetched (TO-DO): for the time being, it's hardcoded
    payment_missing = inmonth_due + fine_value + interest_n_cm_on_day_15 - firstpaymentontime
    objsdebtaccount = Decimal(payment_missing)
    paymentmissingtocompare = Decimal(payment_missing)
    getDecimalContext().prec = 8
    self.assertEqual(objsdebtaccount, paymentmissingtocompare)

    overpaid = 0
    if payment_missing < 0:
      overpaid -= payment_missing
      payment_missing = 0
    self.assertEqual(objsdebtaccount, paymentmissingtocompare)

    self.assertEqual(bill_obj.cred_account, overpaid)


  def test_bill_payment_2(self):
    '''
    Here ONE payments is on time and ANOTHER is late.
    :return:
    '''
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
    paydate_ontime = date(2018, 2, 5)
    firstpaymentontime = 1000
    payment_obj = Payment(paid_amount=firstpaymentontime, paydate=paydate_ontime)
    payments.append(payment_obj)
    paydate_late = date(2018, 2, 15)
    paydate_frozen = copy(paydate_late)
    secondpaymentlate = 500
    payment_obj = Payment(paid_amount=secondpaymentlate, paydate=paydate_late)
    payments.append(payment_obj)
    bill_obj.setPayments(payments)
    bill_obj.process_payment()

    # inmonth_due_amount
    inmonth_due = 1000 + 600 + 200
    self.assertEqual(bill_obj.inmonth_due_amount, inmonth_due)
    # inmonthpluspreviousdebts
    self.assertEqual(bill_obj.inmonthpluspreviousdebts, inmonth_due)
    # payment_account
    payment_total_amount = firstpaymentontime + secondpaymentlate
    self.assertEqual(bill_obj.payment_account, payment_total_amount)
    # inmonthpluspreviousdebts_minus_payments
    expected_inmonthplusdebts_minus_payments = inmonth_due - payment_total_amount
    self.assertEqual(bill_obj.inmonthpluspreviousdebts_minus_payments, expected_inmonthplusdebts_minus_payments)

    # inmonthpluspreviousdebts_minus_payments
    amount_in_mora = inmonth_due - firstpaymentontime  # 800
    fine_value = amount_in_mora * 0.1
    self.assertEqual(bill_obj.multa_account, fine_value) # 80 ie 10% of 800

    # debt_account, after processing, is payment_missing
    # corr_monet considers 2018-jan not february

    paydate_minus_one_month = paydate_frozen - relativedelta(months=1)
    corrmonet = Juros.fetch_corrmonet_for_month(paydate_minus_one_month) # corr_monet considers 2018-jan not february
    # print ('corrmonet paydate_frozen', paydate_frozen, paydate_minus_one_month, corrmonet)
    monthdaysfraction = 15/28 # monthdaysfraction considers february not january
    interest_n_cm_on_day_15 = amount_in_mora * ((0.01 + corrmonet) * monthdaysfraction) # 0.005 must be fetched (TO-DO): for the time being, it's hardcoded
    payment_missing = inmonth_due + fine_value + interest_n_cm_on_day_15 - payment_total_amount

    # With these numbers, we'll class Decimal() otherwise, equality will differ 'far away' into the decimal places

    objsdebtaccount = Decimal(payment_missing)
    paymentmissingtocompare = Decimal(payment_missing)
    getDecimalContext().prec = 8

    overpaid = 0
    if payment_missing < 0:
      overpaid -= payment_missing
      payment_missing = 0
    self.assertEqual(objsdebtaccount, paymentmissingtocompare)

    self.assertEqual(bill_obj.cred_account, overpaid)

  def test_bill_payment_3(self):
    '''
    Here TWO payments are done on the same late date
    :return:
    '''
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

    fine_value = inmonth_due * 0.1
    self.assertEqual(bill_obj.multa_account, fine_value) # 80 ie 10% of 800

    # debt_account, after processing, is payment_missing
    paydate_minus_one_month = paydate_frozen - relativedelta(months=1)
    corrmonet = Juros.fetch_corrmonet_for_month(paydate_minus_one_month) # corr_monet considers 2018-jan not february
    print ('corrmonet paydate_frozen', paydate_frozen, paydate_minus_one_month, corrmonet)
    monthdaysfraction = 27/31 # monthdaysfraction considers february not january
    interest_n_cm_on_payday = inmonth_due * ((0.01 + corrmonet) * monthdaysfraction) # 0.005 must be fetched (TO-DO): for the time being, it's hardcoded
    payment_missing = inmonth_due + fine_value + interest_n_cm_on_payday - paid_amount
    print ('payment_missing', payment_missing)
    overpaid = 0
    if payment_missing < 0:
      overpaid -= payment_missing
      payment_missing = 0
    self.assertEqual(bill_obj.debt_account, payment_missing)

    # cred_account (overpaid is simmetric of payment_missing if latter is negative, otherwise, it's zero)
    self.assertEqual(bill_obj.cred_account, overpaid)


  def test_bill_payment_4(self):
    '''
    Here TWO payments are done on the same late date
    :return:
    '''
    monthrefdate = date(2017, 11, 1)
    #duedate          = date(2018, 1, 10)
    duedate = None
    alug = 3000; cond = 1300; iptu = 300
    inmonth_due = alug + cond + iptu
    billingitems = []
    billingitem  = {Bill.REFTYPE_KEY:'ALUG','value':alug}
    billingitems.append(billingitem)
    billingitem  = {Bill.REFTYPE_KEY:'COND','value':cond}
    billingitems.append(billingitem)
    billingitem  = {Bill.REFTYPE_KEY:'IPTU','value':iptu}
    billingitems.append(billingitem)

    bill_obj = Bill(monthrefdate=monthrefdate, duedate=duedate, billingitems=billingitems)
    previousmonthsdebts = 1255.00
    bill_obj.set_previousmonthsdebts(previousmonthsdebts = previousmonthsdebts)
    payments = []
    paydate = date(2017, 12, 10)
    #paydate_frozen = copy(paydate)
    firstpayondateamount = inmonth_due + previousmonthsdebts
    payment_obj = Payment(paid_amount=firstpayondateamount, paydate=paydate)
    payments.append(payment_obj)
    paydatelate = date(2017, 12, 11)
    secondpaylateamount = 100
    payment_obj = Payment(paid_amount=secondpaylateamount, paydate=paydatelate)
    payments.append(payment_obj)
    bill_obj.setPayments(payments)
    bill_obj.process_payment()

    # inmonth_due_amount
    self.assertEqual(bill_obj.inmonth_due_amount, inmonth_due)
    # previousmonthsdebts
    self.assertEqual(bill_obj.previousmonthsdebts, previousmonthsdebts)
    # inmonthpluspreviousdebts
    inmonth_due_plus_previousmonthsdebts = inmonth_due + previousmonthsdebts
    self.assertEqual(bill_obj.inmonthpluspreviousdebts, inmonth_due_plus_previousmonthsdebts)
    # payment_account
    paid_amount = firstpayondateamount + secondpaylateamount
    self.assertEqual(bill_obj.payment_account, paid_amount)
    # inmonthpluspreviousdebts_minus_payments
    expected_inmonthplusdebts_minus_payments = inmonth_due_plus_previousmonthsdebts - paid_amount
    self.assertEqual(bill_obj.inmonthpluspreviousdebts_minus_payments, expected_inmonthplusdebts_minus_payments)

    # inmonthpluspreviousdebts_minus_payments
    amount_in_mora = inmonth_due - firstpayondateamount  # 800
    fine_value = amount_in_mora * 0.1
    self.assertEqual(bill_obj.multa_account, fine_value) # 80 ie 10% of 800

    # debt_account, after processing, is payment_missing
    corrmonet = Juros.fetch_corrmonet_for_month(monthrefdate)
    monthdaysfraction = 1/31
    interest_n_cm_on_payday = amount_in_mora * ((0.01 + corrmonet) * monthdaysfraction) # 0.005 must be fetched (TO-DO): for the time being, it's hardcoded
    payment_missing = inmonth_due_plus_previousmonthsdebts + fine_value + interest_n_cm_on_payday - paid_amount
    overpaid = 0
    if payment_missing < 0:
      overpaid -= payment_missing
      payment_missing = 0
    self.assertEqual(bill_obj.debt_account, payment_missing)

    # cred_account (overpaid is simmetric of payment_missing if latter is negative, otherwise, it's zero)
    self.assertEqual(bill_obj.cred_account, overpaid)


  def test_bill_payment_5(self):
    '''
    Here TWO payments are done on the same late date
    :return:
    '''
    monthrefdate = date(2017, 1, 1)
    #duedate          = date(2018, 1, 10)
    duedate = None
    alug = 2500; cond = 950; iptu = 250
    inmonth_due = alug + cond + iptu
    billingitems = []
    billingitem  = {Bill.REFTYPE_KEY:'ALUG','value':alug}
    billingitems.append(billingitem)
    billingitem  = {Bill.REFTYPE_KEY:'COND','value':cond}
    billingitems.append(billingitem)
    billingitem  = {Bill.REFTYPE_KEY:'IPTU','value':iptu}
    billingitems.append(billingitem)

    bill_obj = Bill(monthrefdate=monthrefdate, duedate=duedate, billingitems=billingitems)
    previousmonthsdebts = 3000.00
    bill_obj.set_previousmonthsdebts(previousmonthsdebts = previousmonthsdebts)
    payments = []
    paid_amount  = 0
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

    # inmonth_due_amount
    self.assertEqual(bill_obj.inmonth_due_amount, inmonth_due)
    # previousmonthsdebts
    self.assertEqual(bill_obj.previousmonthsdebts, previousmonthsdebts)
    # inmonthpluspreviousdebts
    inmonth_due_plus_previousmonthsdebts = inmonth_due + previousmonthsdebts
    self.assertEqual(bill_obj.inmonthpluspreviousdebts, inmonth_due_plus_previousmonthsdebts)
    # payment_account
    self.assertEqual(bill_obj.payment_account, paid_amount)
    # inmonthpluspreviousdebts_minus_payments
    expected_inmonthplusdebts_minus_payments = inmonth_due_plus_previousmonthsdebts - paid_amount
    self.assertEqual(bill_obj.inmonthpluspreviousdebts_minus_payments, expected_inmonthplusdebts_minus_payments)

    # inmonthpluspreviousdebts_minus_payments
    amount_in_mora = inmonth_due
    fine_value = amount_in_mora * 0.1
    # print('=>', inmonth_due_plus_previousmonthsdebts, bill_obj.multa_account, fine_value)
    self.assertEqual(bill_obj.multa_account, fine_value)

    # debt_account, after processing, is payment_missing
    cm_jan_for_feb = Juros.fetch_corrmonet_for_month(monthrefdate)
    date2018_02 = monthrefdate + relativedelta(months=+1)
    cm_feb_for_mar = Juros.fetch_corrmonet_for_month(date2018_02)
    date2018_03 = date2018_02 + relativedelta(months=+1)
    cm_mar_for_apr = Juros.fetch_corrmonet_for_month(date2018_03)
    date2018_04 = date2018_03 + relativedelta(months=+1)
    cm_apr_for_may = Juros.fetch_corrmonet_for_month(date2018_04)
    date2018_05 = date2018_04 + relativedelta(months=+1)
    cm_may_for_jun = Juros.fetch_corrmonet_for_month(date2018_05)
    ongoingvalue = inmonth_due * (1.1) # fine applied
    # correction in Feb
    ongoingvalue += ongoingvalue *(0.01 + cm_jan_for_feb)
    # correction in Mar
    ongoingvalue += ongoingvalue * (0.01 + cm_feb_for_mar)
    # correction in Aprr
    # paydate1 = date(2017, 4, 10)
    ongoingvalue += ongoingvalue * ((0.01+cm_mar_for_apr)*(10/30))
    ongoingvalue -= payamount1
    # paydate2 = date(2017, 5, 10)
    ongoingvalue += ongoingvalue * ((0.01+cm_mar_for_apr)*(20/30)) # part in April (20 days of Apr)
    ongoingvalue += ongoingvalue * ((0.01+cm_apr_for_may)*(10/31)) # part in May (10 days of May)
    ongoingvalue -= payamount2
    # paydate3 = date(2017, 6, 3)
    ongoingvalue += ongoingvalue * (1 + (0.01+cm_apr_for_may)*(21/31)) # part in May (21 days of May)
    ongoingvalue += ongoingvalue * (1 + (0.01+cm_may_for_jun)*(3/30)) # part in June (3 days of Jun)
    ongoingvalue -= payamount3
    payment_missing = ongoingvalue
    overpaid = 0
    if payment_missing < 0:
      overpaid -= payment_missing
      payment_missing = 0
    self.assertEqual(bill_obj.debt_account, payment_missing)

    # cred_account (overpaid is simmetric of payment_missing if latter is negative, otherwise, it's zero)
    self.assertEqual(bill_obj.cred_account, overpaid)



if __name__ == '__main__':
  pass

unittest.main()
