#/usr/bin/env python3
'''
__init__.py in calc_lib package
'''

import os, sys
PACKAGE_PARENT = '..'
SCRIPT_DIR = os.path.dirname(os.path.realpath(os.path.join(os.getcwd(), os.path.expanduser(__file__))))
sys.path.append(os.path.normpath(os.path.join(SCRIPT_DIR, PACKAGE_PARENT)))

import settings_local as sl

def getAppPaths():
  return sl.AppPaths

# === adhoc_test() ===
def adhoc_test():
  sqlite_database_abspath = getAppPaths().get_sqlite_database_abspath()
  print (sqlite_database_abspath, sqlite_database_abspath)
  sl.adhoc_test()

if __name__ == '__main__':
  adhoc_test()