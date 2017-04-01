#!/usr/bin/env python3
import datetime, os, sys
import sqlite3
import __init__


def get_corr_monet_percent_for_month_year(month_year=None):
  sqlite_database_abspath = __init__.getAppPaths().get_sqlite_database_abspath()
  conn = sqlite3.connect(sqlite_database_abspath)
  sql = '''SELECT corr_monet FROM corr_monet_indices
   WHERE
     year = ? AND
     month = ?
  '''
  cursor = conn.cursor()
  month = month_year.month
  year  = month_year.year
  values = [month, year]
  result_set = cursor.execute(sql, values)
  if result_set:
    record = result_set.fetchone()
    corr_monet = record[0]
  return corr_monet

class CorrMonetFetcher:

  @staticmethod
  def fetch(monthyear):
    year  = monthyear.year
    month = monthyear.month
    try:
      corr_monet = __class__.corr_monet_dict[year][month]
      return corr_monet
    except (AttributeError, KeyError) as e:
      print (e)
      return None

def adhoc_test():
  month_year = datetime.date(year=2016, month=1, day=1)
  corr_monet = get_corr_monet_percent_for_month_year(month_year)
  print('corr_monet', corr_monet, 'for', month_year)


if __name__ == '__main__':
  adhoc_test()
