<p>Contrato(s): {{ $contract_imovel_endereco }}</p>

@if (!empty($cobrancas_passadas))
<p>Últimos holeriths</p>
<table border="1">
	<tr>
		<th></th>
		<th>Ref.</th>
		<th>Data Pagt.</th>
		<th>Valor</th>
		<th>Pagt.</th>
	</tr>
	@foreach ($cobrancas_passadas as $cobranca)
		<tr>
			<?php
				// Methods (eg. carbondate->format()) that are issued on fields that might be null and then result in exception raised against the app,
				// are checked here inside if's
				$cobranca_formatstrmonthref = 'n/a';
				$cobranca_formatstrduedate  = 'n/a';
				// cobranca is surely not null, for it came from loop
				if ($cobranca->monthrefdate!=null) {
					$cobranca_formatstrmonthref = $cobranca->monthrefdate->format('M-Y');
				}
				if ($cobranca->duedate!=null) {
					$cobranca_formatstrduedate  = $cobranca->duedate->format('M-Y');
				}
				// $contract_id = $contract->id;
				// $year = $cobranca->monthrefdate->year;
				// $month = $cobranca->monthrefdate->month;
			?>
			<td>{{ $loop->iteration }}</td>
			<td>{{ $cobranca_formatstrmonthref }}</td>
			<td>{{ $cobranca_formatstrduedate }}</td>
			<td>{{ $cobranca->total_value }}</td>
			<td>{{ ($cobranca->has_been_paid? 'Quitado' : 'Em Aberto') }}</td>
			<td><a href="{{ route('cobranca.mostrar', $cobranca->id) }}">visualizar</a></td>
		</tr>
	@endforeach
</table>
@endif {{-- @if(empty($cobrancas_passadas)) --}}

<br>
@if (!empty($cobranca_atual))
<p>Holerith Atual</p>
<table border="1">
	<tr>
		<th></th>
		<th>Ref.</th>
		<th>Data Pagt.</th>
		<th>Valor</th>
		<th>Pagt.</th>
	</tr>
	<tr>
		<?php
			$cobranca_formatstrmonthref = 'n/a';
			$cobranca_formatstrduedate  = 'n/a';
			// cobranca is surely not null, for it came from loop
			// however, monthrefdate or duedate may be null and
			// 	then there's an exception-raise risk of issue method ->format() on null
			if ($cobranca_atual->monthrefdate!=null) {
				$cobranca_formatstrmonthref = $cobranca_atual->monthrefdate->format('M-Y');
			}
			if ($cobranca_atual->duedate!=null) {
				$cobranca_formatstrduedate  = $cobranca_atual->duedate->format('M-Y');
			}
		?>
		<td></td>
		<td>{{ $cobranca_formatstrmonthref }}</td>
		<td>{{ $cobranca_formatstrduedate }}</td>
		<td>{{ $cobranca_atual->total_value }}</td>
		<td>{{ ($cobranca_atual->has_been_paid ? 'Quitado' : 'Em Aberto') }}</td>
		<?php
			// $contract_id = $cobranca_atual->id;
			// $year        = $cobranca_atual->monthrefdate->year;
			// $month       = $cobranca_atual->monthrefdate->month;
		?>
		<td><a href="{{ route('cobranca.mostrar', $cobranca_atual->id) }}">visualizar</a></td>
	</tr>
</table>
@endif {{-- @if (!empty($cobranca_atual)) --}}

<br>
<li> |  | </li>
