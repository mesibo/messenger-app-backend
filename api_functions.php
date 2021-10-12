<?php
include_once ('mesibohelper.php');
include_once ("json.php");

$downloadurl = 'https://appimages.mesibo.com/';

function GetRequestField($r, $field, $defaultval="") {
	$val = $defaultval;

	if(isset($r[$field])) {
		$val = trim($r[$field]);
	}

	return $val;
}

function add_invite_text(&$result) {
	$invite = array();
	$invite['text'] = 'Hey, I use Mesibo for free messaging, voice and video calls. Download it from https://m.mesibo.com';
	$invite['subject'] = 'Mesibo Messenger: Open Source Messenger';
	$invite['title'] = 'Invite a friend via';
	$result['share'] = $invite; // we already used 'invite' earlier
	$result['invite'] = $invite['text'];
}

function add_urls(&$result) {
	$result['fileurl'] = 'https://appimages.mesibo.com/';
	$result['downloadurl'] = 'https://media.mesibo.com/files/';
	$result['uploadurl'] = 'https://media.mesibo.com/api.php';
	$result['uploadurl'] = 'https://app.mesibo.com/api.php';
	$result['uiconfig'] = 'https://app.mesibo.com/api.php';
		
	$urls = array();
	$urls['upload'] = 'https://s3.mesibo.com/api.php';
	$urls['download'] = 'https://appimages.mesibo.com/'; //only for profile images

	$result['urls'] = $urls;
}

function sendOtpToUser($phone, $otp) {

	/* Now you should send this OTP to your users using your preferred method.
	* For example, SMS
	 */
	$otptext = "otp for $phone is $otp";
	error_log($otptext);
}

//$user will always be null in login
function login_callbackapi($r, &$result) {
	$name = GetRequestField($r, 'name', '');
	$phone = GetRequestField($r, 'phone', '');
	$otp = GetRequestField($r, 'otp', '');
	$appid = GetRequestField($r, 'appid', '');
	$dt = GetRequestField($r, 'dt', 0);
	if($appid == '') {
		$result['error'] = 'MISSINGAPPID';
		return false;
	}

	$phone = ltrim($phone, " +0±\n\r\t\v\0");

	if(strlen($phone) < 9)
		return false;

	// if user has not supplied otp, let's generate one
	if($otp == '') {
		$result['title'] = "Important: OTP";
		$result['message'] = "mesibo will NOT send OTP. Instead, you can generate OTPs from your mesibo account. Sign up at https://mesibo.com/console and click on the `Demo Apps` link from the left navigation bar and follow the instructions.";
		$result['delay'] = 2000;
		$response = MesiboOTP($phone, 5, 300, 1);

		// should not happen. If it happens, check the quota
		if(!$response || !$response['result']) {
			$result['error'] = 'BADAPP';
			return false;
		}

		$otp = $response['otp'];

		sendOtpToUser($phone, $otp);
		return true;
	}

	$response = MesiboAddUser($name, $phone, $appid, 0, 365*24*60, 0, $otp, 0);
	if(!$response || !$response['result']) {
		//print_r($response);
		$result['error'] = 'BADUSER';
		return false;
	}


	$newuser = 0;
	$ts = time(); //earlier we were using mysql unix_timestamp
	$token = $response['user']['token'];
	$uid = $response['user']['uid'];
	$result['token'] = $token;
	$result['phone'] = $phone;

	add_urls($result);
	add_invite_text($result);

	return true; 
}

function logout_callbackapi($user, &$result) {
	$uid = $user['uid'];
	return true;
}

function DoExit($result, $data) {
	$data['result'] = "FAIL";

	if($result) {
		$data['result'] = "OK";
	}

	$jsondata = safe_json_encode($data);
	print $jsondata;
	flush();
	exit;
}

function OnEmptyExit($var, $code) {
	if($var == '') {
		$result = array();
		$result['code'] = $code;
		DoExit(false, $result);
	}
}


