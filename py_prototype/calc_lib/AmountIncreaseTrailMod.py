#!/usr/bin/env python3
'''
See docstring for class AmountIncreaseTrail
'''

# from datetime import date
# from datetime import timedelta
from dateutil.relativedelta import relativedelta
from datetime import date
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
      montant_ini,
      monthrefdate,
      restart_timerange_date, # not to be confused with restart_mora_date
      end_timerange_date,     # ie restart_mora_date is end_timerange_date + 1 day
      interest_rate,
      corrmonet_in_month,
      paid_amount = None,
      finevalue   = None,
      monthseqnumber = 1,
      contract_id = None
  ):
    '''
    Dynamic properties, ie derived fields, depend on other (original) fields (above)

      uptodate
      restart_mora_date
      was_fine_applied
      daysinmonth
      increaseamount
      updatedvalue
      balance

      self.uptodate
        => the same as end_timerange_date
      self.restart_mora_date
        => day after end_timerange_date
      self.was_fine_applied
        => if finevalue is not None, was_fine_applied is True; False otherwise
      self.daysinmonth
        => it calendar.monthrange(end_timerange_date)[1] (ie, total days in a month) of M+1
        OBS.: end_timerange_date AND restart_timerange_date MUST ALWAYS BE IN THE SAME MONTH/YEAR
              otherwise, an exception will be raised
      self.increaseamount
        => it's the interest plus corr.monet. applied to debt (it does not include fine)
      self.updatedvalue
        => it's debt plus increaseamount (as above, it also does not include fine)
      self.balance
        => it's the net result of bill minus payment and possibly financial increases if payment is late
           balance is also called forwardvalue, for, when it's not zero, it becomes either
           previousdebts or cred_amount in the following bill.
    '''
    self.montant_ini    = montant_ini
    self.monthrefdate   = monthrefdate # this is never changed across an Amount Increase Trail list that shows the updating of a debt_account according to late payments!
    self.monthseqnumber = monthseqnumber
    self.contract_id    = contract_id
    self.restart_timerange_date = restart_timerange_date
    self.end_timerange_date     = end_timerange_date
    self.interest_rate          = interest_rate
    self.corrmonet_in_month     = corrmonet_in_month

    if self.restart_timerange_date is None:
      self.restart_timerange_date = self.monthrefdate + relativedelta(months=+1)
    if self.end_timerange_date is None:
      year  = self.restart_timerange_date.year
      month = self.restart_timerange_date.month
      lastdayinmonth = calendar.monthrange(year, month)[1]
      self.end_timerange_date = self.restart_timerange_date.replace(day=lastdayinmonth)
    self.check_ini_n_end_dates_n_raise_if_consistent()

    self.paid_amount    = paid_amount # if not None, end_timerange_date equals semantically paydate
    self.finevalue      = finevalue

  @property
  def daysininterest(self):
    delta = self.end_timerange_date - self.restart_timerange_date
    days_in_range = delta.days + 1
    return days_in_range

  @property
  def monthfraction(self):
    return self.daysininterest / self.daysinmonth

  @property
  def updatefactor(self):
    return (self.interest_rate + self.corrmonet_in_month) * self.monthfraction

  @property
  def was_fine_applied(self):
    if self.finevalue is None:
      return False
    return True

  @property
  def restart_mora_date(self):
    '''
      formerly this method was called day_after_end_date()
    :return:
    '''
    return self.end_timerange_date + relativedelta(days=+1)

  def check_ini_n_end_dates_n_raise_if_consistent(self):
    iniyear  = self.restart_timerange_date.year
    inimonth = self.restart_timerange_date.month
    fimyear  = self.end_timerange_date.year
    fimmonth = self.end_timerange_date.month
    if (iniyear, inimonth) != (fimyear, fimmonth):
      error_msg = '(iniyear=%d, inimonth=%d) != (fimyear=%d, fimmonth=%d)' %(iniyear, inimonth, fimyear, fimmonth)
      raise ValueError(error_msg)

  @property
  def daysinmonth(self):
    self.check_ini_n_end_dates_n_raise_if_consistent()
    return calendar.monthrange(self.end_timerange_date.year, self.end_timerange_date.month)[1]

  @property
  def increaseamount(self):
    return self.montant_ini * self.updatefactor

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
    paid_amount = 0
    if self.finevalue is not None:
      multa = self.finevalue
    if self.paid_amount is not None:
      paid_amount = self.paid_amount
    return self.updatedvalue + multa - paid_amount

  def extract_lastdayofmonth_of_enddate(self):
    year  = self.end_timerange_date.year
    month = self.end_timerange_date.month
    lastdayinmonth = calendar.monthrange(year, month)[1]
    lastdayofmonthdate = self.end_timerange_date.replace(day=lastdayinmonth)
    return lastdayofmonthdate

  def __str__(self):
    fieldlist = [
      'montant_ini', 'interest_rate', 'corrmonet_in_month',
      'monthrefdate', 'restart_timerange_date', 'end_timerange_date',
      'daysininterest', 'daysinmonth', 'monthfraction', 'updatefactor',
      'increaseamount', 'updatedvalue', 'paid_amount',
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
    -> montante base      = {montant_ini:.2f}
    -> alícota juros      = {interest_rate:.2f}
    -> corr.monet. no mês = {corrmonet_in_month:.4f}
    -> data-início j+cm   = {restart_timerange_date}
    -> data-até j+cm      = {end_timerange_date}    
    -> dias contados      = {daysininterest}
    -> dias no mês        = {daysinmonth}
    -> fração do mês (fm) = {monthfraction:.2f}
    -> fator atu(j+cm)*fm = {updatefactor:.4f}
    -> montante*(j+cm)*fm = {increaseamount:.2f}
    -> montante atualiz.  = {updatedvalue:.2f}'''.format(**datadict)
    if self.paid_amount is not None and self.paid_amount > 0:
      outstr += '''
    -> pagamento          = -{paid_amount}
                         em {end_timerange_date}'''.format(**datadict)
    if self.was_fine_applied:
      outstr += '''
    -> incidência multa   = {finevalue}'''.format(**datadict)
    outstr += '''
    ----------------------------------
    Saldo a pagar         = {balance:.2f}
                         em {end_timerange_date}
    ----------------------------------
    '''.format(**datadict)
    return outstr

def adhoctest():
  ait = AmountIncreaseTrail(
    montant_ini = 1000,
    monthrefdate = date(2017,1,1),
    restart_timerange_date = None,
    end_timerange_date     = None,
    interest_rate      = 0.01,
    corrmonet_in_month = 0.004,
    paid_amount        = 800,
    finevalue          = 100,
  )
  print (ait)

  ait = AmountIncreaseTrail(
    montant_ini = 2000,
    monthrefdate = date(2017,12,1),
    restart_timerange_date = date(2018,3,15),
    end_timerange_date     = None,
    interest_rate      = 0.01,
    corrmonet_in_month = 0.004,
    paid_amount        = None,
    finevalue          = None,
  )
  print (ait)


if __name__ == '__main__':
  adhoctest()

# TO-DO: unit testing