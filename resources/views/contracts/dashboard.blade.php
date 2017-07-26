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

					@if($contract==null)
						<p>Não há contrato(s) atualmente.</p>
					@endif  {{-- @if($contract==null) --}}

					@if($contract!=null)
						<?php
							$contract_imovel_endereco = 'Não há.';
							if ($contract->imovel!=null) {
								$contract_imovel_endereco = $contract->imovel->get_street_address();
							}
							$cobranca_atual_strmonthref = 'n/a';
							if ($cobranca_atual!=null) {
								$cobranca_atual_strmonthref = $cobranca_atual->monthyeardateref->format('M-Y');
							}
						?>
						@include('contracts.dashboard_contract')

						<h6>Cobrança atual Ref. {{ $cobranca_atual_strmonthref }}</h6>

					@endif  {{-- @if($contract!=null) --}}



				</div>
			</div>
		</div>
	</div>
</div>
@endsection
