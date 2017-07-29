#!/usr/bin/env python3
# -*- coding: utf-8 -*-
import os

SPACE_ENC = '%20'

def calc_pmt_ie_valor_da_prestacao(p , r, n):
  # the pmt formula
  pmt_numerator = p * r * ((1 + r) ** n)
  pmt_denominator = ((1 + r) ** n) - 1
  pmt = pmt_numerator / pmt_denominator
  return pmt

def pmt_simulation():
  '''
  '''
  line = 'mont   increase    abatido    saldo'
  linerule = 50*'=';
  print (line )
  print (linerule)
  '{p} inc. {'
  p = 101.515; r = 0.05; prestacao = 20; n=6
  balance = p
  for i in range(n):
    seq = i+1
    montant = balance
    increase = montant * (1+r)
    balance = increase - prestacao
    line = '   %d  %f     %f    20,00  %f' %(seq, montant, increase, balance)
    print (line )

  print ()
  print (linerule)
  pmt_prestacao = calc_pmt_ie_valor_da_prestacao(p, r, n)
  print ('pmt = %f' %pmt_prestacao)

  print (linerule)
  p = 2000; r = 0.015; n=24
  pmt_prestacao = calc_pmt_ie_valor_da_prestacao(p, r, n)
  line = '   p=%d    r=%f     n=%d  ==>>> %f' %(p, r, n, pmt_prestacao)
  print (line)





if __name__ == '__main__':
  pmt_simulation()
