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
  from BillMod import create_adhoctest_bill


class Payment:
  '''
  attributes:
    + paid_amount
    + paydate
  '''
  def __init__(self, paid_amount, paydate):
    self.paid_amount = paid_amount
    self.paydate     = paydate
    self.is_payment_transaction_done= False

  def __str__(self):
    return 'paid=%s on %s' %(str(self.paid_amount), str(self.paydate))


class PaymentProcessor:

  def __init__(self, payment_obj, counterpart_bill):

    self.payment_obj      = payment_obj
    self.counterpart_bill = counterpart_bill

  def process_payment(self):
    '''
    process_payment() must be run before generate_monthly_bill()

    THIS METHOD MUST BE A.C.I.D. on its database side (to-do, to verify/validate/unit-test)

    :param counterpart_bill:
    :return:
    '''
    if self.payment_obj.paid_amount == 0 or self.payment_obj.is_payment_transaction_done:
      return
    # if something goes wrong, restablish object at the end
    backup_counterpart_bill = copy(self.counterpart_bill)
    self.counterpart_bill.apply_late_mora_if_tardy(self.payment_obj.paydate)

    # credit payment to bill, debit due-value on bill (this is a debt/credit transaction)
    if self.payment_obj.paid_amount == self.counterpart_bill.total_due:
      self.counterpart_bill.payment_missing = 0
      self.counterpart_bill.debi_to_carry   = 0
      self.counterpart_bill.debo_to_carry   = 0
    elif self.payment_obj.paid_amount < self.counterpart_bill.total_due:
      self.counterpart_bill.payment_missing = self.counterpart_bill.total_due - self.payment_obj.paid_amount

      if self.payment_obj.paid_amount >= self.counterpart_bill.debo_to_carry:
        diminish_from_debi = self.payment_obj.paid_amount - self.counterpart_bill.debo_to_carry
        self.counterpart_bill.debo_to_carry = 0
        self.counterpart_bill.debi_to_carry -= diminish_from_debi
      else:
        self.counterpart_bill.debo_to_carry -= self.payment_obj.paid_amount
    else: # ie payment_obj.paid_amount > counterpart_bill.payment_due:
      self.counterpart_bill.add_to_cred_to_carry(self.payment_obj.total_paid - self.counterpart_bill.total_due)
      self.counterpart_bill.debi_to_carry = 0
      self.counterpart_bill.debo_to_carry = 0

    self.counterpart_bill.payment_done = self.payment_obj.paid_amount
    self.payment_obj.is_payment_transaction_done = True



def get_nonprocessed_payments_ordered_by_date():
  payments = []
  paydate = date(2018, 2, 10)
  payment_obj = Payment(paid_amount=1000, paydate=paydate)
  payments.append(payment_obj)
  paydate = date(2018, 2, 15)
  payment_obj = Payment(paid_amount=500, paydate=paydate)
  payments.append(payment_obj)
  return payments

def adhoctest():
  counterpart_bill = create_adhoctest_bill()
  print(counterpart_bill)
  payments = get_nonprocessed_payments_ordered_by_date()
  for payment_obj in payments:
    print (payment_obj)
    payproc = PaymentProcessor(payment_obj, counterpart_bill)
    payproc.process_payment()
    print (counterpart_bill)

if __name__ == '__main__':
  adhoctest()
