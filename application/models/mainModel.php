<?php

class mainModel {
	public function __construct()
	{
		$dbHost = DB_HOST;
		$dbName = DB_NAME;
		$dbUser = DB_USER;
		$dbPassword = DB_PASSWORD;
		$this->pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPassword);
	}

	public function checkUserCredentials($email, $password)
	{
		$stmt = $this->pdo->prepare('SELECT u.user_email FROM users as u 
			WHERE u.user_email = :email AND u.user_password = :password LIMIT 1');
		$stmt->execute(array('email' => $email, 'password' => $password));
		$res = $stmt->fetch();

		return $res;
	}

	public function checkEmailExistence($email)
	{
		$stmt = $this->pdo->prepare('SELECT COUNT(*) FROM users as u WHERE u.user_email = :email LIMIT 1');
		$stmt->execute(array('email' => $email));
		$resCount = $stmt->fetch();

		if ($resCount[0] == '1') {
			$res = true;
		} else {
			$res = false;
		}

		return $res;
	}

	public function createNewUser($email, $password)
	{
		$stmt = $this->pdo->prepare('INSERT INTO users (user_email, user_password) 
			VALUES (:email, :password)');
		$stmt->execute(array('email' => $email, 'password' => $password));
	}

	public function saveToken($email, $token)
	{
		$expires = time()+60;
		$tokenData = [];
		$tokenData['token_value'] = $token;
		$tokenData['expires_at'] = $expires;
		setcookie('access_token', serialize($tokenData), $expires, '/');
		$_COOKIE['access_token'] = serialize($tokenData); // Force set of the cookie to use immediately

		$stmt = $this->pdo->prepare('INSERT INTO tokens (token_email, token_value, token_expires) 
			VALUES (:email, :token, :expires)');
		$stmt->execute(array('email' => $email, 'token' => $token, 'expires' => $expires));
	}

	public function updateToken($email, $token)
	{
		// Delete old token
		$stmt = $this->pdo->prepare('DELETE FROM tokens 
			WHERE token_email = :email');
		$stmt->execute(array('email' => $email));
		// Set new token
		$this->saveToken($email, $token);
	}

	public function getUserByToken($tokenValue)
	{
		$stmt = $this->pdo->prepare('SELECT u.user_email FROM users as u 
			LEFT JOIN tokens as t 
			ON u.user_email = t.token_email 
			WHERE t.token_value = :token_value LIMIT 1');
		$stmt->execute(array('token_value' => $tokenValue));
		$res = $stmt->fetch();

		return $res;
	}

	public function getTokenByEmail($email)
	{
		$stmt = $this->pdo->prepare('SELECT t.token_value FROM tokens as t 
			LEFT JOIN users as u 
			ON u.user_email = t.token_email 
			WHERE t.token_email = :user_email LIMIT 1');
		$stmt->execute(array('user_email' => $email));
		$res = $stmt->fetch();

		return $res;
	}

	public function getAllUsers()
	{
		$res = [];
		$stmt = $this->pdo->query('SELECT u.user_email FROM users as u');
		while ($row = $stmt->fetch()) {
			$res[] = $row;
		}

		return $res;
	}
}