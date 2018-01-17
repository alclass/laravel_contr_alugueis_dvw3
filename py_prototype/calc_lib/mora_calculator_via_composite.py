#!/usr/bin/env python3

import datetime, decimal
from calc_lib.juros_calculator import Juros
import calc_lib.datatime_expanded as dateexp
import models.calc_lib.datatime_expanded as dateexp

def generate_month_sequence(month_year, up_til_date):
  pass


def get_corr_monet_percent_for_month_year(month_year):
  pass

class Mora:

  def __init__(self, from_value, ref_monthyear, calc_closing_date, contract_convention ):

    self.from_value          = from_value
    self.ref_monthyear       = ref_monthyear
    self.calc_closing_date   = calc_closing_date
    self.contract_convention = contract_convention


  def calculate_billing_value(self):
    month_by_month_time_fraction_array = dateexp.get_afterdatesmonth_the_month_by_month_time_fraction_array(self.ref_monthyear, self.calc_closing_date)
    month_by_month_increment_factor_array = self.contract_convention.get_increment_factor_array_mo_by_mo( month_by_month_time_fraction_array )
    return Juros.calculate_final_montant(self.from_value, month_by_month_increment_factor_array)


  def get_corr_monet_percent_for_month_year(self, month_year=None):
    return get_corr_monet_percent_for_month_year(month_year)

  def __str__(self):
    lines = []
    line = 'Mora Contratual Convencionada:'; lines.append(line)
    line = '=============================='; lines.append(line)
    line = 'Multa Contratual (em percentual): %s' %('{:.1f}'.format(self.multa_contr_percent)); lines.append(line)
    line = 'Juros a.m. (em percentual): %s' %('{:.1f}'.format(self.juros_am_percent)); lines.append(line)
    if self.bool_aplicar_corr_monet:
      line = 'Aplicar Corr. Monet. (ao mês ref. em tabela pública).'; lines.append(line)
    line = '=============================='; lines.append(line)
    outstr = '\n'.join(lines)
    return outstr

if __name__ == '__main__':
  pass
