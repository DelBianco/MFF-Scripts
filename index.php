<?php
/**
 * Created by PhpStorm.
 * User: maxwell
 * Date: 21/03/17
 * Time: 11:45
 */

$mysqli = new mysqli("localhost", "root", "m230889m", "MFF_Scripts");
$mysqli->set_charset("utf8");
if ($mysqli->connect_errno) {
    $err = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error ;
    var_dump($err);
    die();
}
$periodicos = array();
$query = $mysqli->query('SELECT * FROM periodico');
while($row = $query->fetch_assoc()) {
    $periodicos[] = $row['nome'];
}
$pessoas = array();
$query = $mysqli->query('SELECT * FROM pessoa');
while($row = $query->fetch_assoc()) {
    $pessoas[$row['idPessoa']] = $row['nomeCompleto'];
}
$publicacoes = array();
$query = $mysqli->query('SELECT p.idPessoa as id, p.nomeCompleto as nome, pa.ano as ano , per.nome as periodico
                            FROM pessoaArtigo pa 
                              INNER JOIN pessoa p ON p.idPessoa = pa.idPessoa
                              INNER JOIN artigoARS ar ON ar.idArtigo = pa.idArtigo
                              INNER JOIN periodico per ON ar.idPeriodico = per.idPeriodico');
while($row = $query->fetch_assoc()) {
    $publicacoes[$row['id']]['nome'] = $row['nome'];
    $publicacoes[$row['id']]['publicacoes'][] = array('ano' => $row['ano'],'periodico' => $row['periodico']);
}

$colaboracoes = array();
foreach ($pessoas as $idPessoa => $pessoa){
    $artigos = array();
    $query = $mysqli->query('SELECT idArtigo FROM pessoaArtigo pa WHERE pa.idPessoa = '.$idPessoa);
    while($row = $query->fetch_assoc()) {
        $artigos[] = $row['idArtigo'];
    }
    $query = $mysqli->query('SELECT p.nomeCompleto as nome FROM pessoaArtigo pa INNER JOIN pessoa p ON p.idPessoa = pa.idPessoa WHERE pa.idArtigo in ('.implode(',',$artigos).') and pa.idPessoa <> '.$idPessoa);
    while($row = $query->fetch_assoc()) {
        $colaboracoes[$pessoa][] = $row['nome'];
    }
}
?>
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
            <hr>
            <h3>Periodicos encontrados</h3>
            <ul>
            <?php
                foreach ($periodicos as $periodico){
                    echo '<li>'.$periodico.'</li>';
                }
            ?>
            </ul>
            <hr>
            <h3>Pessoas encontradas</h3>
            <ul>
                <?php
                foreach ($pessoas as $pessoa){
                    echo '<li>'.$pessoa.'</li>';
                }
                ?>
            </ul>
            <hr>
            <div class="row">
                <div class="col-xs-12">
                    <h3>Publicacoes por ano</h3>
                    <table class="table table-striped">
                        <thead><tr><th>Pesquisador</th><th>Anos</th><th>Total</th></thead>
                        <tbody>
                        <?php
                            foreach ($publicacoes as $autor){
                                echo '<tr><td>'.$autor['nome'].'</td><td>';
                                foreach ($autor['publicacoes'] as $pub) {
                                    echo $pub['ano'].'<br>' ;
                                }
                                echo '</td><td>'.count($autor['publicacoes']) .' publicações';
                                echo '</td></tr>';
                            }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-xs-12">
                    <h3>Publicacoes por periodico</h3>
                    <table class="table table-striped">
                        <thead><tr><th>Pesquisador</th><th>Periodicos em que ja publicou</th></thead>
                        <tbody>
                        <?php
                        foreach ($publicacoes as $autor){
                            echo '<tr><td>'.$autor['nome'].'</td><td>';
                            $per = null;
                            foreach ($autor['publicacoes'] as $pub) {
                                if($per == null){
                                    $per = $pub['periodico'];
                                    echo $per.'<br>';
                                }else{
                                    if($per != $pub['periodico']){
                                        $per = $pub['periodico'];
                                        echo $per.'<br>';
                                    }
                                }
                            }
                            echo '</td></tr>';
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-xs-12">
                    <h3>Publicacoes por periodico e ano</h3>
                    <table class="table table-striped">
                        <thead><tr><th>Pesquisador</th><th>Publicacoes em periodicos por ano</th></thead>
                        <tbody>
                        <?php
                        foreach ($publicacoes as $autor){
                            echo '<tr><td>'.$autor['nome'].'</td><td>';
                            foreach ($autor['publicacoes'] as $pub) {
                                echo $pub['periodico'].' - '.$pub['ano'].'<br>' ;
                            }
                            echo '</td></tr>';
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-xs-12">
                    <h3>Colaborações</h3>
                    <table class="table table-striped">
                        <thead><tr><th>Pesquisador</th><th>Publicacoes com</th></thead>
                        <tbody>
                        <?php
                        foreach ($colaboracoes as $autor => $colaboradores){
                            echo '<tr><td>'.$autor.'</td><td>';
                            echo implode('<br>',$colaboradores);
                            echo '</td></tr>';
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <hr>
        </div>
    </body>
</html>
