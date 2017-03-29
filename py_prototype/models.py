#!/usr/bin/env python3
import datetime

class User:

  def __init__(self, name, cpf, address):
    self.name = name
    self.address = address
    self.cpf = cpf

  def __str__(self):
    outstr = '''name = %(name)s
    cpf = %(cpf)s
    address = %(address)s
    '''
    return outstr


class Imovel:

  def __init__(self, sigla, address, tamanho_m2, n_quartos=None, n_banheiros=None):
    self.sigla = sigla
    self.address = address
    self.tamanho_m2 = tamanho_m2
    self.n_quartos = n_quartos
    self.n_banheiros = n_banheiros

  def __str__(self):
    outstr = '''sigla = %(sigla)s
    address = %(address)s
    tamanho_m2 = %(m2_tamanho_m2)s
    '''
    return outstr

class RentContract:

  def __init__(self, imovel, locatario, locador, valor_aluguel, signed_date):
    self.imovel    = imovel
    self.locatario = locatario
    self.locador   = locador
    self.valor_aluguel = valor_aluguel
    self.signed_date   = signed_date
    self.multa_percent    = None
    self.juros_am_percent = None
    self.bool_usar_corr_monet = True

  def set_mora_attribs(self, multa_percent, juros_am_percent, bool_usar_corr_monet=True):
    self.multa_percent    = multa_percent
    self.juros_am_percent = juros_am_percent
    self.bool_usar_corr_monet = bool_usar_corr_monet

  def imputacao_ao_pagamento(self, valor_pago, date, months_in_debt_dict, up_til_date=None):
    pass

  def __str__(self):
    outstr = '''sigla = %(sigla)s
    address = %(address)s
    tamanho_m2 = %(m2_tamanho_m2)s
    '''
    return outstr


def generate_month_sequence(month_year, up_til_date):
  pass

class MoraQuinhao(dict):
  '''
  This class is planned to use 4 attributes, they are:
  1) the rubrica type (either multa, or juros or corr_monet)
  2) the percentual to be applied on the rubrica
  3) the original (or base) value to which the percentual will be applied
  4) [dynamic] the component amount that results from the percentual on the base value
  '''

  RUBRICA_MULTA_CONTR = 1
  RUBRICA_JUROS_AM    = 2
  RUBRICA_CORR_MONET  = 3

  KEYS = ['rubrica', 'basevalue', 'percentual', 'fixed_amount']

  def __setitem__(self, key, value):
    if key in MoraQuinhao.KEYS:
      if key == 'rubrica':
        self.check_rubrica_validity(value)
      return dict.__setitem__(key, value)
    else:
      raise KeyError('key %s is not in %s' %(str(key), str(MoraQuinhao.KEYS)))

  @property
  def basevalue(self):
    try:
      return self['percentual']
    except KeyError:
      pass
    return None

  @property
  def percentual(self):
    try:
      return self['percentual']
    except KeyError:
      pass
    return None

  @property
  def fixed_amount(self):
    try:
      return self['fixed_amount']
    except KeyError:
      pass
    return None

  @property
  def valor_componente_da_rubrica(self):
    fixed_amount = self.fixed_amount
    if fixed_amount is not None:
      return fixed_amount
    basevalue  = self.basevalue
    if basevalue is None:
      return None
    percentual = self.percentual
    if percentual is None:
      return None
    return basevalue * percentual

  def check_rubrica_validity(self, rubrica):
    if rubrica not in MoraQuinhao.rubricas_list():
      raise ValueError('Rubrica not in QuinhaoList = %s' %str(MoraQuinhao.rubricas_list()))

  @classmethod
  def rubricas_list(cls):
    return [MoraQuinhao.RUBRICA_MULTA_CONTR, MoraQuinhao.RUBRICA_CORR_MONET, MoraQuinhao.RUBRICA_JUROS_AM]

  def get_rubrica_by_name(self):
    if self.rubrica == MoraQuinhao.RUBRICA_JUROS_AM:
      return 'juros a.m.'
    elif self.rubrica == MoraQuinhao.RUBRICA_CORR_MONET:
      return 'correção monetária'
    elif self.rubrica == MoraQuinhao.RUBRICA_MULTA_CONTR:
      return 'multa contratual'
    return 'componente'

  def __str__(self):
    outstr = '''Quinhão:
    Rubrica : %(rubrica_name)s
    ''' %({'rubrica_name':self.get_rubrica_by_name()})
    if self.fixed_amount is not None:
      outstr += ''' ==============
      Valor Aplic.: %(fixed_amount)s
      ''' %({'fixed_amount':str(self.fixed_amount)})
      return outstr
    outstr += ''' ==============
    Valor Base: %(basevalue)s
    Percentual: %(percentual)s
    ''' %({'basevalue':str(self.basevalue), 'percentual':str(self.percentual)})
    return outstr

def adhost_test():
  mora_quinhao = MoraQuinhao()
  mora_quinhao['rubrica', MoraQuinhao.RUBRICA_MULTA_CONTR]
  mora_quinhao['basevalue', 1000]
  mora_quinhao['percentual', 0.10]
  print (mora_quinhao)


if __name__ == '__main__':
  adhost_test()