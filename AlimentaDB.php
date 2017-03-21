
<!DOCTYPE html>
<html lang="en">
	<head>
	    <meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

		<title>MFF - Scripts</title>

		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
		<script src="https://code.jquery.com/jquery-3.1.1.slim.min.js" integrity="sha384-A7FZj7v+d/sdmMqp/nOQwliLvUsJfDHW+k9Omg/a/EheAdgtzNs3hpfag6Ed950n" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>

		<link href="https://fonts.googleapis.com/css?family=Raleway:200" rel="stylesheet"> 
		<style type="text/css">
			body{
				font-family: 'Raleway', sans-serif;
			}
		</style>
	</head>
	<body>
		<div class="container">
		<?php
		
		function buscaSemelhante($tabela,$coluna,$valor,$idDeRetorno){
			// CONNECTANDO AO BANCO LOCAL
			$mysqli2 = new mysqli("localhost", "root", "m230889m", "MFF_Scripts");
			if ($mysqli2->connect_errno) {
				$err = "Failed to connect to MySQL: (" . $mysqli2->connect_errno . ") " . $mysqli2->connect_error ;
				var_dump($err);
				die();
			}
			$mysqli2->set_charset("utf8");
			$query = $mysqli2->query('SELECT * FROM '.$tabela);
			$ret = false;
			$retAlmost = false;
			while($row = $query->fetch_assoc()) {
				similar_text($row[$coluna], $valor, $percent);
				if($percent >= 90){
					$ret = intval($row[$idDeRetorno]);

					break;
				}elseif($percent > 80 && $percent < 90){
					$retAlmost = $row;
				}
			}
			$mysqli2->close();
			
			if($ret == false && $retAlmost != false){
				$ret = $retAlmost;
			}
			var_dump($ret);
			return $ret	;
		}

		// LENDO ARQUIVO CSV 
		$leuHeader = false;
		$num = 0;
		$file = array();
		if (($handle = fopen("teste1.csv", "r")) !== FALSE) {

			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
				if($num == 0){
					$keys = $data;
				}elseif($num == count($data)){
					$file[] = $data;
				}else{
					echo 'numero de elementos na linha não batem esperado '.$num.' encontrados '.count($data).' colunas!! :(';
					echo json_encode($data);
				}
				$num = count($data);
			}
			fclose($handle);
		}

		$arr = array();
		foreach ($file as  $linha) {
			$authors = array();
			$i = 0;
			$periodico = null;
			$ano = null;
			foreach ($linha as $value) {
				$key = explode(' ', $keys[$i]);
				if($key[0] == "Autor" && $value != ''){
					$authors[] = $value;
				}
				if($key[0] == "Periódico" && $value != ''){
					$periodico = $value;
				}
				if($key[0] == "Ano" && $value != ''){
					$ano = $value;
				}
				$i++;
			}
			$arr[] = array(
				'periodico' => $periodico,
				'ano' => $ano,
				'autores' => $authors
			);
		}
		echo "<br> ...... Conectado ao banco de dados ...... <br><br>";
		$mysqli = new mysqli("localhost", "root", "m230889m", "MFF_Scripts");
		$mysqli->set_charset("utf8");
		if ($mysqli->connect_errno) {
			$err = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error ;
			var_dump($err);
			die();
		}

		echo "<div>";
		foreach($arr as $pair){
			$periodico = $pair['periodico'];
			$ano = $pair['ano'];
			//Antes de inserir busca por um periodico com o nome semelhante 
			$res = buscaSemelhante('periodico', 'nome', $periodico,'idPeriodico');
			$periodicoID = null;
			if($res == false){
				if ($mysqli->query('INSERT INTO periodico (nome) VALUES ("'.$periodico.'")')) {
					$periodicoID = $mysqli->insert_id;
					echo 'Periodico inserido id: '. $periodicoID.'<br><br>';
				}else{
					echo 'Nao foi possivel adicionar o periodico ('.$periodico.'), saindo com erro : '.$mysqli->error.'<br><br>';
				}
			}elseif(is_int($res)){
				$periodicoID = $res;
				echo 'periodico encontrado id: '.$periodicoID.'<br><br>';
			}elseif(is_array($res)){
				echo 'Similaridade encontrada mas nao foi sufiente para substituir automaticamente por favor verifique <br>';
				print_r($res);
			}else{
				echo 'erro nao identificado<br><br>';
				var_dump($res);
			}
			if($periodicoID != null){
				$artigoID = null;
				// Inserindo artigo pois cada linha de arr é um artigo
				if ($mysqli->query('INSERT INTO artigoARS (idPeriodico) VALUES ("'.$periodicoID.'")')) {
					$artigoID = $mysqli->insert_id;
					echo 'Artigo inserido id: '. $artigoID.'<br><br>';
				}else{
					echo 'Nao foi possivel adicionar o artigo com o periodico ('.$periodicoID.'), saindo com erro : '.$mysqli->error.'<br><br>';
				}
				foreach ($pair['autores'] as $autor){
					$pessoaID = null;
					$res = buscaSemelhante('pessoa', 'nomeCompleto', $autor,'idPessoa');
					if($res == false){
						if ($mysqli->query('INSERT INTO pessoa (nomeCompleto) VALUES ("'.$autor.'")')) {
							$pessoaID = $mysqli->insert_id;
							echo 'Pessoa inserida id: '. $pessoaID.'<br><br>';
						}else{
							echo 'Nao foi possivel adicionar a pessoa ('.$autor.'), saindo com erro : '.$mysqli->error.'<br><br>';
						}
					}elseif(is_int($res)){
						$pessoaID = $res;
						echo 'pessoa encontrada id: '.$pessoaID.'<br><br>';
					}elseif(is_array($res)){
						echo 'Similaridade encontrada mas nao foi sufiente para substituir automaticamente por favor verifique <br>';
						print_r($res);
					}else{
						echo 'erro nao identificado<br><br>';
						var_dump($res);
					}
					if($pessoaID != null && $artigoID != null){
						$mysqli->query('INSERT INTO pessoaArtigo (idArtigo,idPessoa,ano) VALUES ('.$artigoID.','.$pessoaID.','.$ano.')');
					}
				}
			}
		}
		$mysqli->close();
		echo "</div>";
		?>
		</div>
	</body>
</html>
