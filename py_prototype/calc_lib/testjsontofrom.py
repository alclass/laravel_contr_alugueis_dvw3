#!/usr/bin/env python3

# import classes
import json

class JsonTest:

  def __init__(self, factor):
    self.x = 1 * factor
    self.y = 2 * factor

  def serialize_instance(self):
    d = {'__classname__' : type(self).__name__}
    d.update(vars(self))
    return d

  def to_json(self):
    objsdict = self.__dict__
    return json.dumps(objsdict)

  @staticmethod
  def from_json(jsonrepr):
    objsdict = json.loads(jsonrepr)
    print('__class__ =>', __class__)
    obj = __class__.__new__(__class__, 1) # (**objsdict)
    for key, value in objsdict.items():
      setattr(obj, key, value)
    return obj


  def __repr__(self):
    return str(self.serialize_instance())

  def __str__(self):
    return '(x={0}, y={1})'.format(self.x, self.y)

objlist = []
for factor in range(1,11):
  jt = JsonTest(factor)
  elem_json = jt.to_json()
  objlist.append(elem_json)

print(objlist)
print('dumping objlist')
dumpedjson = json.dumps(objlist)
print('dumpedjson =>', dumpedjson)

print('un-dumping objlist')
newlist = json.loads(dumpedjson)
print('newlist =>', newlist)

for newelem in newlist:
  obj = JsonTest.from_json(newelem)
  print('newelem =>', obj)

