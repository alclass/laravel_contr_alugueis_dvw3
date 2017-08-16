@extends('layouts.app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-default">
				<div class="panel-heading">Dashboard :: {{ $user->get_first_n_last_names() }}</div>

				<div class="panel-body">
					<h3>Área do Usuário</h3>
            @if(count($errors)>0)
            <div class="alert alert-danger">
              @foreach($errors as $error)
                <p>{{ $error }}</p>
              @endforeach
            </div>
            @endif

					@if(empty($contracts))
						<p>Não há contrato(s) atualmente.</p>
					@endif  {{-- @if($contract==null) --}}

					@if(count($contracts)>0)
						@foreach($contracts as $contract)
							<?php
								$contract_imovel_endereco = 'Não há.';
								if ($contract->imovel!=null) {
									$contract_imovel_endereco = $contract->imovel->get_street_address();
								}
							?>
							@include('contracts.dashboard_contract')
							@foreach($contract->cobrancas->where('has_been_paid', 0)->sortBy('monthyeardateref') as $cobranca)
							<?php
								$cobranca_em_aberto_strmonthref = 'n/a';
								if ($cobranca->monthyeardateref!=null) {
									$cobranca_em_aberto_strmonthref = $cobranca->monthyeardateref->format('M-Y');
								}
							?>
							<h6>Cobrança em aberto Ref. {{ $cobranca_em_aberto_strmonthref }}</h6>

							@endforeach  {{-- @foreach($contract->cobrancas->where('has_been_paid', 0) as $cobranca) --}}
						@endforeach  {{-- @foreach($contracts as $contract) --}}
					@endif  {{-- @if(count($contracts)>0) --}}

				</div>
			</div>
		</div>
	</div>
</div>
@endsection
