#!/usr/bin/env python3
from copy import copy
from datetime import date
#from datetime import timedelta
from dateutil.relativedelta import relativedelta
import unittest
#import calendar
'''
Info on method monthrange()
=> calendar.monthrange(year, month) returns a 2-tuple (weekdayindex, number of days in month)
weekdayindex is 0 for Monday, 1 for Tuesday, on until 6 for Sunday
'''

class Bill:

  def __init__(self):
    pass

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


class TestMonthRefs(unittest.TestCase):

  def setUp(self):
    self.bill_obj = Bill()

  def test_generate_conventioned_monthyeardateref_against_given_date_before_day10(self):
    test_date = date(2017,1,9)
    expected_monthref = date(2017,1,1)
    return_monthref = self.bill_obj.generate_conventioned_monthyeardateref_against_given_date(test_date)
    self.assertEqual(expected_monthref, return_monthref)
    test_date = date(2017,1,1)
    expected_monthref = date(2017,1,1)
    return_monthref = self.bill_obj.generate_conventioned_monthyeardateref_against_given_date(test_date)
    self.assertEqual(expected_monthref, return_monthref)

  def test_generate_conventioned_monthyeardateref_against_given_date_after_day10(self):
    test_date = date(2017,1,11)
    expected_monthref = date(2017,2,1)
    return_monthref = self.bill_obj.generate_conventioned_monthyeardateref_against_given_date(test_date)
    self.assertEqual(expected_monthref, return_monthref)
    test_date = date(2017,1,31)
    expected_monthref = date(2017,2,1)
    return_monthref = self.bill_obj.generate_conventioned_monthyeardateref_against_given_date(test_date)
    self.assertEqual(expected_monthref, return_monthref)

  def test_generate_conventioned_monthyeardateref_against_given_date_on_day10(self):
    test_date = date(2017,1,10)
    expected_monthref = date(2017,1,1)
    return_monthref = self.bill_obj.generate_conventioned_monthyeardateref_against_given_date(test_date)
    self.assertEqual(expected_monthref, return_monthref)

  def test_generate_conventioned_monthyeardateref_against_today(self):
    today = date.today()
    expected_monthref = date(today.year, today.month, 1)
    if today.day > 10:
      expected_monthref = expected_monthref + relativedelta(months=+1)
    return_monthref = self.bill_obj.generate_conventioned_monthyeardateref_against_given_date(None)
    self.assertEqual(expected_monthref, return_monthref)


def adhoctest():
  b = Bill()
  d = b.generate_conventioned_monthyeardateref_against_given_date()
  print (d)

if __name__ == '__main__':
  adhoctest()


unittest.main()