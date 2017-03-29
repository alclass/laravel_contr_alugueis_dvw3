#!/usr/bin/env python3
import datetime
from models import User, Imovel, MoraQuinhao


def get_corr_monet_percent_for_month_year(month_year=None):
  return 0.38

class MoraMonthly:

  # for the time being month_year is "integer"!
  # 0 is the origin, 1 the first month in delay, 2 the second month in delay and so on

  def __init__(self,
               ref_month_year_orig_oblig,
               valor_orig,
               data_para_atualizar_a_obrig, # an integer for the time being
               multa_percent,
               juros_am_percent,
               bool_aplicar_corr_monet=True,
               discount_percent = None,
               ):
    self.ref_month_year_orig_oblig     = ref_month_year_orig_oblig
    self.valor_orig                    = valor_orig
    self.data_para_atualizar_a_obrig   = data_para_atualizar_a_obrig
    self.multa_percent                 = multa_percent
    self.juros_am_percent              = juros_am_percent
    self.bool_aplicar_corr_monet       = bool_aplicar_corr_monet
    self.discount_percent              = discount_percent
    self.quinhoes_mes_a_mes_tuple_list = []
    self.calcula_mora()

  def calcula_mora(self):
    updated_valor = self.valor_orig
    for this_month_year in  range(1, self.data_para_atualizar_a_obrig + 1):
      for i in MoraQuinhao.rubricas_list():
        quinhao_mes = []
        mora_in_month_value = 0
        if this_month_year == 1:  # ie, multa is only in the first month of delay
          composicao_quinhao = MoraQuinhao()
          composicao_quinhao['rubrica']    = MoraQuinhao.RUBRICA_MULTA_CONTR
          composicao_quinhao['basevalue']  = updated_valor
          composicao_quinhao['percentual'] = self.multa_percent
          mora_in_month_value += composicao_quinhao.valor_componente_da_rubrica
          quinhao_mes.append(composicao_quinhao)
        # juros
        composicao_quinhao['rubrica']    = MoraQuinhao.RUBRICA_JUROS_AM
        composicao_quinhao['basevalue']  = updated_valor
        composicao_quinhao['percentual'] = self.juros_am_percent
        mora_in_month_value += composicao_quinhao.valor_componente_da_rubrica
        quinhao_mes.append(composicao_quinhao)
        if self.bool_aplicar_corr_monet:
          composicao_quinhao = MoraQuinhao()
          composicao_quinhao['rubrica']    = MoraQuinhao.RUBRICA_CORR_MONET
          composicao_quinhao['basevalue']  = updated_valor
          composicao_quinhao['percentual'] = get_corr_monet_percent_for_month_year()
          mora_in_month_value += composicao_quinhao.valor_componente_da_rubrica
          quinhao_mes.append(composicao_quinhao)
      quinhao_mes_tuple = (this_month_year, quinhao_mes)
      self.quinhoes_mes_a_mes_tuple_list = quinhao_mes_tuple
      updated_valor += mora_in_month_value

  def report_mora(self):
    outstr = '=*40'
    outstr += '\n'
    outstr = 'Original Obligation = R$ %s' %(str(self.valor_orig))
    outstr += '\n'
    outstr = '-*40'
    outstr += '\n'
    outstr += 'Delay = %s' %(str(self.data_para_atualizar_a_obrig))
    outstr += '\n'
    updated_valor = self.valor_orig
    for this_month_year, quinhao_mes in self.quinhoes_mes_a_mes_tuple_list:
      outstr += 'Mês [%s] Valor Componente da Mora' %(str(this_month_year))
      outstr += '\n'
      outstr += 'Valor obrig. = %s' % (str(updated_valor))
      outstr += '\n'
      total_quinhoes = 0
      for quinhao in quinhao_mes:
        outstr += 'Componente Rubrica = ' % (str(quinhao['rubrica']))
        outstr += '\n'
        outstr += 'Percentual: %s' % (str(quinhao['percentual']))
        outstr += '\n'
        outstr += 'Sobre: %s' % (str(quinhao['origvalue']))
        valor_quinhao = quinhao.get_valor_componente_da_rubrica()
        outstr += 'Valor: %s' % (str(valor_quinhao))
        outstr += '\n'
        total_quinhoes += valor_quinhao
      updated_valor += total_quinhoes
      total_quinhoes = 0
    outstr += 'Valor Atualizado da Obrig = R$ %s' %(str(self.updated_valor))
    outstr += '\n'
    outstr += ' =-*+ '*15
    outstr += '\n'
    return outstr

if __name__ == '__main__':
  print ('This script is run under the adhoc_test.py script in this folder.')