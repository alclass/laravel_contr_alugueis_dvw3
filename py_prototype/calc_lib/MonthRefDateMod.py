#!/usr/bin/env python3

'''
Info on method monthrange()
=> calendar.monthrange(year, month) returns a 2-tuple (weekdayindex, number of days in month)
weekdayindex is 0 for Monday, 1 for Tuesday, on until 6 for Sunday
'''

class TestMonthRefs(unittest.TestCase):

  def setUp(self):
    monthyeardateref = date(2018,1,1)
    duedate          = date(2018,2,10)
    billingitems  = create_billingitems_list_for_invoicebill()
    self.bill_obj = Bill(
      monthyeardateref,
      duedate,
      billingitems
    )

  def test_1(self):
    paid_amount = 1000
    paydate = date(2018, 2, 5)
    payment_obj = Payment(paid_amount, paydate)
    self.bill_obj.add_payment_obj(payment_obj)


  def test_generate_conventioned_monthyeardateref_against_given_date_before_day10(self):
    '''
    A 'monthref' is a date that always ends up with day=1.
    (Day has no meaning to a 'monthref', a 'monthref' is a somewhat class-reuse of Date.)
    The convention in the callee method is that when day > 10, monthref forwards to next month.
    :return:
    '''
    given_date        = date(2017,1,9)
    expected_monthref = date(2017,1,1)
    returned_monthref = self.bill_obj.generate_conventioned_monthyeardateref_against_given_date(given_date)
    self.assertEqual(expected_monthref, returned_monthref)
    given_date        = date(2017,1,1)
    expected_monthref = date(2017,1,1)
    returned_monthref = self.bill_obj.generate_conventioned_monthyeardateref_against_given_date(given_date)
    self.assertEqual(expected_monthref, returned_monthref)

  def test_generate_conventioned_monthyeardateref_against_given_date_after_day10(self):
    given_date        = date(2017,1,11)
    expected_monthref = date(2017,2,1)
    returned_monthref = self.bill_obj.generate_conventioned_monthyeardateref_against_given_date(given_date)
    self.assertEqual(expected_monthref, returned_monthref)
    given_date        = date(2017,1,31)
    expected_monthref = date(2017,2,1)
    returned_monthref = self.bill_obj.generate_conventioned_monthyeardateref_against_given_date(given_date)
    self.assertEqual(expected_monthref, returned_monthref)

  def test_generate_conventioned_monthyeardateref_against_given_date_on_day10(self):
    given_date        = date(2017,1,10)
    expected_monthref = date(2017,1,1)
    returned_monthref = self.bill_obj.generate_conventioned_monthyeardateref_against_given_date(given_date)
    self.assertEqual(expected_monthref, returned_monthref)

  def test_generate_conventioned_monthyeardateref_against_today(self):
    '''
    This test is a bit odd in the sense that it depends on today's date.
    However, if tests are run everyday, in the case it ends up passing thru' all days (along some months),
    it seems a reasonable complement to the above tests that do not depend on case variable
      inside the test and also have the 'casing' coverage that diverges results (returns)
      when day = 10.
    :return:
    '''
    today = date.today()
    expected_monthref = date(today.year, today.month, 1)
    if today.day > 10:
      expected_monthref = expected_monthref + relativedelta(months=+1)
    # passing None to method means that the method will consider given_date to be today's date
    returned_monthref = self.bill_obj.generate_conventioned_monthyeardateref_against_given_date(None)
    self.assertEqual(expected_monthref, returned_monthref)
