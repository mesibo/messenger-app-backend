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



/********************************************************************************
 This File contains all functions necessary for sending a request using  Mesibo API's to perform
 the operations as mentioned in the documentation of the functions given below.
 
 IMPORTANT: All functions are dependent on the Mesibo API function (mesiboapi.php).
*********************************************************************************/
require_once('mesiboapi.php');

/********************************************************************************
Descripton: Creates a New User   

Parameters:	$email- A valid Email address which will be used for login. 
			$password- User's prefered password.  
			$name- User's name.

Return Values: True- When user account is Created.  
			   False- If user already exists. 
*********************************************************************************/
function MesiboAddUser($address, $appid, $session, $expiry, $flag, $restrictions=0) {
    
	$parameters=array();
	$parameters['op']='useradd';
	$parameters['addr']=$address;
	$parameters['appid']=$appid;
	$parameters['session']=$session;
	$parameters['expiry']=$expiry;
	$parameters['flag']=$flag;
	$parameters['restrictions']=$restrictions;
	return MesiboAPI($parameters);
}

function MesiboDeleteUser($addr, $appid) {  
    
	$parameters=array();
	$parameters['op']='deluser';
	$parameters['addr']=$address;
	$parameters['appid']=$appid;
    
	return MesiboAPI($parameters);
}    

function MesiboDeleteToken($token) {  
    
	$parameters=array();
	$parameters['op']='deltoken';
	$parameters['token']=$token;
	return MesiboAPI($parameters);
}    

function MesiboSetGroup($groupid, $name, $flag, $members) {  
	$parameters=array();
	$parameters['gid']=$groupid;
	$parameters['op']=$groupid?'groupset':'groupadd';
	$parameters['name']=$name;
	$parameters['flag']=$flag;
	$parameters['m']=$members;
	return MesiboAPI($parameters);
}    

function MesiboDeleteGroup($groupid) {  
	$parameters=array();
	$parameters['op']='groupdel';
	$parameters['gid']=$groupid;
	return MesiboAPI($parameters);
}    

function MesiboEditMembers($groupid, $members, $delete) {  
	$parameters=array();
	$parameters['op']='groupeditmembers';
	$parameters['gid']=$groupid;
	$parameters['m']=$members;
	$parameters['delete']=$delete;
	return MesiboAPI($parameters);
}    

function MesiboGetMembers($groupid) {  
	$parameters=array();
	$parameters['op']='getmembers';
	$parameters['gid']=$groupid;
	return MesiboAPI($parameters);
}    

function MesiboMessage($from, $to, $groupid, $channel, $type, $expiry, $flag, $message, $forced=0) {  
	$parameters=array();
	$parameters['op']='message';
	$parameters['from']=$from;
	$parameters['to']=$to;
	$parameters['gid']=$groupid;
	$parameters['channel']=$channel;
	$parameters['type']=$type;
	$parameters['expiry']=$expiry;
	$parameters['flag']=$flag;
	$parameters['forced']=$forced;
	$parameters['msg']=$message;
	return MesiboAPI($parameters);
}    

