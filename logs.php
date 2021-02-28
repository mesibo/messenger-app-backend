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



define ("LOG_APPNAME", 'messenger');
define ("LOG_PATH", '/webdata/applogs/');

define ("LOG_ERROR", 0);
define ("LOG_INFORMATION", 1); //LOG_INFO already defined by php http://php.net/manual/en/function.syslog.php
define ("LOG_REQUEST", 10);


function log_filename($type) {
	$prefix = 'error';

	switch($type) {
		case LOG_INFORMATION:
			$prefix = 'info';
			break;

		case LOG_REQUEST:
			$prefix = 'req';
			break;
	}

	$path = LOG_PATH . LOG_APPNAME . '/';
	if (!file_exists($path)) {
	    mkdir($path, 0777, true);
	}

	$file = $path.$prefix.'-'.date("m-Y").'.log';
	if (!file_exists($file)) {
		file_put_contents($file,'');
	}

	return $file;
}

function log_general($msg, $type=LOG_ERROR) { 
	//$date = date('d.m.Y h:i:s'); 
	$date = strftime('%d%m%Y-%H%M%S');
	error_log('#'.$date.': '.$msg."\n", 3, log_filename($type)); 
} 

function log_error($msg) { 
	log_general($msg, LOG_ERROR);
}

function log_info($msg) { 
	log_general($msg, LOG_INFORMATION);
}

function log_print_array($r, &$text) {
	$text .= "[ ";
	foreach ( $r as $key => $value) {
		$text .= "$key - ";
		if(is_array($value))
			log_print_array($value, $text);
		else {
			$value = trim($value);
			$text .= "$value, " ;
		}
	}

	$text = rtrim($text, ', ');
	$text .= " ]";
}	

function log_request($r=null) {
	if(null == $r)
		$r = $_REQUEST;

	//$currtime = strftime('%b %d,%Y %r');
	$currtime = strftime('%d%m%Y%H%M%S');
	$ipaddr =  $_SERVER["REMOTE_ADDR"];
	$clientip = $ipaddr;
	if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$clientip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}

	//$text = "#$currtime: IP - $ipaddr , Client IP - $clientip, ";
	$text = "IP - $ipaddr, client IP - $clientip, ";
	log_print_array($r, $text);
	log_general($text, LOG_REQUEST);
}
