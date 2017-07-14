<?php

public function calculate_debt_with_months_days($firstmonthdate, $n_months, $n_days) {
  $corr_monet = CorrMonet::find('monthdate', '=', $firstmonthdate);
  $percent_to_apply = $this->contract->percentual_multa + $this->contract->percentual_juros + $corr_monet
  $n_months -= 1;
  $ongoingdebt = $this->originaldebtvalue * (1 + $percent_to_apply);
  if ($n_months == 0) {
    return $ongoingdebt;
  }
  $month_date = $firstmonthdate->copy()->addMonth();
  for ($i; $i < $n_months; $i++) {
    $corr_monet = CorrMonet::find('monthdate', '=', $firstmonthdate);
    $percent_to_apply = $this->contract->percentual_juros + $corr_monet;
    $ongoingdebt = $this->originaldebtvalue * (1 + $percent_to_apply);
  }
