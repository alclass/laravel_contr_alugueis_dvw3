


class D:

  def __init__(self):
    pass
    self.p = 20

  def __setitem__(self, key, value):
    self.__dict__[key] = value

  def __getitem__(self, key):
    return self.__dict__[key]

  @property
  def p(self):
    return self.__p

  @p.setter
  def p(self, value):
    self.__p = value



# test
print ('Testing dict inheritance')
d = D()
d['hello'] = 'Hello Workd'
print ('''
d['hello'] = 'Hello Workd'
Seeing...
''')
print (d['hello'])
print (d.p)
d.p = 10
print (d.p)

