<?php

require_once(dirname(__FILE__)."/config.inc.php");
require_once(dirname(__FILE__)."/classes/session.class.php");
$session = new Session($config);
$template = $config->getTemplate($session->getAuth());

if (isset($_REQUEST["login"])) {
	$user = isset($_REQUEST["username"]) ? stripslashes($_REQUEST["username"]) : null;
	$pass = isset($_REQUEST["password"]) ? stripslashes($_REQUEST["password"]) : null;

	try {
		$auth = $config->getAuth($user, $pass);
		$session->login($auth);
		$template->viewloginsuccess($auth);
	} catch (AuthException $e) {
		$template->viewloginfailed();
		exit;
	}
}

if (isset($_REQUEST["logout"])) {
	$session->login($config->getAnonymousAuth());
	$template->viewlogoutsuccess();
}

if ($session->getAuth()->isAnonymous()) {
	$template->viewloginform();
	exit;
}

$template->viewuserpanel();

?>
