#!/usr/bin/env python3
from copy import copy
from datetime import date
# from datetime import timedelta
from dateutil.relativedelta import relativedelta
# import unittest

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
  billingitem = {REFTYPE_KEY: 'DEBI', 'value': 200}
  billingitems.append(billingitem)
  billingitem = {REFTYPE_KEY: 'DEBO', 'value': 400}
  billingitems.append(billingitem)
  return billingitems

class MonthYearDateRef:

  def generate_conventioned_monthyeardateref_against_given_date(self, p_date=None):
    '''
    if date is from yyyy-mm-01 until yyyy-mm-10,
      then
        monthyeardateref is yyyy-mm-01
      else (ie, date > yyyy-mm-10)
        monthyeardateref is next_month(yyyy-mm-01)
          ie, it's the first day in the following month relative to yyyy-mm-01
    :return:
    '''

    if p_date is None:
      monthyeardateref = date.today()
    else:
      monthyeardateref = copy(p_date)
    if monthyeardateref.day > 10:
      monthyeardateref = monthyeardateref + relativedelta(months=+1)
    monthyeardateref = monthyeardateref.replace(day = 1)
    return monthyeardateref

  def find_monthyearrefdate(self, month=None, year=None):
    if month is None or year is None:
      monthyeardateref = self.generate_conventioned_monthyeardateref_against_given_date()
    else:
      monthyeardateref = date(year, month, 1)
    return monthyeardateref

class Bill:

  REFTYPE_KEY = REFTYPE_KEY

  def __init__(self, monthyeardateref, duedate, billingitems):
    self.late_mora_has_been_applied = False
    self.debi_to_carry = 0
    self.debo_to_carry = 0
    self.payment_missing  = 0
    self.payment_done  = 0
    self.monthyeardateref = monthyeardateref
    self.duedate          = duedate
    self.billingitems     = billingitems
    self.total_due        = 0
    for billingitem in self.billingitems:
      value = billingitem['value']
      self.total_due += value
      reftype = billingitem[self.REFTYPE_KEY]
      if reftype == 'DEBI':
        self.debi_to_carry = billingitem['value']
      if reftype == 'DEBO':
        self.debo_to_carry = billingitem['value']

  def add_contracts_billing_items_to_self(self, contract_obj):
    '''
    billing_items will be a list of dict for the time being
    :return:
    '''
    for contract_billing_item in contract_obj.billing_items:
      billingitem = copy(contract_billing_item)
      self.billingitems.append(billingitem)

  def sync_debi_n_debo_back_to_billing_items(self):
    '''
    billing_items will be a list of dict for the time being
    :return:
    '''
    for billingitem in self.billingitems:
      reftype = billingitem[self.REFTYPE_KEY]
      if reftype == 'DEBI':
        billingitem['value'] = self.debi_to_carry
      if reftype == 'DEBO':
        billingitem['value'] = self.debo_to_carry

  def add_to_cred_to_carry(self, p_credit_value_to_add):
    '''
    credit_value is a surplus, ie, the debtor paid more than it was due
    :param credit_value:
    :return:
    '''
    carried_credit = 0
    for billingitem in self.billingitems:
      reftype = billingitem[self.REFTYPE_KEY]
      if reftype == 'CRED':
        carried_credit = billingitem['value']
    carried_credit += p_credit_value_to_add
    # replace updated value on it
    billingitem['value'] = carried_credit

  def get_open_payments(self):
    #open_pay_history = {'2018-01-12':500, '2017-12-05':2500}
    open_payments = {'2018-01-10': 500, '2017-12-15': 2500}
    return open_payments

  def is_late_on_duedate(self, paydate):
    if paydate > self.duedate:
      return True
    return False

  def sum_items_without_debi_or_debo(self):
    total_without_debi_n_debo = 0 # ie, only the contract items (ALUG, COND, IPTU etc)
    for billingitem in self.billingitems:
      reftype = billingitem[self.REFTYPE_KEY]
      if reftype in ['DEBI', 'DEBO']:
        continue
      total_without_debi_n_debo += billingitem['value']
    return total_without_debi_n_debo


  def apply_late_mora(self):
    if self.late_mora_has_been_applied:
      # raise ValueError('late_mora_has_been_applied ')
      return False
    debi_parcel = self.sum_items_without_debi_or_debo() + self.debi_to_carry
    debi_parcel = debi_parcel * (1 + 0.1 + 0.01 + 0.005)
    self.debi_to_carry = debi_parcel
    if self.debo_to_carry > 0:
      self.debo_to_carry = self.debo_to_carry * (1 + 0.01 + 0.005)
    self.sync_debi_n_debo_back_to_billing_items()
    self.late_mora_has_been_applied = True
    self.recalculate_total_due()
    return True

  def recalculate_total_due(self):
    total_due = 0
    for billingitem in self.billingitems:
      total_due += billingitem['value']
    self.total_due = total_due

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
    line = 'Payment done   ----  %s\n' % (str(self.payment_done))
    text += line
    line = 'Payment missing ----  %s\n' % (str(self.payment_missing))
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
  print (invoicebill)

if __name__ == '__main__':
  adhoctest()
