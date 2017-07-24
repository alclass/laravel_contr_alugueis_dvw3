<h3> Dados Resumidos do  <a href="{{ route('contract', $contract) }}"> Contrato ID {{ $contract->id }}</a></h3>
<?php
  // Available variables here should be:
  // $contract
  $endereco = "n/a";
  $imovel_href = "#";
  $current_rent_value = "";
  $tipo_imov = "";
  $area_edif_iptu_m2 = "";
  $imovel = $contract->imovel;
  if ($imovel != null) {
    $endereco = $imovel->get_street_address();
    $imovel_href = route('imovel.show', $imovel);
    $tipo_imov = $imovel->tipo_imov;
    $area_edif_iptu_m2	= $imovel->area_edif_iptu_m2;
  }
  $next_reajust_date_str = "n/a";
  $next_reajust_date = $contract->find_rent_value_next_reajust_date();
  if ($next_reajust_date) {
    $next_reajust_date_str = $next_reajust_date->format('d/M/Y');
  }
?>
<h3> Imóvel {{ $tipo_imov }} </h3>
<h4> Endereço:  <a href="{{ $imovel_href }}"> {{ $endereco }} </a></h4>
<h4> Área IPTU: {{ $area_edif_iptu_m2	 }} m2</h4>
<h4> Início do Contrato: {{ $contract->start_date }} </h4>
<h5> Aluguel no Início do Contrato:  {{ $contract->initial_rent_value }} </h5>
<h4> Valor Atual do Aluguel:   {{ $contract->current_rent_value }} </h4>
<h5> Próximo Reajuste: {{ $next_reajust_date_str }} </h4>
