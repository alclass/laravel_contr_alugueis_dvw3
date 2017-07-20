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
@foreach ($contract->get_ultimas_cobrancas(3) as $cobranca)
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
	<li> |  | </li>
@endforeach
