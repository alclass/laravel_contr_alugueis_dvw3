#!/usr/bin/env python3
from copy import copy
from datetime import date
from datetime import timedelta
from dateutil.relativedelta import relativedelta
# import unittest

from .BillMod import Bill

class Payment:
  '''
  attributes:
    + total_paid
    + payment_date
  '''
  def __init__(self, total_paid, payment_date):
    self.total_paid   = total_paid
    self.payment_date = payment_date
    self.total_transferred = 0

  def __str__(self):
    return 'paid=%s on %s' %(str(self.total_paid), str(self.payment_date))


class PaymentProcessor:

  def __init__(self, payment_obj, counterpart_bill):

    self.payment_obj      = payment_obj
    self.counterpart_bill = counterpart_bill

  def process_payment(self, payment_obj, counterpart_bill):
    '''
    ->process_payment must be run() before ->generate_monthly_bill()

    THIS METHOD MUST BE A.C.I.D. on its database side (to-do, to verify/validate/unit-test)

    :param payment_obj:
    :param counterpart_bill:
    :return:
    '''
    if self.payment_obj.total_paid == 0:
      return
    # if something goes wrong, restablish object at the end
    backup_counterpart_bill = copy(self.counterpart_bill)
    if self.counterpart_bill.is_late_on_duedate(self.payment_obj.paydate):
      self.counterpart_bill.apply_late_mora()

    # credit payment to bill, debit due-value on bill (this is a debt/credit transaction)
    if self.payment_obj.total_paid == self.counterpart_bill.payment_due:
      self.counterpart_bill.payment_due = 0
      self.counterpart_bill.debi_to_carry = 0
      self.counterpart_bill.debo_to_carry = 0
      self.counterpart_bill.payment_done = self.payment_obj.total_paid
      self.payment_obj.total_transferred = self.payment_obj.total_paid
      self.payment_obj.total_paid = 0
    elif self.payment_obj.total_paid < self.counterpart_bill.payment_due:
      counterpart_bill.payment_due -= self.payment_obj.total_paid
      if self.counterpart_bill.monthy_amount >= self.counterpart_bill.payment_due:
        self.counterpart_bill.debi_to_carry = self.counterpart_bill.payment_due
        self.counterpart_bill.payment_done = self.payment_obj.total_paid
        self.payment_obj.total_transferred = self.payment_obj.total_paid
      else:
        self.counterpart_bill.debi_to_carry = self.counterpart_bill.monthy_amount
        self.counterpart_bill.debo_to_carry = self.counterpart_bill.payment_due - self.counterpart_bill.monthy_amount
        self.counterpart_bill.payment_done = self.payment_obj.total_paid
        self.payment_obj.total_transferred = self.payment_obj.total_paid
    else: # ie payment_obj.total_paid > counterpart_bill.payment_due:
      self.counterpart_bill.payment_due = 0
      self.counterpart_bill.cred_to_carry = self.payment_obj.total_paid - self.counterpart_bill.payment_due
      self.counterpart_bill.debi_to_carry = 0
      self.counterpart_bill.debo_to_carry = 0
      self.counterpart_bill.payment_done = self.payment_obj.total_paid
      self.payment_obj.total_transferred = self.payment_obj.total_paid

    open_payments = self.get_open_payments()
    duedate = self.monthyeardateref - relativedelta(months=-1)
    duedate = duedate.replace(day=10)
    for paydate in open_payments:
      if paydate > duedate:
        mora_in_days = paydate - timedelta()


def get_nonprocessed_payments_ordered_by_date():
  payments = []
  paydate = date(2018, 1, 10)
  payment_obj = Payment(total_paid=1000, payment_date=paydate)
  payments.append(payment_obj)
  paydate = date(2018, 1, 15)
  payment_obj = Payment(total_paid=500, payment_date=paydate)
  payments.append(payment_obj)


def adhoctest():
  counterpart_bill = None
  payments = get_nonprocessed_payments_ordered_by_date()
  for payment_obj in payments:
    payproc = PaymentProcessor(payment_obj, counterpart_bill)
    print (payproc)

if __name__ == '__main__':
  adhoctest()
