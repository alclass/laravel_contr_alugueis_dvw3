#!/usr/bin/env python3
import datetime
from dateutil.relativedelta import relativedelta


def add_n_months_to_date(p_date, p_n_months):
  return p_date + relativedelta(months=p_n_months)

def add_n_months_to_date_oldimpl(p_date, p_n_months):

  # 1st step: how many years are there in p_n_months?
  n_years = p_n_months // 12
  projected_monthyear =  datetime.date(year=p_date.year+n_years, month=p_date.month, day=p_date.day)
  # 2nd step: treat months remainder, add it to original month, check if year increased
  orig_n_month = projected_monthyear.month
  n_months_remainder = p_n_months % 12
  n_month = orig_n_month + n_months_remainder
  if n_month > 12:
    projected_monthyear = datetime.date(year=projected_monthyear.year+1, month=n_month-12, day=projected_monthyear.day)
  else:
    projected_monthyear = datetime.date(year=projected_monthyear.year, month=n_month, day=projected_monthyear.day)
  return projected_monthyear

def date_for_next_month(ref_monthyear):
  return add_n_months_to_date(ref_monthyear)

def get_afterdatesmonth_the_month_by_month_time_fraction_array(ref_monthyear, end_date):
  start_date      = date_for_next_month(ref_monthyear)
  months_and_days = months_and_days_inbetween(end_date, start_date)
  n_months        = months_and_days[0]
  n_days          = months_and_days[1]
  fraction_months = [1]*n_months
  fraction_days   = 0
  if end_date.month in [1,3,5,7,8,10,12]:
    fraction_days = n_days / 31.0
  elif end_date.month in [4,6,9,11]:
    fraction_days = n_days / 30.0
  elif end_date.month in [2]:
    if is_leap_year(end_date.year):
      fraction_days = n_days / 29.0
    else:
      fraction_days = n_days / 28.0
  # setting the month_by_month_array_fractions
  month_by_month_array_fractions = [1]*n_months
  month_by_month_array_fractions.append(fraction_days)
  return month_by_month_array_fractions

def is_leap_year(year):
  if year % 4 == 0:
    if year % 400 == 0:
      return True
    if year % 100 == 0:
      return False
    return True
  return False

def months_and_days_inbetween(p_date_earlier, p_date_remoter):
  date_earlier = p_date_earlier
  date_remoter = p_date_remoter
  if p_date_earlier < p_date_remoter:
    date_earlier = p_date_remoter
    date_remoter = p_date_earlier
  n_months = (date_earlier.year - date_remoter.year) * 12 + date_earlier.month - date_remoter.month
  n_days = date_earlier.day - date_remoter.day
  return n_months, n_days


class DateCalculator:
  '''
  This class expands Python's datetime standard module's functionalities

  '''

  def __init__(self, p_date):
    self.date_in_obj = p_date

  def add_n_months_to_date(self, p_n_months)
    p_monthyear = self.date_in_obj
    return add_n_months_to_date(p_monthyear, p_n_months)

  def date_for_next_month(self, ref_monthyear):
    ref_monthyear = self.date_in_obj

  def is_leap_year(self, year):
    year = self.date_in_obj.year
    return is_leap_year(year)

  def get_afterdatesmonth_the_month_by_month_time_fraction_array(self, end_date):
    ref_monthyear = self.date_in_obj
    return get_month_by_month_time_fraction_array(ref_monthyear, end_date)

  # def find_corr_monet_in_n_months_after_monthyear(p_monthyear):

  def months_and_days_inbetween(self, p_date_remoter):
    p_date_earlier = self.date_in_obj
    return months_and_days_inbetween(p_date_earlier, p_date_remoter)
    date_earlier = p_date_earlier


def ad_hoc_test():
  day = 1
  month = 5
  year = 2012
  date1 = datetime.date(year=year, month=month, day=day)
  print ('date 1 ==>>> ' , date1)
  day = 15
  month = 8
  year = 2012
  date2 = datetime.date(year=year, month=month, day=day)
  print ('date 2 ==>>> ' , date2 )
  array_months_and_days = months_and_days_inbetween(date2, date1)
  print ('array_months_and_days() ==>>> ' , )
  print (array_months_and_days)
  month = 4
  ref_monthyear = datetime.date(year=year, month=month, day=day)
  months_n_days = get_month_by_month_time_fraction_array(ref_monthyear, date2)
  print ('get_month_by_month_time_fraction_array() ==>>> ' , )
  print (months_n_days)

  # ==================================================
  print ('='*50)

  # test 2
  p_monthyear = date2
  p_n_months = 5
  print ('p_monthyear = ' , p_monthyear, ' :: Adding p_n_months =', p_n_months)
  projected_date = add_n_months_to_monthyear(p_monthyear, p_n_months)
  print ('projected_date =', projected_date)


  year = 2017; month = 12
  cm = CorrMonetFetcher.fetch(monthyear = datetime.date(year=year, month=month, day=1))
  print('year', year, 'month', month, 'c.m.', cm)

if __name__ == '__main__':
  ad_hoc_test()
