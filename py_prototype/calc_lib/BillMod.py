#!/usr/bin/env python3
from copy import copy
from datetime import date
# from datetime import timedelta
from dateutil.relativedelta import relativedelta
# import unittest

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
  billingitem = {'typeref': 'ALUG', 'value': 1900}
  billingitems.append(billingitem)
  billingitem = {'typeref': 'COND', 'value': 600}
  billingitems.append(billingitem)
  billingitem = {'typeref': 'IPTU', 'value': 200}
  billingitems.append(billingitem)
  billingitem = {'typeref': 'DEBI', 'value': 200}
  billingitems.append(billingitem)
  billingitem = {'typeref': 'DEBO', 'value': 400}
  billingitems.append(billingitem)
  return billingitems

class MonthYearDateRef:

  def generate_conventioned_monthyeardateref_against_given_date(self, p_date=None):
    '''
    if date is from yyyy-mm-01 until yyyy-mm-10,
      then
        monthyeardateref is yyyy-mm-01
      else
        monthyeardateref is next_month(yyyy-mm-01) ie, it's the first day in the following month relative to yyyy-mm-01
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

  def find_monthrefdate(self, month=None, year=None):
    if month is None or year is None:
      monthyeardateref = self.generate_conventioned_monthyeardateref_against_given_date()
    else:
      monthyeardateref = date(year, month, 1)
    return monthyeardateref

class Bill:

  def __init__(self, monthyeardateref, duedate, billingitems):
    self.monthyeardateref = monthyeardateref
    self.duedate          = duedate
    self.billingitems     = billingitems
    self.total = 0
    for billingitem in self.billingitems:
      value = billingitem['value']
      self.total += value
      if 'DEBI' in billingitem:
        self.debi_to_carry = billingitem['DEBI']
      if 'DEBO' in billingitem:
        self.debo_to_carry = billingitem['DEBO']

  def get_contracts_billing_items(self):
    '''
    billing_items will be a list of dict for the time being
    :return:
    '''
    pass

  def sync_debi_n_debo_billing_items(self):
    '''
    billing_items will be a list of dict for the time being
    :return:
    '''
    for billingitem in self.billingitems:
      if 'DEBI' in self.billingitem:
        self.billingitem['DEBI'] = self.debi_to_carry
      if 'DEBO' in self.billingitem:
        self.billingitem['DEBO'] = self.debo_to_carry

  def get_open_payments(self):
    #open_pay_history = {'2018-01-12':500, '2017-12-05':2500}
    open_payments = {'2018-01-10': 500, '2017-12-15': 2500}
    return open_payments

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
      line = '%d -> %s  ----------  %s\n' %(i, billingitem['typeref'], str(billingitem['value']))
      text += line
    line = '-------------------\n'
    text += line
    line = 'Total  ----------  %s\n' % (str(self.total))
    text += line
    return text

  def __str__(self):
    return self.pretty_print_bill()


def adhoctest():
  billingitems = create_billingitems_list_for_invoicebill()
  monthyearrefdate = date(2018, 1, 1)
  duedate          = date(2018, 2, 10)
  invoicebill = Bill(monthyearrefdate, duedate, billingitems)
  print (invoicebill)

if __name__ == '__main__':
  adhoctest()
