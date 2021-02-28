<?php
/** Copyright (c) 2019 Mesibo
 * https://mesibo.com
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the terms and condition mentioned on https://mesibo.com
 * as well as following conditions are met:
 *
 * Redistributions of source code must retain the above copyright notice, this list
 * of conditions, the following disclaimer and links to documentation and source code
 * repository.
 *
 * Redistributions in binary form must reproduce the above copyright notice, this
 * list of conditions and the following disclaimer in the documentation and/or other
 * materials provided with the distribution.
 *
 * Neither the name of Mesibo nor the names of its contributors may be used to endorse
 * or promote products derived from this software without specific prior written
 * permission.
 *
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA,
 * OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * Documentation
 * https://mesibo.com/documentation/
 *
 * Source Code Repository
 * https://github.com/mesibo/messenger-app-backend
 *
 * Android App Source code Repository
 * https://github.com/mesibo/messenger-app-android
 *
 * iOS App Source code Repository
 * https://github.com/mesibo/messenger-app-ios
 *
 */

function loginui_callbackapi($user, &$result) {
	$device = GetRequestField('d', '');
	$version = GetRequestField('v', '');
	$result['ui'] = "1";
	$ip = get_ip_str();
	//Apple has entire 17.x.x.x ip range
	if(0 === strpos($ip, "17."))
		$result['ui'] = "0";
		$result['ui'] = "0";
	return true;
}

//$user will always be null in login
function login_callbackapi($user, &$result) {
	global $google_map_key;

	$phone = GetRequestField('phone', '');
	$code = GetRequestField('code', '');
	$appid = GetRequestField('appid', '');
	$dt = GetRequestIntField('dt', 0);
	if($appid == '') {
		$result['error'] = 'MISSINGAPPID';
		return false;
	}

	$phonecode = '1';
	if(strlen($phone) < 9)
                return false;

	if($code == '') {
		// Note: here you should send an OTP and save it in the database for the verification
		return true;
	}

	// Note: here you should verify the OTP by comparing against the saved on. For demo, we are 
	// using 123456
	if($code != '123456') {
		$result['error'] = 'BADCODE';
		$result['errmsg'] = 'Invalid OTP';
                return false;
	}

	$restrictions=0;
	$response = MesiboAddUser($phone, $appid, 0, 365*24*60, 0, $restrictions);
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

	add_urls($result);
	add_invite_text($result);
	
	$e = Array();
	$e['uid'] = $uid;
	$e['cc'] = $phonecode;
	$e['phone'] = $phone;
	$e['ts'] = $ts;
	$newuser = dbhelper_insert('users', $e, Array ('uid', 'phone', 'cc', 'ts'));

	$user = dbhelper_getrow("select uid, name, status, photo, ts from users where uid='$uid' limit 1");
	
	$e = Array();
	$e['uid'] = $uid;
	$e['token'] = $token;
	dbhelper_insert('tokens', $e, Array ('token'));

	$result['name'] = $user['name'];
	$result['status'] = $user['status'];
	$result['photo'] = $user['photo'];
	$result['phone'] = $phone;
	$result['cc'] = $phonecode;
	$result['google'] = $google_map_key;
	load_thumbnail($result);


	// if user has relogged in, details would be same so we dont need to send
	if(false || $newuser == 1) {
		$subject = "%NAME% is on Mesibo now";
		$message = "Hey, your contact %NAME% has just joined Mesibo, you may want to say hi!";
		contact_update_notify($phone, 0, $phone, $user['name'], $user['status'], $user['photo'], $user['ts'], $subject, $message);
	}

	return true; 
}

function logout_callbackapi($user, &$result) {
	$uid = $user['uid'];
	// we can also send notification to delete so that user is not there - but user won't get offline messages
	dbhelper_delete('tokens', 'uid', $uid);
	dbhelper_delete('notifytokens', 'uid', $uid);
	return true;
}

