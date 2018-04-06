<?php
require_once(ROOT.'application/models/mainModel.php');

class mainController {
	public function __construct()
	{
		$this->model = new mainModel();
	}

	public function indexAction()
	{		
		$uri = trim($_SERVER['REQUEST_URI'], '/');
		$msg = '';

		if ($uri == 'oauth/token') {
			$email = isset($_POST['email']) && !empty($_POST['email']) ? $_POST['email'] : false;
			$password = isset($_POST['password']) && !empty($_POST['password']) ? $_POST['password'] : false;

			if ($email && $password) {
				if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
					$user = $this->model->checkUserCredentials($email, md5($password));
					if ($user != false) {
						$tokenData = unserialize($_COOKIE['access_token']);					
						$currentUserToken = $this->model->getTokenByEmail($email);
						if (!isset($_COOKIE['access_token']) || $tokenData['token_value'] != $currentUserToken['token_value']) {
							$token = $this->generateToken();
							$this->model->updateToken($email, $token);
						}
						$tokenData = unserialize($_COOKIE['access_token']);
						$expiresAt = $tokenData['expires_at'];
						$tokenValue = $tokenData['token_value'];					

						$userDataArr = [];
						$userDataArr['access_token'] = $tokenValue;
						$userDataArr['expires_at'] = $expiresAt;
					} else {
						$isUserExists = $this->model->checkEmailExistence($email);
						if ($isUserExists == true) {
							$msg .= "Bad password!";
						} else {
							$this->model->createNewUser($email, md5($password));
							$token = $this->generateToken();
							$this->model->saveToken($email, $token);
							$msg .= "New user was added with this email: ".$email." and this token: ".$token;
						}
					}
				} else {
					$msg .= 'Uncorrect email address!';
				}				
			}
		} elseif ($uri == 'user') {
			$requestHeaders = apache_request_headers();
			if (!isset($_COOKIE['access_token'])) {
				$msg .= "Token is not set yet!";
			} else {
				$tokenData = unserialize($_COOKIE['access_token']);
				$tokenValue = $tokenData['token_value'];
				$currentTokenValue = $requestHeaders['Authorization'];
				if ($tokenValue != $currentTokenValue) {
					$msg .= "Token is not correct!";
				} else {
					$user = $this->model->getUserByToken($tokenValue);

					if ($user != false) {
						$userDataArr = [];
						$userDataArr['email'] = $user['user_email'];
					}
				}
			}
		} else {
			$msg .= 'No URI matches!';
		}

		if ($msg != '') {
			$response = $msg;
		} else {
			header('Content-Type: application/json');
			if (isset($userDataArr)) {
				$response = $userDataArr;
			} else {
				$response = 'No user data!';
			}
		}

		echo json_encode($response);
	}

	public function generateToken()
	{
		return md5(uniqid());
	}
}