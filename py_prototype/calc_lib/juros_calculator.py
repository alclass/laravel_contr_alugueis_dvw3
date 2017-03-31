#!/usr/bin/env python3

import decimal

class Juros:

  @staticmethod
  def calculate_fmontant_from_increment_factor_array(montant_i, month_by_month_increment_factor_array):

    prod = montant_i
    for j in range(len(month_by_month_increment_factor_array)):
      fraction = month_by_month_increment_factor_array[j]
      prod *= (1 + fraction)
      print ('prod [', j, '] by ', '{:.3f}'.format(fraction), '=>', '{:.2f}'.format(prod))
    return prod


def ad_hoc_test():
  fractions_array = [0.02, 0.015, 0.011]
  montant_i = 1000
  print ('montant_i = ', '{:.2f}'.format(montant_i))
  montant_f = Juros.calculate_fmontant_from_increment_factor_array(montant_i, fractions_array)
  print ('montant_f = ', '{:.2f}'.format(montant_f))

if __name__ == '__main__':
  ad_hoc_test()




