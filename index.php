<?php
	include_once("httpheaders.php");
	ini_set('default_charset', 'UTF-8');
	include_once ("errorhandler.php");

	include_once ('api_functions.php');

	$r = json_decode(stripslashes(file_get_contents("php://input")), true);

	$op = GetRequestField($r, 'op', '');
	if($op == '') {
		header('Location: https://mesibo.com');
		exit;
	}

	$token = GetRequestField($r, 'token', '');
	
	//OnEmptyExit($op, 'NOOP');
	$result = array();
	$op = strtolower($op);
	$result['op'] = $op;
	$apifuncname = $op."_callbackapi";
	if(!function_exists($apifuncname)) {
		$apifuncname = "$op"."_callbackapi";
		if(!function_exists($apifuncname)) {
			$result['error'] = 'BADOP';
			DoExit(false, $result);
		}
	}

	$result['op'] = $op;
	$result['ts'] = time(); //always send time so that client know the time diff
	$res = $apifuncname($r, $result);

	//for login, we will add it from inside
	if($res === true && $token != '') {
	}
	DoExit($res, $result);

