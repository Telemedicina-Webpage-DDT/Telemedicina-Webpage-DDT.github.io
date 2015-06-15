<!DOCTYPE html>
<html lang="en">
	<head>
 		<meta charset="utf-8">
 		<meta http-equiv="X-UA-Compatible" content="IE=edge">
 		<meta name="viewport" content="width=device-width, initial-scale=1">
 		<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
 		<meta name="description" content="">
 		<meta name="author" content="">
 		<link rel="icon" href="../../../favicon.ico">
    	<!-- Custom styles for this template -->
    	<link href="signin.css" rel="stylesheet">
    	<!-- Bootstrap Core CSS -->
    	<link rel="stylesheet" href="../../css/bootstrap.min.css" type="text/css">
    	<!-- Custom Fonts -->
    	<link href='http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800' rel='stylesheet' type='text/css'>
    	<link href='http://fonts.googleapis.com/css?family=Merriweather:400,300,300italic,400italic,700,700italic,900,900italic' rel='stylesheet' type='text/css'>
    	<link rel="stylesheet" href="font-awesome/css/font-awesome.min.css" type="text/css">
    	<!-- Plugin CSS -->
    	<link rel="stylesheet" href="../../css/animate.min.css" type="text/css">
    	<!-- Custom CSS -->
    	<link rel="stylesheet" href="../../css/creative.css" type="text/css">

    	<title>Acceda a Telemedicina</title>
	</head>
	<body>
		<header>
    	    <div class="header-content">
	    		<div class="container">
    				<div class="row">
    					<div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 text-center"></div>
    					<div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 text-center">
							<h1>Acceda</h1>
							<?php
							if (!isset($_POST['submit'])){
							?>
							<!-- The HTML login form -->
								<form action="<?=$_SERVER['PHP_SELF']?>" method="post">
									<p>Usuario: </p> <input class="form-control" type="text" name="username" /><br />
									<p>Contraseña: </p> <input class="form-control" type="password" name="password" /><br />
									<input class="btn btn-default btn-xl wow tada" type="submit" name="submit" value="Ingresa" />
								</form>
						</div>
							<?php
							} else {
								require_once("db_const.php");
								$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
								# check connection
								if ($mysqli->connect_errno) {
									echo "<p>MySQL error no {$mysqli->connect_errno} : {$mysqli->connect_error}</p>";
									exit();
								}
							
								$username = $_POST['username'];
								$password = $_POST['password'];
							
								$sql = "SELECT * from usuarios WHERE username LIKE '{$username}' AND password LIKE '{$password}' LIMIT 1";
								$result = $mysqli->query($sql);
								if (!$result->num_rows == 1) {
									#echo "<p href="register.php">Usuario/Contraseña Incorrecto. ¿Registrar?</p>";
									?>

								<h2>Usuario/Contraseña Incorrecto. <a href="register.php">¿Registrar?</a></h2>


								<?php

								} else {
									echo "<p>Ha accedido correctamente</p>";
									// do stuffs
									?>
									<h2>Vaya al <a href="../../index.html">Inicio</a></h2>



									<?php

								}
							}
							?>	

	<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->

					</div>
				</div>
			</div>
		</header>
		
    <script src="../../assets/js/ie10-viewport-bug-workaround.js"></script>	
	</body>

</html>