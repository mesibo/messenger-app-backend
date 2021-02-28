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



	include_once("includes.php");

	$mysqli = new mysqli ($db_host,  $db_user,  $db_pass, $db_name);
	if($mysqli->connect_errno) {
		DoExit(APIERROR_DBERROR, $result);
	}

	$db = new MysqliDb ($mysqli);
	if(1) {
		log_request($_REQUEST);
	}

	$op=GetRequestField('op', '');
	$json=GetRequestField('json', 1);
	$token = GetRequestField('token', '');
	OnEmptyExit($op, 'NOOP');
	$result = array();
	$result['op'] = $op;

	$user = null;
	if($op != 'login' && $op != 'loginui') {
		if(strlen($token) > 16)
			$user = get_user_from_token($token);

		if(!$user) {
			if($op == 'logout') {
				DoExit(true, $result);
			}
			$result['error'] = 'AUTHFAIL';
			DoExit(false, $result);
		}
		$user['token'] = $token;
	}


	$op = strtolower($op);
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
	$res = $apifuncname($user, $result);

	//for login, we will add it from inside
	if($res === true && $token != '') {
		add_invite_text($result);
		add_urls($result);
	}
	DoExit($res, $result);

