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


class AmountIncreaseTrail:
  '''
  This class stores the properties (attributes) of an "Amount Increase Trail" piece.
  An "Amount Increase Trail" piece stores a step-increase "vector" in a finance mora
  calculation.

  All steps (ie, a list of AIT's) will give the full evolution of a debt.
  '''

  def __init__(self, montant_ini,  interest_rate,  corrmonet_in_month,
               monthrefdate, paydate,
               daysininterest, payapplied,
               finevalue = None):
    self.montant_ini        = montant_ini
    self.interest_rate      = interest_rate
    self.corrmonet_in_month = corrmonet_in_month
    self.monthrefdate       = monthrefdate
    # self.restart_mora_date  = None # it's a dynamic property
    self.paydate            = None
    if paydate is not None:
      self.paydate          = paydate
    self.daysininterest     = daysininterest
    self.payapplied         = payapplied
    self.finevalue          = finevalue

  @property
  def was_fine_applied(self):
    if self.finevalue is None:
      return False
    return True

  @property
  def restart_mora_date(self):
    if self.paydate is None:
      lastdayinmonth = calendar.monthrange(self.monthrefdate.year, self.monthrefdate.month)[1]
      restart_date = self.monthrefdate.replace(day=lastdayinmonth)
      return restart_date
    return self.paydate + relativedelta(days=+1)

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
    if self.was_fine_applied:
      multa = self.finevalue
    return self.updatedvalue + multa - self.payapplied

  def __str__(self):
    fieldlist = [
      'montant_ini', 'interest_rate', 'corrmonet_in_month',
      'monthrefdate', 'paydate',
      'daysininterest', 'daysinmonth',
      'increaseamount', 'updatedvalue', 'payapplied',
      'was_fine_applied', 'finevalue', 'balance',
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
    if self.was_fine_applied:
      outstr += '''
     + finevalue (incid) = {finevalue}'''.format(**datadict)
    outstr += '''
    ----------------------------------
    balance              = {balance:.2f}
    ----------------------------------
    '''.format(**datadict)
    return outstr





if __name__ == '__main__':
  pass