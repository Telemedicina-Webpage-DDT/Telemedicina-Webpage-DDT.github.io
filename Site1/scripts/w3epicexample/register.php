<html>
<head>
	<title>Registro de Usuario - Telemedicina</title>
</head>
<body>	
<h1>Rellene los siguientes campos</h1>
<?php
require_once("db_const.php");
if (!isset($_POST['submit'])) {
?>	<!-- The HTML registration form -->
	<form action="<?=$_SERVER['PHP_SELF']?>" method="post">
		Usuario: <input type="text" name="username" /><br />
		Contraseña: <input type="password" name="password" /><br />
		Primer Nombre: <input type="text" name="first_name" /><br />
		Apellido: <input type="text" name="last_name" /><br />
		E-mail: <input type="type" name="email" /><br />

		<input type="submit" name="submit" value="Ingrese" />
	</form>
<?php
} else {
## connect mysql server
	$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
	# check connection
	if ($mysqli->connect_errno) {
		echo "<p>MySQL error no {$mysqli->connect_errno} : {$mysqli->connect_error}</p>";
		exit();
	}
## query database
	# prepare data for insertion
	$username	= $_POST['username'];
	$password	= $_POST['password'];
	$first_name	= $_POST['first_name'];
	$last_name	= $_POST['last_name'];
	$email		= $_POST['email'];

	# check if username and email exist else insert
	$exists = 0;
	$result = $mysqli->query("SELECT username from usuarios WHERE username = '{$username}' LIMIT 1");
	if ($result->num_rows == 1) {
		$exists = 1;
		$result = $mysqli->query("SELECT email from usuarios WHERE email = '{$email}' LIMIT 1");
		if ($result->num_rows == 1) $exists = 2;	
	} else {
		$result = $mysqli->query("SELECT email from usuarios WHERE email = '{$email}' LIMIT 1");
		if ($result->num_rows == 1) $exists = 3;
	}

	if ($exists == 1) echo "<p>Username already exists!</p>";
	else if ($exists == 2) echo "<p>Username and Email already exists!</p>";
	else if ($exists == 3) echo "<p>Email already exists!</p>";
	else {
		# insert data into mysql database
		$sql = "INSERT  INTO `usuarios` (`username`, `password`, `first_name`, `last_name`, `email`) 
				VALUES ('{$username}', '{$password}', '{$first_name}', '{$last_name}', '{$email}')";

		if ($mysqli->query($sql)) {
			//echo "New Record has id ".$mysqli->insert_id;
			echo "<p>Registred successfully!</p>";
		} else {
			echo "<p>MySQL error no {$mysqli->errno} : {$mysqli->error}</p>";
			exit();
		}
	}
}
?>		
</body>
</html>