#!/usr/bin/env python3
import datetime
from models import User, Imovel, MonthlyQuinhoesOfMora


def get_corr_monet_percent_for_month_year(month_year=None):
  return 0.38

class MoraTotal:

  # for the time being month_year is "integer"!
  # 0 is the origin, 1 the first month in delay, 2 the second month in delay and so on

  def __init__(self,
               ref_month_year_orig_oblig,
               valor_orig,
               data_para_atualizar_a_obrig, # an integer for the time being
               contract_conventions = None,
               bool_aplicar_corr_monet=True,
               discount_percent = None,
  ):
    self.ref_month_year_orig_oblig     = ref_month_year_orig_oblig
    self.valor_orig                    = valor_orig
    self.data_para_atualizar_a_obrig   = data_para_atualizar_a_obrig
    self.contract_conventions          = contract_conventions
    self.discount_percent              = discount_percent
    self.divida_descrita_mes_a_mes = []
    # each elem in divida_descrita_mes_a_mes is a list of monthly quinhoes
    # the index is the month position (0 is the 1st month, 1 is the second month and so on)
    self.calcula_mora()

  def calcula_mora(self):
    for this_month_year in  range(self.data_para_atualizar_a_obrig):
      monthlyQuinhoes = MonthlyQuinhoesOfMora(self.valor_orig, self.data_para_atualizar_a_obrig-this_month_year)
      self.divida_descrita_mes_a_mes.append(monthlyQuinhoes)


  def report_mora(self):
    outstr = '='*40
    outstr += '\n'
    outstr += 'Original Obligation = R$ %s' %(str(self.valor_orig))
    outstr += '\n'
    outstr += '-'*40
    outstr += '\n'
    outstr += 'Delay = %s' %(str(self.data_para_atualizar_a_obrig))
    outstr += '\n'
    updated_valor = self.valor_orig
    n_mes=0
    for this_month_year, monthlyQuinhoes in enumerate, self.divida_descrita_mes_a_mes):
      outstr += '  ==>>>> Mês [%d] Valor Componente da Mora' %(this_month_year)
      outstr += '\n'
      outstr += '=' * 40
      outstr += '\n'
      outstr += 'Valor obrig. = %s' % (str(updated_valor))
      outstr += '\n'
      total_quinhoes = 0
      for i, quinhoes_do_mes in enumerate(monthlyQuinhoes):
        for quinhao in quinhoes_do_mes:
          n_quinhao = i+1
          outstr += '-' * 40
          outstr += '\n'
          outstr += ' :: %d => Componente Rubrica = %s' %(n_quinhao_componente, quinhao.get_rubrica_by_name())
          outstr += '\n'
          outstr += '-' * 40
          outstr += '\n'
          outstr += 'Percentual: %s' % (str(quinhao.percentual))
          outstr += '\n'
          outstr += 'Sobre (basevalue): %s  :: ' % (str(quinhao.basevalue))
          valor_quinhao = quinhao.valor_componente_da_rubrica
          outstr += 'Valor componente: %s' % (str(valor_quinhao))
          outstr += '\n'
          total_quinhoes += valor_quinhao
      updated_valor += total_quinhoes
      total_quinhoes = 0
    outstr += 'Valor Atualizado da Obrig = R$ %s' %(str(updated_valor))
    outstr += '\n'
    outstr += ' =-*+ '*15
    outstr += '\n'
    return outstr

if __name__ == '__main__':
  print ('This script is run under the adhoc_test.py script in this folder.')