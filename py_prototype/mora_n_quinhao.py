#!/usr/bin/env python3

import datetime, decimal


def get_corr_monet_percent_for_month_year(month_year=None):
  return 0.38


def generate_month_sequence(month_year, up_til_date):
  pass


class ContractConventionsMockClass:
  def __init__(self):
    self.multa_contr_percent = 10
    self.juros_am_percent = 1
    self.bool_aplicar_corr_monet = True

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

class QuinhaoOfMora:
  '''
  # no need to inherit dict, it's already available in the underlying root Object

  This class is planned to use 4 attributes, they are:
  1) the rubrica type (either multa, or juros or corr_monet)
  2) the percentual to be applied on the rubrica
  3) the original (or base) value to which the percentual will be applied
  4) [dynamic] the component amount that results from the percentual on the base value
  '''

  RUBRICA_CORR_MONET = 1
  RUBRICA_JUROS_AM = 2
  RUBRICA_MULTA_CONTR = 3

  CENTS = decimal.Decimal('.01')
  ONEHUNDRED = decimal.Decimal('100.00')

  KEYS = [
    'basevalue',
    'fixed_amount',
    'rubrica',
    'percentual',
  ]

  def __init__(self, rubrica, basevalue, percentual=None, fixed_amount=None):

    self.rubrica = rubrica
    self.basevalue = basevalue

    self.percentual = None
    self.fixed_amount = None

    if percentual is not None:
      self.percentual = percentual
    if fixed_amount is not None:
      self.fixed_amount = fixed_amount

  def __getitem__(self, key):
    if key == 'rubrica':
      return self.rubrica
    elif key == 'basevalue':
      return self.basevalue
    elif key == 'percentual':
      return self.percentual
    elif key == 'fixed_amount':
      return self.fixed_amount
    return self.__dict__[key]

  def __setitem__(self, key, value):
    if key in __class__.KEYS:
      if key == 'rubrica':
        self.check_rubrica_validity(value)
        self.rubrica = value
        return
      elif key == 'basevalue':
        self.basevalue = value
        return
      elif key == 'percentual':
        self.percentual = value
        return
      elif key == 'fixed_amount':
        self.fixed_amount = value
        return
    self.__dict__[key] = value
    # raise KeyError('key %s is not in %s' %(str(key), str(__class__.KEYS)))

  def check_rubrica_validity(self, rubrica):
    if rubrica not in __class__.rubricas_list():
      raise ValueError('Rubrica not in QuinhaoList = %s' % str(__class__.rubricas_list()))

  @staticmethod
  def rubricas_list():
    return [
      __class__.RUBRICA_CORR_MONET,
      __class__.RUBRICA_JUROS_AM,
      __class__.RUBRICA_MULTA_CONTR,
    ]

  def get_rubrica_by_name(self):
    if self.rubrica == __class__.RUBRICA_JUROS_AM:
      return 'juros a.m.'
    elif self.rubrica == __class__.RUBRICA_CORR_MONET:
      return 'correção monetária'
    elif self.rubrica == __class__.RUBRICA_MULTA_CONTR:
      return 'multa contratual'
    return 'componente'

  @property
  def rubrica(self):
    try:
      # return self.__dict__['rubrica']
      return self.__rubrica
    except KeyError:
      pass
    return None

  @rubrica.setter
  def rubrica(self, rubrica):
    self.check_rubrica_validity(rubrica)
    self.__rubrica = rubrica
    # self.__dict__['rubrica'] = rubrica

  @property
  def basevalue(self):
    try:
      # return self.__dict__['basevalue']
      return self.__basevalue
    except KeyError:
      pass
    return None

  @basevalue.setter
  def basevalue(self, basevalue):
    if basevalue is None:
      self.__basevalue = None
      return
    self.__basevalue = decimal.Decimal(basevalue)
    # self.__dict__['basevalue'] = basevalue

  @property
  def percentual(self):
    try:
      return self.__percentual
      # return self.__dict__['percentual']
    except KeyError:
      pass
    return None

  @percentual.setter
  def percentual(self, percentual):
    if percentual is None:
      self.__percentual = None
      return
    self.__percentual = decimal.Decimal(percentual)
    # self.__dict__['percentual'] = percentual

  @property
  def fixed_amount(self):
    try:
      return self.__fixed_amount
      # return self.__dict__['fixed_amount']
    except KeyError:
      pass
    return None

  @fixed_amount.setter
  def fixed_amount(self, fixed_amount):
    if fixed_amount is None:
      self.__fixed_amount = None
      return
    self.__fixed_amount = decimal.Decimal(fixed_amount)
    # self.__dict__['fixed_amount'] = fixed_amount

  @property
  def valor_componente_da_rubrica(self):
    fixed_amount = self.fixed_amount
    if fixed_amount is not None:
      return fixed_amount
    basevalue = self.basevalue
    if basevalue is None:
      return None
    percentual = self.percentual
    if percentual is None:
      return None
    f = percentual / __class__.ONEHUNDRED  # works in Python 2 & 3
    v = basevalue * f
    return v.quantize(__class__.CENTS, decimal.ROUND_HALF_UP)

  def __str__(self):
    outstr = '''Quinhão:
    Rubrica : %(rubrica_name)s
    ''' % ({'rubrica_name': self.get_rubrica_by_name()})
    if self.fixed_amount is not None:
      outstr += '''==============
      Valor Aplic.: %(fixed_amount)s
      ''' % ({'fixed_amount': str(self.fixed_amount)})
      return outstr
    outstr += '''==============
    Valor Base: %(basevalue)s
    Percentual: %(percentual)s
    Componente: %(valor_componente_da_rubrica)s''' % ({
      'basevalue': "{:.2f}".format(self.basevalue),
      'percentual': "{:.2f}".format(self.percentual),
      'valor_componente_da_rubrica': self.valor_componente_da_rubrica
    })
    return outstr


class MonthlyQuinhoesOfMora:  # no need to inherit dict, it's already available in the underlying root Object

  def __init__(self,
               basevalue,
               n_of_months_in_mora,
               n_of_days_in_mora=0,
               contract_conventions=None):

    self.basevalue           = basevalue
    self.n_of_months_in_mora = n_of_months_in_mora
    self.n_of_days_in_mora   = n_of_days_in_mora

    self.set_contract_conventions(contract_conventions)

    # copy basevalue to ongoing_value, which will increase month by month
    self.ongoing_value = self.basevalue

    # This variable is a list of list: it keeps month quinhoes all months
    self.quinhoes_mes_a_mes = []
    # calculates mora from this constructor (__init__)
    self.calcula_mora_mes_a_mes()

  def set_contract_conventions(self, contract_conventions=None):
    if contract_conventions is None:
      self.contract_conventions = ContractConventionsMockClass()
    else:
      self.contract_conventions = contract_conventions

  def calcula_mora_mes_a_mes(self):
    self.mora_mes_a_mes = []
    for n in range(self.n_of_months_in_mora):
      quinhoes_do_mes = []
      added_to_debt = 0
      if n == 0:  # ie, the first month, that is, only in the first month multa is applied
        # quinhao_multa
        quinhao = self.make_quinhao_multa()
        quinhoes_do_mes.append(quinhao)
        added_to_debt += quinhao.valor_componente_da_rubrica
      # quinhao_juros
      quinhao = self.make_quinhao_juros()
      quinhoes_do_mes.append(quinhao)
      added_to_debt += quinhao.valor_componente_da_rubrica
      # quinhao_corr_monet
      quinhao = self.make_quinhao_corr_monet()
      quinhoes_do_mes.append(quinhao)
      added_to_debt += quinhao.valor_componente_da_rubrica
      self.ongoing_value += added_to_debt
      self.mora_mes_a_mes.append(quinhoes_do_mes)

  def make_quinhao_multa(self):
    return QuinhaoOfMora(
      rubrica=QuinhaoOfMora.RUBRICA_MULTA_CONTR,
      basevalue=self.ongoing_value,
      percentual=self.contract_conventions.multa_contr_percent,
    )

  def make_quinhao_juros(self):
    return QuinhaoOfMora(
      rubrica=QuinhaoOfMora.RUBRICA_JUROS_AM,
      basevalue=self.ongoing_value,
      percentual=self.contract_conventions.juros_am_percent,
    )

  def make_quinhao_corr_monet(self):
    return QuinhaoOfMora(
      rubrica=QuinhaoOfMora.RUBRICA_CORR_MONET,
      basevalue=self.ongoing_value,
      percentual=self.contract_conventions.get_corr_monet_percent_for_month_year(),
    )

  def calc_total_para_quitar_mes(self):
    '''
    In this dynamic retrieval, we consider the following sum:
    1) the basevalue in the last month
       plus
    2) the amount of increment that above quantity has
       (this amount is taken from quinhao.valor_componente_da_rubrica)
    ---------------------------------
    So, this dynamic retrieval is: basevalue + quinhao.valor_componente_da_rubrica
    ---------------------------------
    :return:
    '''
    quinhoes_ultimo_mes = self.mora_mes_a_mes[-1]
    mora_ultimo_mes = 0
    quinhao = quinhoes_ultimo_mes[0]
    basevalue = quinhao.basevalue
    for quinhao in quinhoes_ultimo_mes:
      mora_ultimo_mes += quinhao.valor_componente_da_rubrica
    return basevalue + mora_ultimo_mes

  def report_mora(self):
    lines = []
    line = '>> Mora:'; lines.append(line)
    line = '======='; lines.append(line)
    line = '   Valor inicial: %s' %("{:.2f}".format(self.basevalue)); lines.append(line)
    line = '   Componentes da Mora Contratual:'; lines.append(line)
    line = str(self.contract_conventions); lines.append(line)
    for i, quinhoes_do_mes in enumerate(self.mora_mes_a_mes):
      n_mes = i + 1
      line = '> Mês: %d' % n_mes; lines.append(line)
      total_mes = 0
      for quinhao_mora in quinhoes_do_mes:
        line = str(quinhao_mora); lines.append(line)
        total_mes += quinhao_mora.valor_componente_da_rubrica
      line = 'Total Mora Mês: %s' % (str(total_mes)); lines.append(line)
      line = 'Total Quita Mês: %s' % (str(total_mes+quinhao_mora.basevalue)); lines.append(line)
    outstr = '\n'.join(lines)
    return outstr


def adhost_test():
  mora_quinhao = QuinhaoOfMora(
    rubrica=QuinhaoOfMora.RUBRICA_MULTA_CONTR,
    basevalue=1000,
    percentual=10,
  )

  print(mora_quinhao)

  print('=' * 70)
  print('=' * 70)

  mqm = MonthlyQuinhoesOfMora(
    basevalue=1000,
    n_of_months_in_mora=3,
  )
  print(mqm.report_mora())
  print('*'*50)
  print('mqm.calc_total_para_quitar_mes() => ', mqm.calc_total_para_quitar_mes())


if __name__ == '__main__':
  adhost_test()
