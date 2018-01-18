#!/usr/bin/env python3

from copy import copy
from datetime import date
# from datetime import timedelta
from dateutil.relativedelta import relativedelta
# import unittest
import calendar # calendar.monthrange(year, month)




class DateBillCalculator:

  def calc_ndays_between_dates(self, startdate, enddate):
    if enddate >= startdate:
      delta = enddate - startdate
      return delta.days + 1
    else:
      return 0

  def calc_mo_by_mo_days_between_dates(self, startdate, enddate):
    ndays = self.calc_ndays_between_dates(startdate, enddate)
    if ndays < 1:
      return []
    return self.calc_mo_by_mo_days(startdate, ndays)

  def calc_mo_by_mo_days(self, mora_start_date, p_n_days):
    n_days = p_n_days
    mo_by_mo_days = []
    ongoing_date = copy(mora_start_date)
    while n_days > 0:
      year  = ongoing_date.year
      month = ongoing_date.month
      day   = ongoing_date.day
      lastdayinmonth = calendar.monthrange(year, month)[1]
      days_to_complete_month = lastdayinmonth - day + 1
      if days_to_complete_month >= n_days:
        mo_by_mo_days.append(n_days)
        return mo_by_mo_days
      n_days -= days_to_complete_month
      mo_by_mo_days.append(days_to_complete_month)
      ongoing_date = ongoing_date + relativedelta(months=+1)
      ongoing_date = ongoing_date.replace(day=1)
    return mo_by_mo_days

  def transform_monthdays_into_monthfractions(self, mo_by_mo_days, firstmonthdate):
    ongoingmonthdate = copy(firstmonthdate)
    monthfractions = []
    for ndays in mo_by_mo_days:
      month = ongoingmonthdate.month
      year  = ongoingmonthdate.year
      daysinthatmonth = calendar.monthrange(year, month)[1]
      monthfraction = ndays / daysinthatmonth
      monthfractions.append(monthfraction)
      ongoingmonthdate = ongoingmonthdate + relativedelta(months=+1)
    return monthfractions

import unittest

class TestDateBill(unittest.TestCase):

  def setUp(self):
    self.datebillcalculator = DateBillCalculator()

  def test_calc_mo_by_mo_days(self):
    mora_start_date = date(2018, 1, 15)
    p_n_days = 1
    expected_mo_by_mo_days = [1]
    returned_mo_by_mo_days = self.datebillcalculator.calc_mo_by_mo_days(mora_start_date, p_n_days)
    self.assertEqual(expected_mo_by_mo_days, returned_mo_by_mo_days)

    mora_start_date = date(2018, 1, 1)
    p_n_days = 31
    expected_mo_by_mo_days = [31]
    returned_mo_by_mo_days = self.datebillcalculator.calc_mo_by_mo_days(mora_start_date, p_n_days)
    self.assertEqual(expected_mo_by_mo_days, returned_mo_by_mo_days)

    mora_start_date = date(2018, 1, 15)
    p_n_days = 31
    expected_mo_by_mo_days = [17, 14]
    returned_mo_by_mo_days = self.datebillcalculator.calc_mo_by_mo_days(mora_start_date, p_n_days)
    self.assertEqual(expected_mo_by_mo_days, returned_mo_by_mo_days)

    mora_start_date = date(2018, 1, 1)
    p_n_days = 365
    expected_mo_by_mo_days = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31]
    returned_mo_by_mo_days = self.datebillcalculator.calc_mo_by_mo_days(mora_start_date, p_n_days)
    self.assertEqual(expected_mo_by_mo_days, returned_mo_by_mo_days)


    mora_start_date = date(2018, 1, 15)
    p_n_days = 53
    expected_mo_by_mo_days = [17, 28, 8]
    returned_mo_by_mo_days = self.datebillcalculator.calc_mo_by_mo_days(mora_start_date, p_n_days)
    self.assertEqual(expected_mo_by_mo_days, returned_mo_by_mo_days)

  def test_calc_mo_by_mo_days_between_dates(self):
    d1 = date(2018, 1, 10)
    d2 = date(2018, 1, 15)
    expected_days_between_dates = 6
    returned_expected_days_between_dates = self.datebillcalculator.calc_ndays_between_dates(d1, d2)
    self.assertEqual(expected_days_between_dates, returned_expected_days_between_dates)

  def test_transform_monthdays_into_monthfractions(self):
    mo_by_mo_days = [15, 12, 3]
    firstmonthdate = date(2018, 1, 1)
    expected_monthfractions = [15/31, 12/28, 3/31]
    returned_monthfractions = self.datebillcalculator\
      .transform_monthdays_into_monthfractions(
      mo_by_mo_days,
      firstmonthdate
    )

unittest.main()