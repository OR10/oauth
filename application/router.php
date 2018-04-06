<?php

class Router {
	public static function start()
	{	
		require_once(ROOT.'application/controllers/mainController.php');

		$controller = new mainController();
		$controller->indexAction();
	}
}