<html>
<head>
  <link rel="stylesheet" href="{{ asset('css/select_jquery.css') }}">
</head>
<body>

<h1>Página para Criar Itens de Cobrança</h1>
<hr>
<p>
  Esta Página é um protótipo para Criar Itens de Cobrança.
</p>


<!-- /submit/billingitems -->
<form action="{{ route('billingitemscreatorroute') }} ">

<label for="cobrancatipos_dropmenu_id">Criar um tipo de cobrança</label>
<select name="cobrancatipos_dropmenu" id="cobrancatipos_dropmenu_id">
<option value="ALUG">Aluguel</option>
<option value="COND">Condomínio</option>
<option value="IPTU">IPTU</option>
</select>


<p>
  <br>
</p>

<div id="placecobrancadadoshere">
  cobrança tipo
</div>

<p>
  <br>
</p>

<div id="countrydiv">

<select id="country">
	<option>--Select--</option>
	<option>EUA</option>
	<option>Austrália</option>
	<option>França</option>
</select><br/><br/>

<label>Select City:</label><br/><br/>

<select id="city">
<!--Dependent Select option field-->
</select>


</div>




</form>



</body>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.js"></script>
  <script type="text/javascript" src="{{ asset('js/select_jquery.js') }}"></script>
  <script type="text/javascript">
    // $().onchangeontipocobranca();
  </script>

</html>
