#!/usr/bin/env python3
from datetime import date
from copy import copy
# from datetime import timedelta
from dateutil.relativedelta import relativedelta
# import decimal
import sys

INTEREST_RATE_DEFAULT = 0.01
MULTA_RATE_DEFAULT    = 0.1

CORRMONET_INDICES = {
  2017: [
    0.010, 0.003, 0.002, 0.003,
    0.004, 0.002, 0.006, 0.001,
    0.001, 0.003, 0.003, 0.004,
  ],
  2018: [
    0.005, 0.002, 0.006, 0.002,
    0.003, 0.003, 0.007, 0.003,
    0.002, 0.004, 0.006, 0.001,
  ],
}

def get_default_interest_rate():
  return INTEREST_RATE_DEFAULT

def get_default_multa_rate():
  return MULTA_RATE_DEFAULT

def equalize_array_sizes(l1, l2):
  lenl1 = len(l1); lenl2 = len(l2)
  if lenl1 == lenl2:
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
  def fetch_corrmonet_for_month(monthrefdate):
    year  = monthrefdate.year
    month = monthrefdate.month
    monthzeroindex = month - 1
    try:
      cm_index = CORRMONET_INDICES[year][monthzeroindex]
    except IndexError:
      cm_index = 0.005
    return cm_index

  @staticmethod
  def calculate_fmontant_from_increment_factor_array(
      montant_i,
      month_by_month_increment_factor_array
    ):
    fmontant = montant_i
    for j in range(len(month_by_month_increment_factor_array)):
      fraction = month_by_month_increment_factor_array[j]
      fmontant *= (1 + fraction)
      # print ('prod [', j, '] by ', '{:.3f}'.format(fraction), '=>', '{:.2f}'.format(prod))
    return fmontant

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
  def gen_mo_by_mo_interest_plus_corrmonet_times_fraction_array(
      monthrefdate,
      interestarray,
      mo_by_mo_fraction_array,
      usesMminus1CorrMonetIndex=True
  ):
    mo_by_mo_fraction_array, interestarray = equalize_array_sizes(mo_by_mo_fraction_array, interestarray)
    mo_by_mo_interest_plus_corrmonet_times_fraction_array = []
    for i, interest in enumerate(interestarray):
      ongoingdate = monthrefdate + relativedelta(months=i)
      if usesMminus1CorrMonetIndex:
        corrmonetmonthdate = ongoingdate + relativedelta(months=-1)
      else:
        corrmonetmonthdate = copy(ongoingdate)
      corrmonet   = Juros.fetch_corrmonet_for_month(corrmonetmonthdate)
      integrated_fraction = (interest + corrmonet) * mo_by_mo_fraction_array[i]
      mo_by_mo_interest_plus_corrmonet_times_fraction_array.append(integrated_fraction)
    return mo_by_mo_interest_plus_corrmonet_times_fraction_array

  '''
  @staticmethod
  def apply_multa_interest_n_corrmonet(value, monthrefdate, contractrule=None):
    interest = get_default_interest_rate()
    multa    = get_default_multa_rate()
    if contractrule is not None:
      interest = contractrule.get_interest_rate()
      multa    = contractrule.get_multa_rate()
    corrmonet = Juros.fetch_corrmonet_for_month(monthrefdate)
    compounded_rate = multa + interest + corrmonet
    return value * (1 + compounded_rate)
  '''

  @staticmethod
  def apply_interest_n_corrmonet(value, monthrefdate, contractrule=None):
    interest = get_default_interest_rate()
    if contractrule is not None:
      interest = contractrule.get_interest_rate()
    corrmonet = Juros.fetch_corrmonet_for_month(monthrefdate)
    compounded_rate = interest + corrmonet
    return value * (1 + compounded_rate)

def ad_hoc_test():
  fractions_array = [0.02, 0.015, 0.011]
  montant_i = 1000
  print ('montant_i = ', '{:.2f}'.format(montant_i))
  montant_f = Juros.calculate_fmontant_from_increment_factor_array(montant_i, fractions_array)
  print ('montant_f = ', '{:.2f}'.format(montant_f))


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


if __name__ == '__main__':
  ad_hoc_test()
  if len(sys.argv) > 1 and sys.argv[1] == '-u':
    del sys.argv[1]
    unittest.main()
