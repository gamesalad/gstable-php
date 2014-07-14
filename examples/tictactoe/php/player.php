<?php

# Get USER ID or create a new user.
function getPlayerID($username, $password) {
	$db = new mysqli("127.0.0.1", "root", "", "tictactoe");
	if (mysqli_connect_errno()) {
		printf("Connection failed: %s\n", mysqli_connect_error());
		exit();
	}
	
	$query = sprintf("select id, username from players where username = '%s'", $username);
	$res = $db->query($query);
	if ($res === false) {
		$err = sprintf("Invalid query: %s\nWhole query: %s\n", $db->error, $query);
		throw new Exception($err);
	}
	
	# THIS IS BAD, DON'T STORE THE PASSWORD. STORE A HASH OR SOMETHING.
	$row = $res->fetch_array(MYSQLI_ASSOC);

	if (is_null($row)) {
		$query = sprintf(
			"INSERT INTO players (username, password) VALUES ('%s', '%s')", 
			$username, $password
		);
		$res = $db->query($query);
		if ($res === false) {
			$err = sprintf("Invalid query: %s\nWhole query: %s\n", $db->error, $query);
			throw new Exception($err);
		}

		$query = sprintf("select id, username from players where username = '%s' and password = '%s'", $username, $password);
		$res = $db->query($query);
		if ($res === false) {
			$err = sprintf("Invalid query: %s\nWhole query: %s\n", $db->error, $query);
			throw new Exception($err);
		}

		$row = $res->fetch_array(MYSQLI_ASSOC);
	}
	
	error_log(sprintf("========= ROW ID: %s", $row["id"]));
	
	return $row["id"];
}
?>