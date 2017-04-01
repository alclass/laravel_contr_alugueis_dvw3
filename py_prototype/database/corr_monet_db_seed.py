#!/usr/bin/env python3
#import datetime, os, sys
from collections import OrderedDict
import sqlite3
import __init__

# seed data in a dict (to be put inside a sqlite db table [or maybe some other db])
corr_monet_dict = {
  2015: {
    1: 0.2,
    2: 0.2,
    3: 0.2,
    4: 0.2,
    5: 0.2,
    6: 0.2,
    7: 0.2,
    8: 0.2,
    9: 0.38,
    10: 0.38,
    11: 0.38,
    12: 0.38,
  },
  2016: {
    1: 0.2,
    2: 0.2,
    3: 0.2,
    4: 0.2,
    5: 0.2,
    6: 0.2,
    7: 0.2,
    8: 0.2,
    9: 0.38,
    10: 0.38,
    11: 0.38,
    12: 0.38,
  },
  2017: {
    1: 0.2,
    2: 0.2,
    3: 0.2,
    4: 0.2,
    5: 0.2,
    6: 0.2,
    7: 0.2,
    8: 0.2,
    9: 0.38,
    10: 0.38,
    11: 0.38,
    # 12: 0.38,
  },
}

def create_table():
  sql = '''
  CREATE TABLE IF NOT EXISTS corr_monet_indices (
    corr_monet DECIMAL(5,3),
    month INTEGER,
    year INTEGER
  )
  '''
  sqlite_database_abspath = __init__.getAppPaths().get_sqlite_database_abspath()
  conn = sqlite3.connect(sqlite_database_abspath)
  cursor = conn.cursor()
  cursor.execute(sql)
  conn.close()

def insert_data():

  sqlite_database_abspath = __init__.getAppPaths().get_sqlite_database_abspath()
  conn = sqlite3.connect(sqlite_database_abspath)
  cursor = conn.cursor()

  corr_monet_dict_sorted = OrderedDict(sorted(corr_monet_dict.items()))
  counter = 0
  for year in corr_monet_dict_sorted:
    months_cm_data_dict = corr_monet_dict_sorted[year]
    months_cm_data_dict_sorted = OrderedDict(sorted(months_cm_data_dict.items()))
    for month in months_cm_data_dict_sorted:
      counter += 1
      corr_monet = months_cm_data_dict_sorted[month]
      #sql = 'INSERT into corr_monet_indices (corr_monet, month, year) VALUES (:corr_monet, :month, :year)'
      sql = 'INSERT INTO corr_monet_indices (corr_monet, month, year) VALUES (?, ?, ?)'
      print (sql)
      values = [corr_monet, year, month]
      #cursor.execute(sql, {'corr_monet': corr_monet, 'year':year, 'month':month})
      cursor.execute(sql, values)
  conn.commit()
  conn.close()


def db_seed():
  print ('create_table()')
  create_table()
  print ('insert_data()')
  insert_data()

def adhoc_test():
  pass

if __name__ == '__main__':
  db_seed()
