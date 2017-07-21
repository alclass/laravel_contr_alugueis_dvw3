<p>Contrato(s): {{ $contract_imovel_endereco }}</p>
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
		<td>{{ $loop->iteration }}</td>
		<td>{{ $cobranca->monthyeardateref->format('M-Y') }}</td>
		<td>{{ $cobranca->duedate->format('d/M/Y') }}</td>
		<td>{{ $cobranca->total }}</td>
		<td>{{ ($cobranca->has_been_paid?'Quitado':'Em Aberto') }}</td>
		<?php
			$contract_id = $contract->id;
			$year = $cobranca->monthyeardateref->year;
			$month = $cobranca->monthyeardateref->month;
		?>
		<td><a href="{{ route('cobranca.mostrar', [$contract_id, $year, $month]) }}">visualizar</a></td>
	</tr>
</table>

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
		<td></td>
		<td>{{ $cobranca_atual->monthyeardateref->format('M-Y') }}</td>
		<td>{{ $cobranca_atual->duedate->format('d/M/Y') }}</td>
		<td>{{ $cobranca_atual->total }}</td>
		<td>{{ ($cobranca_atual->has_been_paid ? 'Quitado' : 'Em Aberto') }}</td>
		<?php
			$contract_id = $cobranca_atual->id;
			$year        = $cobranca_atual->monthyeardateref->year;
			$month       = $cobranca_atual->monthyeardateref->month;
		?>
		<td><a href="{{ route('cobranca.mostrar', [$contract_id, $year, $month]) }}">visualizar</a></td>
	</tr>
</table>




	<li> |  | </li>
@endforeach
