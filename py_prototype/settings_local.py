#/usr/bin/env python3
'''
settings_local.py
'''
import os

class AppPaths:

  LOCAL_SETTING_FILENAME   = 'settings_local.py'
  DATABASE_FOLDERNAME      = 'database' # it's in app_root_abspath
  SQLITE_DATABASE_FILENAME = 'real_estate_rents.sqlite'

  @staticmethod
  def get_app_package_root_abspath():
    absfilepath = os.path.abspath(__file__)
    absfolderpath, _ = os.path.split(absfilepath)
    descending_abspath = absfolderpath
    while True:
      files_in_folder = os.listdir(descending_abspath)
      if __class__.LOCAL_SETTING_FILENAME in files_in_folder:
        app_package_root_abspath = descending_abspath
        return app_package_root_abspath # got it!
      if descending_abspath == '/':
        return None
      # go one dir down and go loop on again
      descending_abspath, _ = os.path.split(descending_abspath)
      print ( 'descending_abspath', descending_abspath )
    raise Exception('While-loop should have returned and not gotten here. Logical/Programming error.')

  @staticmethod
  def get_database_folder_abspath():
    app_package_root_abspath = __class__.get_app_package_root_abspath()
    if app_package_root_abspath is None:
      return None
    database_folder_abspath = os.path.join(app_package_root_abspath, __class__.DATABASE_FOLDERNAME)
    return database_folder_abspath

  @staticmethod
  def get_sqlite_database_abspath():
    database_folder_abspath = __class__.get_database_folder_abspath()
    if database_folder_abspath is None:
      return None
    sqlite_database_abspath = os.path.join(database_folder_abspath, __class__.SQLITE_DATABASE_FILENAME)
    return sqlite_database_abspath


def adhoc_test():
  print ('-'*70)
  print ('app_package_root_abspath = [[', AppPaths.get_app_package_root_abspath(), ']]')
  print ('-'*70)
  print ('database_folder_abspath  = [[', AppPaths.get_database_folder_abspath(), ']]')
  print ('-'*70)
  print ('sqlite_database_abspath  = [[', AppPaths.get_sqlite_database_abspath(), ']]')
  print ('-'*70)

if __name__ == '__main__':
  adhoc_test()