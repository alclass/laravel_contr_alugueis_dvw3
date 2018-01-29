#!/usr/bin/env python3

from copy import copy
from datetime import date
from datetime import timedelta
from dateutil.relativedelta import relativedelta
# import unittest
import sys
# from .BillMod import Bill
try:
  from .BillMod import create_adhoctest_bill
except SystemError:
  sys.path.insert(0, '.')
  try:
    from BillMod import create_adhoctest_bill
  except ImportError:
    pass



class Payment:
  '''
  '''


  @staticmethod
  def check_paydates_order_n_raise_exception_if_inconsistent(payments):
    if len(payments) == 0:
      return
    previouspay = payments[0]
    for nextpay in payments[1:]:
      if previouspay.paydate > nextpay.paydate:
        error_msg = 'nextpay.paydate (%s) > previouspay.paydate (%s)' %(str(nextpay.paydate), str(previouspay.paydate))
        raise ValueError(error_msg)
      previouspay = nextpay

  @staticmethod
  def order_payments_in_date_ascending_order_if_needed(payments):
    '''
    TO-DO
    :param payments:
    :return:
    '''
    return payments

  @staticmethod
  def are_there_more_than_one_payment_in_a_day(payments):
    if payments is None or len(payments) == 0:
      return False
    previouspay = payments[0]
    for nextpay in payments[1:]:
      if previouspay.paydate == nextpay.paydate:
        return True
      previouspay = nextpay
    return False


  @staticmethod
  def consolidate_days_when_there_are_more_than_one_payment_in_a_day(payments):
    if payments is None or len(payments) == 0:
      return
    consolidated_paymentlists = []
    previouspay = payments[0]
    del payments[0]
    consolidated_paymentlists.append(previouspay)
    while len(payments) > 0:
      nextpay = payments[0]
      del payments[0]
      if previouspay.paydate         == nextpay.paydate and \
          previouspay.monthrefdate   == nextpay.monthrefdate and \
          previouspay.monthseqnumber == nextpay.monthseqnumber and \
          previouspay.contract_id    == nextpay.contract_id:

        previouspay.paid_amount += nextpay.paid_amount
      else:
        consolidated_paymentlists.append(nextpay)
        previouspay = nextpay

    return consolidated_paymentlists

  def __init__(self, paid_amount, paydate, cobranca=None
            monthrefdate=None, monthseqnumber=1,
            contract_id=None
              ):
    '''

    When more than one payment is done on a single day, the Payment object is integrated.
    However, individual payments are registered inside a JSON field. This field is attribute 'bankrecordsjson'.

    This json is strutured as follows:
      {'paydate':<date>, 'paid_amount':<value>, 'bankaccount_id':<bid>,
       'seqorder_onday': <seq>, 'bankdocline': <banksdocstring>,
       'payeesname': <name>, 'paytype': <transfer|dep-money|dep-cheque>}
      */

    There is no need for 'contract_id' in the above JSON, because contract_id belongs to self, below.
    '''
    self.paid_amount     = paid_amount
    self.paydate         = paydate
    # monthrefdate, monthseqnumber & contract_id are keys to find corresponding bill for which payment was done
    self.cobranca        = cobranca
    self.bankrecordsjson = None
    # OBS: the person who pays is stored in the bank_deposit object, not here
    # the bank_deposit objects should be stored in the above list and the should be an NxM bridge table on database
    # ie, the deposit record complements: user and amount to this payment if a deposit has


    # This physical_money_payment below, if True, must be to set after object construction
    # self.physical_money_payment = False


  @property
  def monthrefdate(self):
    return self.cobranca.monthrefdate

  @property
  def monthseqnumber(self):
    return self.cobranca.monthseqnumber

  @property
  def contract_id(self):
    return self.cobranca.contract_id


  def __str__(self):
    return 'paid=%s on %s' %(str(self.paid_amount), str(self.paydate))



def adhoctest():
  pass

if __name__ == '__main__':
  adhoctest()
