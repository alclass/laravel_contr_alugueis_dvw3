#!/usr/bin/env python3
'''
See docstring for class AmountIncreaseTrail
'''

# from datetime import date
# from datetime import timedelta
from dateutil.relativedelta import relativedelta
import calendar # for calendar.monthrange(year, month)
# import sys


class AmountIncreaseTrail:
  '''
  This class stores the properties (attributes) of an "Amount Increase Trail" piece.
  An "Amount Increase Trail" piece stores a step-increase "vector" in a finance mora
  calculation.

  All steps (ie, a list of AIT's) will give the full evolution of a debt.
  '''

  def __init__(self,
               montant_ini, monthrefdate, pay_or_restart_date, paid_amount,
               interest_rate, corrmonet_in_month, daysininterest, finevalue = None
               ):
    '''
    Dynamic properties, ie derived fields, depend on other (original) fields (above)
      self.restart_mora_date
        => if paid_amount is not None, restart_mora_date is the (daysininterest+1)th in the monthref+1 month
        => if paid_amount is None, restart_mora_date is monthref+2 (ie, the first month after M+1)
      self.uptodate
        => is one day less than self.restart_mora_date
      self.was_fine_applied
        => if finevalue is not None, was_fine_applied is True; False otherwise
      self.daysinmonth
        => it calendar.monthrange()[1] (ie, total days in a month) of M+1
      self.increaseamount
        => it's the interest plus corr.monet. applied to debt (it does not include fine)
      self.updatedvalue
        => it's debt plus increaseamount (as above, it also does not include fine)
      self.balance
        => it's the net result of bill minus payment and possibly financial increases if payment is late
           balance is also called forwardvalue, for, when it's not zero, it becomes either
           previousdebts or cred_amount in the following bill.

    '''
    self.montant_ini   = montant_ini
    self.monthrefdate  = monthrefdate
    self.interest_rate = interest_rate
    self.corrmonet_in_month  = corrmonet_in_month
    self.pay_or_restart_date = None
    if pay_or_restart_date is not None:
      self.pay_or_restart_date = pay_or_restart_date
    self.daysininterest = daysininterest
    self.paid_amount    = paid_amount
    self.finevalue      = finevalue

  @property
  def was_fine_applied(self):
    if self.finevalue is None:
      return False
    return True

  @property
  def uptodate(self):
    '''
    uptodate is one day less than restart_mora_date
    :return:
    '''
    if self.pay_or_restart_date is None:
      lastdayinmonth = calendar.monthrange(self.monthrefdate.year, self.monthrefdate.month)[1]
      lastdayinmonthdate = self.monthrefdate.replace(day=lastdayinmonth)
      return lastdayinmonthdate
    return self.pay_or_restart_date

  @property
  def restart_mora_date(self):
    return self.uptodate + relativedelta(days=+1)

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
    return self.updatedvalue + multa - self.paid_amount

  def extract_lastdayofmonthdate(self):
    year  = self.monthrefdate.year
    month = self.monthrefdate.month
    lastday = calendar.monthrange(year, month)
    lastdayofmonthdate = self.monthrefdate.replace(day=lastday)
    return lastdayofmonthdate

  def __str__(self):
    fieldlist = [
      'montant_ini', 'interest_rate', 'corrmonet_in_month',
      'monthrefdate', 'pay_or_restart_date',
      'daysininterest', 'daysinmonth',
      'increaseamount', 'updatedvalue', 'paid_amount',
      'was_fine_applied', 'finevalue', 'balance', 'uptodate',
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
     + updatedvalue       = {updatedvalue:.2f}'''.format(**datadict)
    if self.paid_amount > 0:
      outstr += '''
     - paid_amount        = {paid_amount}
       -> paydate         = {pay_or_restart_date}'''.format(**datadict)
    if self.was_fine_applied:
      outstr += '''
     + finevalue (incid)  = {finevalue}'''.format(**datadict)
    outstr += '''
    ----------------------------------
    balance               = {balance:.2f}
    uptodate              = {uptodate}
    ----------------------------------
    '''.format(**datadict)
    return outstr





if __name__ == '__main__':
  pass