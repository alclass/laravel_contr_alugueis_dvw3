#!/usr/bin/env python3
import datetime

def get_corr_monet_percent_for_month_year(month_year=None):
  return 0.38


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


if __name__ == '__main__':
  adhost_test()