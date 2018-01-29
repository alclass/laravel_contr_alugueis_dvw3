

class BillModOld:


  def pay_applying_correctionfractions_array(
      self,
      payment_obj,
      mo_by_mo_interest_plus_corrmonet_times_fraction_array
    ):
    if self.base_for_i_n_cm_account == 0:
      return self.pay(payment_obj)

    for factor in mo_by_mo_interest_plus_corrmonet_times_fraction_array:
      # notice that self.base_for_i_n_cm_account is DYNAMIC, ie, it depends on debt_account
      if self.is_pay_moment_later_than_mplus1(payment_obj):
        basevalue = self.debt_account
      else:
        basevalue = self.base_for_i_n_cm_account
      moraincrease = basevalue * factor
      self.debt_account          += moraincrease
      self.interest_n_cm_account += moraincrease
      debt_factor_moraincrease_triple = (basevalue, factor, moraincrease, self.base_for_i_n_cm_account)
      self.debt_factor_mora_increasedvalue_quadlist.append(debt_factor_moraincrease_triple)

    self.credit_debit_payment(payment_obj.paid_amount)
    self.date_of_last_interest_applied = payment_obj.paydate + relativedelta(days=+1)

  '''
  def add_payment_obj(self, payment_obj):
    self.payments.append(payment_obj)
  '''

  def pay_late(self, payment_obj):
    '''
    The interest startdate is not duedate, it takes on two possible values, ie:
      1) for the first late payment, it's monthref plus ONE month.
      2) for the a late payment, it's date of last payment plus one.

    Example [for 1) above]: if monthref is (01)Jan2018, interest_startdate is 01Feb2018.
    However, the corr-monet index is taken M-1, ie, it's the index of its
      previous month. Example: 15 days late in February are calculated with
      January's interest rate.
      * This is so because a month's index is only known M+1,
        example: the February corr-monet. index is only know in March.


    :param payment_obj:
    :return:
    '''

    # 1st hypothesis: a payment happened without no debt at all, simplily 'debit' it to cred_account
    if self.debt_account <= 0:
      self.cred_account    += payment_obj.paid_amount
      self.payment_account += payment_obj.paid_amount
      return

    if self.date_of_last_interest_applied is not None:
      interest_startdate = self.date_of_last_interest_applied
    else:
      interest_startdate = self.monthrefdate + relativedelta(months=+1)

    mo_by_mo_days = self.datecalculator.calc_mo_by_mo_days_between_dates(
      interest_startdate,
      payment_obj.paydate
    )
    '''
    if len(mo_by_mo_days) == 0:
      return
    '''

    monthfractions = self.datecalculator.transform_monthdays_into_monthfractions(
      mo_by_mo_days,
      interest_startdate
    )
    print ('******************************')
    print ('monthfractions:', str(monthfractions))
    interestarray = [0.01] * len(monthfractions)
    mo_by_mo_interest_plus_corrmonet_times_fraction_array = Juros \
      .gen_mo_by_mo_interest_plus_corrmonet_times_fraction_array(
      interest_startdate,
      interestarray,
      monthfractions
    )
    print ('******************************')
    print ('mo_by_mo_interest_plus_corrmonet_times_fraction_array:', str(mo_by_mo_interest_plus_corrmonet_times_fraction_array))
    self.pay_applying_correctionfractions_array(
      payment_obj,
      mo_by_mo_interest_plus_corrmonet_times_fraction_array
    )

  def fetch_debo_amount_from_previous_bills_if_any(self):
    return PreviousBill.fetch_carriedup_debt_amount()

  def apply_multa_if_payment_is_incomplete(self):
    '''

    *** THIS METHOD IS YET LOGICALLY INCOMPLETE ***
      Because it's not enough to find a later date, it should also find
        if earlier pay was enough

    This method should use functional programming techniques for TWO needs, ie:
    1) the method should know whether or not various payments paid on time
    2) the method should quickly find, if it happened, a 'fine' incidence

    :return:
    '''
    # obs: payments list must be IN ORDER OF PAYDATE
    amount_paid_on_date = 0
    for payment_obj in self.payments:
      if payment_obj.paydate <= self.duedate:
        amount_paid_on_date += payment_obj.paid_amount
    if amount_paid_on_date < self.inmonthpluspreviousdebts:
      amount_to_fine = self.inmonth_due_amount - amount_paid_on_date
      multa_amount = amount_to_fine * 0.1
      # Accounting-like accounts debt/credit
      self.multa_account += multa_amount
      self.debt_account  += multa_amount

  def process_payment_old(self):
    '''
    This method should be run after setting the self.payment_objs list of Payments

    Before calculing bill, it must know whether or not to aply fine.

    The fine happens over the monthly amount that is late.
    The fine does not cover an amount that has been carried up, ie, older debt.
    So the TWO things are necessary to apply fine:
      1) even if some payment has been done on time,
        it must satisfy the complete bill's amount,
        if not, the contract fine applies to the amount
        that would complete the monthly bill;
      2) once 1) above happens, it must separate the monthly amount
        from any carrying-up's from older months,
        because fines do not apply to carrying-up's.

    :return:
    '''
    self.apply_multa_if_payment_is_incomplete()
    for payment_obj in self.payments:
      if payment_obj.paydate <= self.duedate:
        self.pay(payment_obj)
      else:
        #self.pay_late(payment_obj)
        self.late_payments.append(payment_obj)

    self.batch_late_payments()

  def batch_late_payments(self, ongoingmonthrefdate=None):

    first_increase = True # boolean signal to add fine_account
    if ongoingmonthrefdate is None:
      ongoingmonthrefdate = self.monthrefdate + relativedelta(months=+1)

    while len(self.late_payments) > 0:
      payment_obj = self.late_payments.pop()
      monthrefOfPayDate = payment_obj.paydate.replace(day=1)
      if monthrefOfPayDate > ongoingmonthrefdate:
        # apply interest for full month
        if first_increase:
          self.debt_account += self.debt_account * (0.01 + Juros.fetch_corrmonet_for_month(ongoingmonthrefdate))
          self.debt_account += self.multa_account
          first_increase = False # boolean signal to add fine_account
          self.date_of_last_interest_applied = ongoingmonthrefdate + relativedelta(months=+1)
      elif monthrefOfPayDate == ongoingmonthrefdate:
        self.pay_late(payment_obj)
      else:
        ongoingmonthrefdate = ongoingmonthrefdate + relativedelta(months=+1)


  def pay(self, payment_obj):
    '''
    This method is for payment on time, not late-mora payments.
    Consider this as a private method.
    Also that it works as a credit / debit operation.
    It can only be called from inside the 'if' that checks this pay is on date
    :param payment_obj:
    :return:
    '''
    self.credit_debit_payment(payment_obj.paid_amount)


  def is_pay_moment_later_than_mplus1(self, payment_obj):
    '''
    This method regulates which basevalue to be used to calculate interest plus corr.monet.
    Example:
      Suppose monthrefdate is 2018-01-01
      Suppose also duedate is 2018-01-10 (reminding that duedate is checked before entering pay_late()
      Under these 2 hypotheses:
        case 1) window moment from 2018-02-11 to 2018-02-28 (or 29 in leap years)
          takes basevalue as debt_account minus fine_amount
        case 2) window moment beyond 2018-02-28 as the whole debt_account applying days down to last interest-corr.monet. calculation
    :param payment_obj:
    :return:
    '''
    date_later_than_mplus1 = self.monthrefdate + relativedelta(months=+2) # monthrefdate is always day 1
    if payment_obj.paydate < date_later_than_mplus1:
      return False
    return True
