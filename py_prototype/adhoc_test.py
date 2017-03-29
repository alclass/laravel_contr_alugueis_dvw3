#!/usr/bin/env python3
from cobranca_calculus import MoraMonthly

# test data
ref_month_year_orig_oblig = 0
valor_orig = 1000
data_para_atualizar_a_obrig = 3  # an integer for the time being
multa_percent = 0.10
juros_am_percent = 10
bool_aplicar_corr_monet = True

mora = MoraMonthly(
  ref_month_year_orig_oblig = 0,
  valor_orig = 1000,
  data_para_atualizar_a_obrig = 3,  # an integer for the time being
  multa_percent = 0.10,
  juros_am_percent = 10,
  bool_aplicar_corr_monet = True,
)

output = mora.report_mora()
print (output)
