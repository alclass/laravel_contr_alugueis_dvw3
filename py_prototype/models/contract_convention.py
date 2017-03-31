#!/usr/bin/env python3

import datetime, decimal
'''
import os, sys
PACKAGE_PARENT = '..'
SCRIPT_DIR = os.path.dirname(os.path.realpath(os.path.join(os.getcwd(), os.path.expanduser(__file__))))
sys.path.append(os.path.normpath(os.path.join(SCRIPT_DIR, PACKAGE_PARENT)))
'''

from calc_lib.datetime_expansion as dateexp

class ContractConvention:

  def __init__(self):

    self.multa_contratual = 0.1
    self.apply_multa_contratual_1_vez = True
    self.juros_am = 0.01
    self.apply_juros_am = True
    self.apply_corr_monet = True

  def get_increment_factor_array_month_by_month(self, month_by_month_increment_factor_array, initial_monthyear):
    month_by_month_increment_fractions_array = []
    for i in range(len(month_by_month_increment_factor_array)):
      tx = 0.0
      if i == 0: # ie, the first loop round, the first month in mora
        if self.apply_multa_contratual_1_vez:
          tx += self.multa_contratual
      if self.apply_juros_am:
        tx += self.juros_am
      if self.apply_corr_monet:
        n_months = i
        monthyear_for_corr_monet = RD.add_n_months_to_monthyear(initial_monthyear, n_months)
        try:
          corr_monet = RD.find_corr_monet_in_n_months_after_monthyear(n_months_after=i, monthyear=initial_monthyear)
        except ValueError:  # TO-DO: create a CorrMonetFetchError in the future !!!
          raise AttributeError('Could not fetch the Corr Monet value')
          #corr_monet = 0.0
        tx += corr_monet
      month_by_month_increment_fractions_array.append(tx)
    return month_by_month_increment_fractions_array

    prod = montant_i
    for j in range(len(month_by_month_increment_factor_array)):
      fraction = month_by_month_increment_factor_array[j]
      prod *= (1 + fraction)
      print ('prod [', j, '] by ', '{:.3f}'.format(fraction), '=>', '{:.2f}'.format(prod))
    return prod

def ad_hoc_test():
  contr_conv = ContractConvention()
  initial_monthyear = datetime.date(day=1, month=5, year=2016)
  month_by_month_increment_factor_array = [1,1,1,0.33]
  print ('get_increment_factor_array_mo_by_mo() on parameters initial_monthyear = ', initial_monthyear, 'AND month_by_month_increment_factor_array = ', month_by_month_increment_factor_array)
  increment_factor_array_mo_by_mo = contr_conv.get_increment_factor_array_mo_by_mo(month_by_month_increment_factor_array, initial_monthyear)
  print ( increment_factor_array_mo_by_mo )

if __name__ == '__main__':
  ad_hoc_test()

