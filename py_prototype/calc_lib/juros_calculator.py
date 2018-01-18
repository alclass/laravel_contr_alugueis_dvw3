#!/usr/bin/env python3
from datetime import date
from copy import copy
# from datetime import timedelta
from dateutil.relativedelta import relativedelta
# import decimal


def equalize_array_sizes(l1, l2):
  lenl1 = len(l1); lenl2 = len(l2)
  if len(l1) == len(l2):
    return l1, l2
  min_size = min(lenl1, lenl2)
  if lenl1 > min_size:
    l1 = l1[ : min_size]
    return l1, l2
  if lenl2 > min_size:
    l2 = l2[ : min_size]
    return l1, l2
  raise ValueError('Logical Error in equalize_array_sizes(l1, l2)')

class Juros:

  @staticmethod
  def calculate_fmontant_from_increment_factor_array(montant_i, month_by_month_increment_factor_array):

    prod = montant_i
    for j in range(len(month_by_month_increment_factor_array)):
      fraction = month_by_month_increment_factor_array[j]
      prod *= (1 + fraction)
      # print ('prod [', j, '] by ', '{:.3f}'.format(fraction), '=>', '{:.2f}'.format(prod))
    return prod

  @staticmethod
  def calculate_fmontant_from_mo_by_mo_interest_n_corrmonet_array(
      imontant,
      mo_by_mo_interest_n_corrmonet_array,
      mo_by_mo_fraction
  ):
    fmontant = imontant
    mo_by_mo_interest_n_corrmonet_array, mo_by_mo_fraction = \
      equalize_array_sizes(mo_by_mo_interest_n_corrmonet_array, mo_by_mo_fraction)
    for c, interest_n_corrmonet in enumerate(mo_by_mo_interest_n_corrmonet_array):
      interest, corrmonet = interest_n_corrmonet
      monthfraction = mo_by_mo_fraction[c]
      fmontant = fmontant * (1 + ((interest + corrmonet) * monthfraction))
    return fmontant

  @staticmethod
  def fetch_corrmonet_for_month(monthdate):
    month = monthdate.month
    corrmonets = [
      0.005, 0.003, 0.006, 0.002,
      0.003, 0.003, 0.007, 0.001,
      0.002, 0.004, 0.006, 0.001,
    ]
    index = month - 1
    corrmonet = corrmonets[index]
    # line = 'corrmonet %s = %s' % (str(monthdate), str(corrmonet))
    # print (line)
    return corrmonet

  @staticmethod
  def gen_mo_by_mo_interest_plus_corrmonet_times_fraction_array(
      duedate,
      interestarray,
      mo_by_mo_fraction_array
  ):
    mo_by_mo_fraction_array, interestarray = equalize_array_sizes(mo_by_mo_fraction_array, interestarray)
    mo_by_mo_interest_plus_corrmonet_times_fraction_array = []
    for i, interest in enumerate(interestarray):
      ongoingdate = duedate + relativedelta(months=i)
      corrmonet = Juros.fetch_corrmonet_for_month(ongoingdate)
      integrated_fraction = (interest + corrmonet) * mo_by_mo_fraction_array[i]
      mo_by_mo_interest_plus_corrmonet_times_fraction_array.append(integrated_fraction)
    return mo_by_mo_interest_plus_corrmonet_times_fraction_array


def ad_hoc_test():
  fractions_array = [0.02, 0.015, 0.011]
  montant_i = 1000
  print ('montant_i = ', '{:.2f}'.format(montant_i))
  montant_f = Juros.calculate_fmontant_from_increment_factor_array(montant_i, fractions_array)
  print ('montant_f = ', '{:.2f}'.format(montant_f))

if __name__ == '__main__':
  ad_hoc_test()


import unittest

class TestJurosCalculator(unittest.TestCase):

  def setUp(self):
    self.juroscalculator = TestJurosCalculator()

  def test_equalize_array_sizes(self):
    l1 = [0, 1]; l2 = [10, 11]
    r1, r2 = equalize_array_sizes(l1, l2)
    self.assertEqual((l1, l2), (r1, r2))

    l1 = [0, 1, 2]; l2 = [10, 11]; l1_expected=[0,1]
    r1, r2 = equalize_array_sizes(l1, l2)
    self.assertEqual((l1_expected, l2), (r1, r2))

    l1 = [0, 1]; l2 = [100, 110, 90]; l2_expected=[100, 110]
    r1, r2 = equalize_array_sizes(l1, l2)
    self.assertEqual((l1, l2_expected), (r1, r2))

  def test_calculate_fmontant_from_mo_by_mo_interest_n_corrmonet_array(self):
    mo_by_mo_interest_n_corrmonet_array = [(0.01, 0.005), (0.01, 0.002), (0.01, 0.003)]
    mo_by_mo_increment_factor_array     = [0.5, 1, 0.3]
    imontant = 1000
    expected_fmontant = \
      1000 * \
      (1 + ((0.01+0.005)*0.5)) *\
      (1 + ((0.01+0.002)*1)) *\
      (1 + ((0.01+0.003)*0.3))

    returned_fmontant = Juros.calculate_fmontant_from_mo_by_mo_interest_n_corrmonet_array(
      imontant,
      mo_by_mo_interest_n_corrmonet_array,
      mo_by_mo_increment_factor_array
    )

    self.assertEqual(expected_fmontant, returned_fmontant)


  def test_gen_mo_by_mo_interest_plus_corrmonet_times_fraction_array(self):
    duedate = date(2018, 1, 10)
    mo_by_mo_fraction_array = [(31-10)/31, 1, 15/31]
    interestarray = [0.01]*3
    returned_mo_by_mo_interest_plus_corrmonet_times_fraction_array = \
      Juros.gen_mo_by_mo_interest_plus_corrmonet_times_fraction_array(
        duedate,
        interestarray,
        mo_by_mo_fraction_array
      )
    corrmonets = []
    nextmonth = copy(duedate)
    corrmonet = Juros.fetch_corrmonet_for_month(nextmonth)
    corrmonets.append(corrmonet)
    nextmonth = nextmonth + relativedelta(months=+1)
    corrmonet = Juros.fetch_corrmonet_for_month(nextmonth)
    corrmonets.append(corrmonet)
    nextmonth = nextmonth + relativedelta(months=+1)
    corrmonet = Juros.fetch_corrmonet_for_month(nextmonth)
    corrmonets.append(corrmonet)

    expected_mo_by_mo_interest_plus_corrmonet_times_fraction_array = \
      [
        (0.01 + corrmonets[0]) * ((31 - 10) / 31),
        (0.01 + corrmonets[1]) * 1,
        (0.01 + corrmonets[2]) * (15/31),
      ]

    self.assertEqual(
      expected_mo_by_mo_interest_plus_corrmonet_times_fraction_array,
      returned_mo_by_mo_interest_plus_corrmonet_times_fraction_array
    )

unittest.main()