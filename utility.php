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


 

function GetRequestField($field, $defaultval="") {
	$val = $defaultval;

	if(isset($_REQUEST[$field])) {
		$db = MysqliDb::getInstance();
		$val = $db->escape(trim($_REQUEST[$field]));
	}

	return $val;
}

function GetRequestIntField($field, $defaultval="0") {
	return intval(GetRequestField($field, $defaultval));
}

function GetRequestFieldEx($field1, $field2, $defaultval="") {
	$val = $defaultval;

	$db = MysqliDb::getInstance();
	if(isset($_REQUEST[$field1])) {
		return $db->escape(trim($_REQUEST[$field1]));
	}

	if(isset($_REQUEST[$field2])) {
		return $db->escape(trim($_REQUEST[$field2]));
	}

	return $val;
}

function GetIPAddress() {

	if(isset($_SERVER['X-Forwarded-For'])) {
		$ip = $_SERVER['X-Forwarded-For'];
	}
	else if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	else 
		$ip = $_SERVER['REMOTE_ADDR'];

	$ip = htmlspecialchars($ip); 
	if (strpos($ip, '::') === 0) { 
		$ip = substr($ip, strrpos($ip, ':')+1); 
	} 
	//$ipaddr = ip2long($ip); 
	// since ip2long can be negative (see php manual), we use sprintf to get unsigned value
	$ipaddr = sprintf('%u', ip2long($ip));
	return $ipaddr;
}

function get_ip_str() {
	if(isset($_SERVER['X-Forwarded-For'])) {
		$ip = $_SERVER['X-Forwarded-For'];
	}
	else if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	else 
		$ip = $_SERVER['REMOTE_ADDR'];

	$ip = htmlspecialchars($ip); 

	if (strpos($ip, '::') === 0) { 
		$ip = substr($ip, strrpos($ip, ':')+1); 
	}
	return $ip; 
}

