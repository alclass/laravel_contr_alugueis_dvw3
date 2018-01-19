#!/usr/bin/env python3
from copy import copy
from datetime import date
# from datetime import timedelta
from dateutil.relativedelta import relativedelta
import sys
# import calendar # calendar.monthrange(year, month)
# from .DateBillCalculatorMod import DateBillCalculator

try:
  from .PaymentMod import Payment
  from .juros_calculator import Juros
  from .DateBillCalculatorMod import DateBillCalculator
except SystemError:
  '''
  SystemError is raised when running this script in its folder
  One option is to include '.' (current folder) in sys.path
  and import the modules without their packages (the folders with __init__.py)   
  '''
  sys.path.insert(0, '.')
  from PaymentMod import Payment
  from juros_calculator import Juros
  from DateBillCalculatorMod import DateBillCalculator


REFTYPE_KEY = 'reftype'
def create_billingitems_list_for_invoicebill():
  '''
    # data
    DEBI stands for 'Debt Immediate' and means that non-paid billing in the previous month
    billing_item = {'typeref':'DEBI', 'value':1900+600+200}
    billing_items.append(billing_item)
    DEBC stands for 'Debt Carried-on' and means that older non-paid amount carried to the previous month

  :return:
  '''
  billingitems = []
  billingitem = {REFTYPE_KEY:'ALUG', 'value': 1900}
  billingitems.append(billingitem)
  billingitem = {REFTYPE_KEY: 'COND', 'value': 600}
  billingitems.append(billingitem)
  billingitem = {REFTYPE_KEY: 'IPTU', 'value': 200}
  billingitems.append(billingitem)
  return billingitems

def create_payments():
  payments = []
  payment_obj = Payment(paid_amount=1000, paydate=date(2018,2,10))
  payments.append(payment_obj)
  payment_obj = Payment(paid_amount=500, paydate=date(2018,2,25))
  payments.append(payment_obj)
  return payments


class PreviousBill:

  nonpaid_months_amount = 0
  carriedup_debt_amount = 0

  @staticmethod
  def fetch_nonpaid_months_amount():
    return PreviousBill.nonpaid_months_amount

  @staticmethod
  def fetch_carriedup_debt_amount():
    return PreviousBill.carriedup_debt_amount


class Bill:

  REFTYPE_KEY = REFTYPE_KEY

  def __init__(self, monthyeardateref, duedate, billingitems):
    self.monthyeardateref  = monthyeardateref
    self.duedate           = duedate
    self.billingitems      = billingitems # generally, it may have: ALUG, COND, IPTU

    self.datecalculator    = DateBillCalculator()
    self.payments          = [] # payment_obj has amount_paid and paydate
    self.multa_account     = 0
    self.debt_account      = 0
    self.cred_account      = 0
    self.payment_account   = 0
    self.juros_n_interest  = 0
    self.bool_payment_done = 0
    self.bill_closed_balance_if_any_to_forward = False
    self.total_due         = 0
    self.months_due_amount = 0
    for billingitem in self.billingitems:
      value = billingitem['value']
      self.months_due_amount += value
    self.carried_amount = self.get_updated_carried_amount_from_previous_bills_if_any()
    self.debt_account = self.months_due_amount + self.carried_amount
    # self.payment_missing IS THE SAME AS self.debt_account


  def setPayments(self, payments):
    self.payments = payments

  def fetch_debi_amount_from_previous_bills_if_any(self):
    return PreviousBill.fetch_nonpaid_months_amount()

  def fetch_debo_amount_from_previous_bills_if_any(self):
    return PreviousBill.fetch_carriedup_debt_amount()

  def get_updated_carried_amount_from_previous_bills_if_any(self):
    previousmonthrefdate = self.monthyeardateref - relativedelta(months=-1)
    debi_value           = self.fetch_debi_amount_from_previous_bills_if_any()
    upt_debi_value       = Juros.apply_interest_n_corrmonet(debi_value, previousmonthrefdate)
    debo_value           = self.fetch_debo_amount_from_previous_bills_if_any()
    upt_debo_value       = Juros.apply_interest_n_corrmonet(debo_value, previousmonthrefdate)
    updated_debi_n_debo  = upt_debi_value + upt_debo_value
    return updated_debi_n_debo

  def pay(self, payment_obj):
    '''
    Consider this as a private method.
    Also that it works as a credit / debit operation.
    It can only be called from inside the 'if' that checks this pay is on date
    :param payment_obj:
    :return:
    '''
    self.debt_account    -= payment_obj.paid_amount
    self.payment_account += payment_obj.paid_amount

  def apply_multa_if_payment_is_incomplete(self):
    '''

    *** THIS METHOD IS YET LOGICALLY INCOMPLETE ***
      Because it's not enough to find a later date, it should also find
        if earlier pay was enough

    This method should use functional programming techniques for TWO needs, ie:
    1) the method should know whether or not various payments paid on time
    2) the method should quickly find, if it happened, a 'fine' incidence

    :return:
    '''
    # obs: payments list must be IN ORDER OF PAYDATE
    amount_paid_on_date = 0
    for payment_obj in self.payments:
      if payment_obj.paydate <= self.duedate:
        amount_paid_on_date += payment_obj.paid_amount
    if amount_paid_on_date < self.months_due_amount:
      amount_to_fine = self.months_due_amount - amount_paid_on_date
      multa_amount = amount_to_fine * 0.1
      self.multa_account += multa_amount
      self.debt_account  += multa_amount

  def process_payment(self):
    '''
    This method should be run after setting the self.payment_objs list of Payments

    Before calculing bill, it must know whether or not to aply fine.

    The fine happens over the monthly amount that is late.
    The fine does not cover an amount that has been carried up, ie, older debt.
    So the TWO things are necessary to apply fine:
      1) even if some payment has been done on time,
        it must satisfy the complete bill's amount,
        if not, the contract fine applies to the amount
        that would complete the monthly bill;
      2) once 1) above happens, it must separate the monthly amount
        from any carrying-up's from older months,
        because fines do not apply to carrying-up's.

    :return:
    '''
    self.apply_multa_if_payment_is_incomplete()
    for payment_obj in self.payments:
      if payment_obj.paydate <= self.duedate:
        self.pay(payment_obj)
      else:
        self.pay_late(payment_obj)


  def pay_late(self, payment_obj):
    '''
    The interest startdate is not duedate, it's monthref plus ONE month.
      Example: if monthref is (01)Jan2018, interest_startdate is 01Feb2018.
    However, the corr-monet index is taken M-1, ie, it's the index of its
      previous month. Example: 15 days late in February are calculated with
      January's interest rate.
      * This is so because a month's index is only known M+1,
        example: the February corr-monet. index is only know in March.


    :param payment_obj:
    :return:
    '''

    interest_startdate = self.monthyeardateref + relativedelta(months=+1)
    mo_by_mo_days = self.datecalculator.calc_mo_by_mo_days_between_dates(
      interest_startdate,
      payment_obj.paydate
    )
    if len(mo_by_mo_days) == 0:
      return

    monthfractions = self.datecalculator.transform_monthdays_into_monthfractions(
      mo_by_mo_days,
      interest_startdate
    )
    interestarray = [0.01] * len(monthfractions)
    mo_by_mo_interest_plus_corrmonet_times_fraction_array = Juros \
      .gen_mo_by_mo_interest_plus_corrmonet_times_fraction_array(
      interest_startdate,
      monthfractions,
      interestarray
    )
    self.pay_applying_correctionfractions_array(
      payment_obj,
      mo_by_mo_interest_plus_corrmonet_times_fraction_array
    )

  def pay_applying_correctionfractions_array(
      self,
      payment_obj,
      mo_by_mo_interest_plus_corrmonet_times_fraction_array
    ):
    debt = self.debt_account
    for factor in mo_by_mo_interest_plus_corrmonet_times_fraction_array:
      debt = debt * (1 + factor)
    self.juros_n_interest = debt - self.debt_account
    self.debt_account     = debt
    self.debt_account    -= payment_obj.paid_amount
    self.payment_account += payment_obj.paid_amount

  '''
  def add_payment_obj(self, payment_obj):
    self.payments.append(payment_obj)
  '''

  def add_contracts_billing_items_to_self(self, contract_obj):
    '''
    billing_items will be a list of dict for the time being
    :return:
    '''
    for contract_billing_item in contract_obj.billing_items:
      billingitem = copy(contract_billing_item)
      self.billingitems.append(billingitem)

  def is_late_on_duedate(self, paydate):
    if paydate > self.duedate:
      return True
    return False

  def apply_late_mora_if_tardy(self, paydate):
    if self.is_late_on_duedate(paydate):
      return self.apply_late_mora()

  def pretty_print_bill(self):
    text = '\n'
    line = 'Monthly Bill Invoice\n'
    text += line
    line = '====================\n'
    text += line
    line = 'Monthref ------- % s\n' %(self.monthyeardateref)
    text += line
    line = 'Due Date ------- % s\n' %(self.duedate)
    text += line
    line = '-------------------\n'
    text += line
    for i, billingitem in enumerate(self.billingitems):
      line = '%d -> %s  ----------  %s\n' %(i, billingitem[self.REFTYPE_KEY], str(billingitem['value']))
      text += line
    line = '-------------------\n'
    text += line
    line = 'Debt Account (same as paym.miss.) %s\n' %(self.debt_account)
    text += line
    line = 'juros_n_interest ------- % s\n' %(self.juros_n_interest)
    text += line
    line = 'Payment done (account) %s\n' % (str(self.payment_account))
    text += line
    line = 'multa_account ----  %s\n' % (str(self.multa_account))
    text += line
    line = 'Carrying-up ----  %s\n' % (str(self.carried_amount))
    text += line
    line = 'Total  ----------  %s\n' % (str(self.total_due))
    text += line
    return text

  def __str__(self):
    return self.pretty_print_bill()

def create_adhoctest_bill():
  billingitems = create_billingitems_list_for_invoicebill()
  monthyearrefdate = date(2018, 1, 1)
  duedate          = date(2018, 2, 10)
  invoicebill = Bill(monthyearrefdate, duedate, billingitems)

  return invoicebill

def adhoctest():
  invoicebill = create_adhoctest_bill()
  payments = create_payments()
  invoicebill.setPayments(payments)
  invoicebill.process_payment()
  print (invoicebill)

if __name__ == '__main__':
  adhoctest()
