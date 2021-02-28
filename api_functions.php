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
include_once("login.php");

function get_uid_from_token($token) {
	$db = MysqliDb::getInstance();
	$db->where('token', $token);
	return $db->getValue('tokens', 'uid');
}

function get_addr_from_token($token) {
	$db = MysqliDb::getInstance();
	$db->join("users u", "u.uid=t.uid", "INNER");
	$db->where('token', $token);
	return $db->getValue('tokens t', 'u.phone');
}

function get_user_from_token($token) {
	$db = MysqliDb::getInstance();
	$db->join("users u", "u.uid=t.uid", "INNER");
	$db->where('token', $token);
	$rv = $db->get('tokens t');
	if($db->count > 0)
		return $rv[0];
	return false;
}

function notify($from, $to, $subject, $message, $type, $name="", $phone="", $gid=0, $status="", $photo="", $ts=0) {
	if($to == '')
		return;

	if($from == '') {
		$from = '100';
	} else {
		//not required as it will anyway will be rejected by mesibo
		$to = str_replace($from, '', $to);
		$to = str_replace(',,', ',', $to);
	}

	$n = array();
	$n['subject'] = $subject;
	$n['msg'] = $message;
	$n['type'] = $type;
	$n['action'] = 0;
	if(!$photo)
		$photo = ''; // don't send boolean, we are decoding it as string 
	
	if($phone != '' || $gid > 0) {
		$n['name'] = $name;
		$n['gid'] = $gid;
		$n['phone'] = $phone;
		$n['status'] = $status;

		if($gid) {
			$n['phone'] = '';
			$n['status'] = '';
			$n['members'] = $status;
		}

		$n['photo'] = $photo;
		$n['ts'] = $ts;
		load_thumbnail($n);
	}
	
	$channel = 0;
	$type = 1;
	$groupid = 0;
	$expiry = 3600; // 1 hour, we should accordingly set full sync timeout in login 
	$flag = 4; // transient
	//TBD, some of these notification can be only when user is online (don't send if offline)
	$m = safe_json_encode($n);
	//print "Notify ($from) ($to) ($groupid) ($m))";

	//MesiboMessage($from, $to, $groupid, $channel, $expiry, $flag, $m, $r);
	MesiboMessage($from, $to, $groupid, $channel, $type, $expiry, $flag, $m, 1);
}

/*
1) Send to all users having peer as a contact (type 0)
2) Send to all group members (type 1)
3) send to a peer (type 2)

*/

function contact_update_notify($from, $type, $to, $name, $status, $photo, $ts, $subject, $message) {
	$result = null;

	$contactcond = ''; // 'subscribed=1 and ' ; //send live update for all or only subscribed contacts
	$phone = $to;
	$gid = 0;
	
	if(0 == $type) {
		$to = dbhelper_getvalue("select group_concat(a.phone) phones from users a, contacts b where $contactcond b.phone='$to' and a.uid=b.uid and a.phone!='$to'", 'phones');
	}
	else if(1 == $type) {
		$gid = $to;
		$phone = '';
		//$result = dbhelper_getvalue("select phone from members where gid=$to and phone!='$phone'");
		$to = get_group_members($gid, $admincount);
		if($status == '') {
			$status = "$admincount:$to";
			if(strlen($status) > 256)
				$status = "";
		}
	}

	notify($from, $to, $subject, $message, 0, $name, $phone, $gid, $status, basename($photo), $ts);
	return true;
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
	$urls = array();
	$urls['upload'] = 'https://s3.mesibo.com/api.php';
	$urls['download'] = 'https://appimages.mesibo.com/'; //only for profile images
	$result['urls'] = $urls;
}

function get_group($gid, $phone) {
	return dbhelper_getrow("select name, photo, ts, role from groups a, members b where a.gid='$gid' and a.gid=b.gid and b.phone='$phone' limit 1;");
}

function profile_callbackapi($user, &$result) {
	$uid = $user['uid'];
	$phone = $user['phone'];

	$name = GetRequestField('name', '');
	//print $name;
	$status = GetRequestField('status', '');
	$gid = GetRequestIntField('gid', 0);

	$filename = get_uploaded_file();
	$filecond = '';
	if($filename) 
		$filecond=",photo='$filename' ";

	$ts = time();
	if($gid > 0) {
		$group = get_group($gid, $phone);
		if(null == $group || $group['role'] == 0) {
			$result['error'] = 'BADGROUP';
			return false;
		}

		$db = MysqliDb::getInstance();
		$db->rawQuery("update groups set name='$name', ts=$ts $filecond where gid=$gid");
		$subject = $user['name']. " has changed group name to $name";
		contact_update_notify($phone, 1, $gid, $name, "", $group['photo'], $ts, $subject, "");
		return true;
	}

	$db = MysqliDb::getInstance();
	$db->rawQuery("update users set name='$name', status='$status', ts=$ts $filecond where uid=$uid");

	contact_update_notify($phone, 0, $user['phone'], $name, $status, $user['photo'], $ts, "", "");
	return true;
}

function setnotify_callbackapi($user, &$result) {
	$uid = $user['uid'];
	$ntoken = GetRequestField('notifytoken', '');
	$dt = GetRequestField('dt', 0);
	$production = GetRequestIntField('prod', 0);
	
	$e = Array();
	$e['uid'] = $uid;
	$e['token'] = $ntoken;
	$e['device'] = $dt;
	$e['production'] = $production;
	dbhelper_insert('notifytokens', $e, Array ('token', 'device', 'production'));
	
	return true;
}

function get_uploaded_file() {
	global $files_path, $files_tn_path;
	include_once("upload.php");
	$folder = $files_path;
	$content = '';

	//print "file:";
	foreach($_FILES as $key => $value) {
		$filename = upload($key, "$folder");
		if(!$filename) {
			//$temp = safe_json_encode($_FILES);
			//print $temp;
			return false;
		}

		return $filename;
	}

	return false;
}

function upload_callbackapi($user, &$result) {
	global $downloadurl;
	global $files_path, $files_tn_path;
	$profile = GetRequestIntField('profile', 0);
	$gid = GetRequestIntField('gid', 0);
	$delete = GetRequestIntField('delete', 0);

	$uid = $user['uid'];
	$phone = $user['phone'];

	$filename = '';

	if(0 == $delete) {
		$filename = get_uploaded_file();
		if(!$filename) {
			$result['error'] = 'MISSINGFILE';
			return false;
		}

		if($profile) {
			$srcfile = $files_path."/$filename";
			$destfile = $files_tn_path."/$filename";
			if(!image_convert($srcfile, '', $destfile, 100, 100, 50)) {
				$result['error'] = 'BADIMAGE';
				return false;
			}
		}
	}

	$ts = time();
	$result['phone'] = "";
	$result['gid'] = 0;
	$result['profile'] = $profile;

	$fileurl = $downloadurl.$filename;

	if($profile) {
		//TBD, delete users existing photo
		if($gid > 0) {
			$group = get_group($gid, $phone);
			if(null == $group || 0 == $group['role']) {
				$result['error'] = 'BADGROUP';
				return false;
			}

			$db = MysqliDb::getInstance();
			$db->rawQuery("update groups set photo='$filename', ts=$ts where gid=$gid");
			if($db->count > 0)
				contact_update_notify($phone, 1, $gid, $group['name'], "", $filename, $ts, $subject, "");

			$result['photo'] = $filename;
			$result['phone'] = ""; // so that profile can be updated on upload results
			$result['gid'] = $gid;
			$result['name'] = $group['name'];
			$result['status'] = $group['status'];
			$result['ts'] = $ts;
			load_thumbnail($result);
			return true;
		}

		$result['photo'] = $filename;
		$result['phone'] = $phone;
		$result['gid'] = 0;
		$result['name'] = $user['name'];
		$result['status'] = $user['status'];
		$result['ts'] = $ts;
		load_thumbnail($result);

		$db = MysqliDb::getInstance();
		$db->rawQuery("update users set photo='$filename', ts=$ts where uid=$uid limit 1");
		$user['photo'] = $fileurl;
		if($db->count > 0)
			contact_update_notify($phone, 0, $user['phone'], $user['name'], $user['status'], $user['photo'], $user['ts'], "", "");
	}

	$result['file'] = $fileurl; // file field is need for file upload
	return true;
}

function load_thumbnail(&$c) {
	global $files_path, $files_tn_path;
	if($c['photo'] != '') {
		$tnfile = $files_tn_path."/".basename($c['photo']);
		if(file_exists($tnfile)) {
			$tn = file_get_contents($tnfile);
			if($tn)
				$c['tn'] = base64_encode($tn);
		}
	}
}

function getcontacts_callbackapi($user, &$result) {
	$ts = GetRequestIntField('ts', 0);
	$phones = GetRequestField('phones', '');
	$reset = GetRequestIntField('reset', 0);

	$uid = $user['uid'];

	if($reset) {
		dbhelper_delete('contacts', 'uid', $uid);
	}

	//DEMO - returning all contacts without any check
	//$query = "select name, status, phone, photo, 0 gid from users where ts > $ts and uid!=$uid union select name, status, '' phone, photo, gid from groups where ts > $ts limit 500";
	//TBD, remove cast later - used only to make it compatible with previous version
	$fields = 'name, status, cast(a.phone as char) phone, photo, "0" gid, cast(ts as char) ts';
	$query = "select $fields from users a, contacts b where ts > $ts and b.uid=$uid and a.phone=b.phone order by ts asc limit 1000";

	$phones = str_replace('null', $user['cc'], $phones); //temporary
	if($phones != '') {
		$p = explode(',', $phones);
		foreach($p as $phone) {
			if(strlen($phone) > 6) {
				dbhelper_rawquery("insert into contacts set uid=$uid, phone=$phone on duplicate key update phone=phone");
			}
		}
		// return all contacts passed in phones (ignore ts as contacts send by users will not be in same order)
		$query = "select $fields from users a where phone in ($phones) and uid!=$uid order by ts asc limit 1000";
	}

	if(!$reset || $phones != '')
		webapi_getrecords($query, $result, 'contactsprocessing_callback', 'contacts', '');
	//else 
	//	$result['contacts'] = array();

	// group temporariliy disabled to test new contacts upload
	// need to fix group based on members
	if($phones == '') {
		$p = $user['phone'];

		//TBD, we MUSE use uid instead of phone else if it is reasssign to someone else, old groups will be assigned to it
		//TBD, remove cast later - used only to make it compatible with previous version
		$query = "select name, '' status, '' phone, photo, cast(groups.gid as char) gid, cast(ts as char)ts from groups, members where ts > $ts and groups.gid=members.gid and members.phone='$p' order by ts asc limit 500;";
		$groups = array();
		if(webapi_getrecords($query, $groups, null, 'contacts', '') && isset($groups['contacts'])) {
			if(isset($result['contacts']) && isset($groups['contacts']))
				$result['contacts'] = array_merge($result['contacts'], $groups['contacts']);
			else if(isset($groups['contacts'])) {
				$result['contacts'] = $groups['contacts'];
			}
		}
	}

	if(isset($result['contacts'])) {
		foreach($result['contacts'] as &$c) {
			load_thumbnail($c);
			if($c['gid'] > 0) {
				$members = get_group_members($c['gid'], $admincount);
				$c['members'] = "$admincount:$members";
			}
		}
	}

	return true;
}

function delcontacts_callbackapi($user, &$result) {
	$phones = GetRequestField('phones', '');
	$uid = $user['uid'];
	$m = explode(',', $phones);
	$db = MysqliDb::getInstance();
	$db->where('uid', $uid);
	$db->where('phone', $m, 'in');
	$db->delete('contacts');
	return true;
}

function setgroup_callbackapi($user, &$result) {
	global $files_path, $files_tn_path;
	$gid = GetRequestIntField('gid', 0);
	$type = GetRequestIntField('type', 0);
	$name = GetRequestField('name', '');
	//$status = GetRequestField('status', '');
	$members = GetRequestField('m', '');
	$delete = GetRequestIntField('delete', 0);

	// we are doing url decode because if file is sent along with this, it's multipart data and unicode seems to be screwed
	$name = urldecode($name);

	$members = urldecode($members);

	$uid = $user['uid'];
	$phone = $user['phone'];
	$ts = time();

	if($gid > 0) {
		$group = get_group($gid, $phone);
		if(null == $group || 0 == $group['role']) {
			$result['error'] = 'BADGROUP';
			return false;
		}

		if($name == '')
			$name = $group['name'];
	}

	$filename = get_uploaded_file();
	if($filename) {
		$srcfile = $files_path."/$filename";
		$destfile = $files_tn_path."/$filename";
		if(!image_convert($srcfile, '', $destfile, 100, 100, 50)) {
			$result['error'] = 'BADIMAGE';
			return false;
		}
	}

	//get existing filename
	if(!$filename && $gid > 0)  {
		$filename = $group['photo'];
	}
	
	$db = MysqliDb::getInstance();
	$db->where('gid', $gid);
	$db->where('role', 2);
	$owner = $db->getValue('members', 'phone');

	$members = "$phone,$members";
	if($owner) {
		$members = "$phone,$members,$owner";
	}

	$response = MesiboSetGroup($gid, $name, 0, $members, $gid);
	if(!$response || !$response['result']) {
		$result['error'] = $response['error'];
		return false;
	}

	$gid = $response['group']['gid'];

	$e = Array();
	$e['gid'] = $gid;
	$e['type'] = $type;
	$e['name'] = $name;
	$e['status'] = '';
	$e['ts'] = $ts;

	$duplicates = Array ('type', 'name', 'status', 'ts');
	if($filename) {
		$e['photo'] = $filename;
		array_push($duplicates, 'photo');
	}
	dbhelper_insert('groups', $e, $duplicates);
	
	$db = MysqliDb::getInstance();
	if(!$db) return false;

	//delete only those members which are not in list - we are doing this so that user roles are maintained
	//$db->rawQuery("delete from members where gid=$gid and phone not in ($members) and role!=2");
	$m = explode(',', $members);
	$db = MysqliDb::getInstance();
	$db->where('gid', $gid);
	$db->where('role', 2, '!=');
	$db->where('phone', $m, 'not in');
	$db->delete('members');

	$result['gid'] = $gid;
	$result['photo'] = $filename?$filename:"";
	$result['name'] = $name;
	$result['ts'] = $ts;
	$result['status'] = $ts;
	load_thumbnail($result);

	editmembers($gid, $members, 0, $phone);
	$members = get_group_members($gid, $admincount);
	$result['members'] = "$admincount:$members";

	$subject = "You have been added to group $name";
	contact_update_notify($phone, 1, $gid, $name, "", $filename, $ts, $subject, "");
	return true;
}

function delgroup_callbackapi($user, &$result) {
	$gid = GetRequestIntField('gid', 0);
	if(0 == $gid) {
		$result['error'] = 'MISSINGPARA';
		return false;
	}

	$members = GetRequestField('m', '');
	$members = urldecode($members);

	$uid = $user['uid'];
	$phone = $user['phone'];
	$ts = time();
	$result['gid'] = $gid;

	$group = get_group($gid, $phone);
	if(null == $group) {
		$result['error'] = 'BADGROUP';
		return false;
	}

	// only owner can delete group
	if(2 != $group['role']) {
		// if user is not admin, api used for exiting group

		$db = MysqliDb::getInstance();
		$e = Array();
		$e['ts'] = $ts;
		$db->where('gid', $gid);
		$db->update('groups', $e);

		$response = MesiboEditMembers($gid, $phone, 1);
		if(!$response || !$response['result']) 
			return false;

		editmembers($gid, $phone, 1, null);
		contact_update_notify($phone, 1, $gid, $group['name'], "", $group['photo'], $ts, "", "");
		return true;
	}

	//send original name in group notification
	//TBD, this may not work as by the time server process this, we delete group below and then 
	//mesibo server has no way to deliver this group message
	contact_update_notify($phone, 1, $gid, $group['name'], "", "", 0, "", "");

	dbhelper_delete('groups', 'gid', $gid);
	dbhelper_delete('members', 'gid', $gid);

	$response = MesiboDeleteGroup($gid);
	if(!$response || !$response['result']) {
		$result['error'] = $response['error'];
		return false;
	}
	return true;
}

function setadmin_callbackapi($user, &$result) {
	$gid = GetRequestIntField('gid', 0);
	$admin = GetRequestIntField('admin', 0);
	if(0 == $gid) {
		$result['error'] = 'MISSINGPARA';
		return false;
	}

	$members = GetRequestField('m', '');
	$members = urldecode($members);

	$uid = $user['uid'];
	$phone = $user['phone'];
	$ts = time();

	$group = get_group($gid, $phone);
	if(null == $group || 0 == $group['role']) {
		$result['error'] = 'BADGROUP';
		return false;
	}

	// to ensure no one can make themselves owner
	if($admin > 0)
		$admin = 1;

	$db = MysqliDb::getInstance();
	$db->rawQuery("update members set role=$admin where gid=$gid and phone in ($members)");

	if($db->count > 0) {
		$db->rawQuery("update groups set ts=$ts where gid=$gid limit 1");
		contact_update_notify($phone, 1, $gid, $group['name'], "", $group['photo'], $ts, "", "");
	}

	$result['gid'] = $gid;
	$members = get_group_members($gid, $admincount);
	$result['members'] = "$admincount:$members";
	return true;
}

function contactsprocessing_callback($i, &$row) {
	if(!isset($row['status']) || is_null($row['status']))
		$row['status'] = '';
	if(!isset($row['name']) || is_null($row['name']))
		$row['name'] = '';
}

function get_group_members($gid, &$admincount) {

	$r = dbhelper_getrow("select sum(if(role, 1, 0)) c, group_concat(phone order by role desc) m from members where gid=$gid");
	$admincount = $r['c'];
	return $r['m'];
}

function getgroup_callbackapi($user, &$result) {
	$gid = GetRequestIntField('gid', 0);

	if(0 == $gid) {
		$result['error'] = 'MISSINGPARA';
		return false;
	}

	$uid = $user['uid'];
	$phone = $user['phone'];

	//TBD, do local validation if user part of the group etc
	$db = MysqliDb::getInstance();
	$db->where('gid', $gid);
	$db->where('phone', $phone);
	$p = $db->getValue('members', 'phone');

	if(!$p) {
		$result['error'] = 'NOTMEMBER';
		return false;
	}

	$group = get_group($gid, $phone);
	if(!$group)
		return false;

	$result['gid'] = $gid;
	$result['name'] = $group['name'];
	$result['status'] = "";
	$result['photo'] = $group['photo'];
	$result['ts'] = $group['ts'];
	load_thumbnail($result);

	$members = get_group_members($gid, $admincount);
	$result['members'] = "$admincount:$members";

	return true;
}


function editmembers_callbackapi($user, &$result) {
	$gid = GetRequestIntField('gid', 0);
	//TBD, we need api to add multiple members
	$members = GetRequestField('m', '');
	$delete = GetRequestIntField('delete', 0);
	$flag = GetRequestIntField('flag', 0);

	$uid = $user['uid'];
	$phone = $user['phone'];

	if(0 == $gid || $members == '') {
		$result['error'] = 'MISSINGPARA';
		return false;
	}

	$group = get_group($gid, $phone);
	if(null == $group || 0 == $group['role']) {
		$result['error'] = 'BADGROUP';
		return false;
	}

	$response = MesiboEditMembers($gid, $members, $delete, $res);
	if(!$response || !$response['result']) {
		$result['error'] = $gid;
		return false;
	}

	$ts = time();
	$db = MysqliDb::getInstance();
	$db->rawQuery("update groups set ts=$ts where gid=$gid limit 1");
	editmembers($gid, $members, $delete, "");
	//TBD, we need to notify deleted members too
	contact_update_notify($phone, 1, $gid, $group['name'], "", $group['photo'], $group['ts'], "", "");
	$members = get_group_members($gid, $admincount);
	$result['members'] = "$admincount:$members";
	$result['gid'] = $gid;
	return true;
}

function editmembers($gid, $members, $remove, $adminphone) {
	//print "$gid-$members";
	$m = explode(',', $members);
	if($remove) {
		$db = MysqliDb::getInstance();
		$db->where('gid', $gid);
		$db->where('phone', $m, 'in');
		$db->delete('members');
		return;
	}

	$to = '';
	foreach($m as $phone) {
		$role=0;
		$phone = trim($phone);
		if($phone == '')
			continue;

		if(0 == strcmp($adminphone, $phone))
			$role=2; // 2 is for group owner, which other admins can't remove
		
		$e = Array();
		$e['gid'] = $gid;
		$e['phone'] = $phone;
		$e['role'] = $role;
		dbhelper_insert('members', $e,  Array ('phone')); 

		//false as we are now sending to all
		if(false && 1 == $db->count && 0 != strcmp($userphone, $phone)) {
			$to .= "$phone,";
		}
	}

	return;
}


function RestoreText($text) {
	$text = str_replace('\r', "\r", $text);
	$text = str_replace('\n', "\n", $text);
	return $text;
}

function DoExit($result, $data) {
	$data['result'] = "FAIL";

	if($result) {
		$data['result'] = "OK";
	}

	$jsondata = safe_json_encode($data);
	// This header will cause issue when error message is printed and error text length > content lenght
	//header("Content-length: " . strlen($jsondata));
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

function webapi_getrecords($query, &$user, $callbackfn, $key='records', $keycount='reccount') {
	$result = NULL;
	$db = MysqliDb::getInstance();
	if($query != '')
		$result = $db->rawQuery($query);

	if(NULL == $result) {
		if($keycount != '')
			$user[$keycount] = 0; // zero records

		$user[$key] = array(); //empty array so that we don't need to check class of data when json decoded
		return false;
	}

	$num_rows = $db->count;
	if($keycount != '')
		$user[$keycount] = $num_rows;

	$i = 0;
	while ($i < $num_rows) {
		$row = $result[$i];
		if($callbackfn != '' && $callbackfn != null)
			$callbackfn($i, $row);

		$user[$key][$i] = $row;
		$i++;
	}

	return true;
}

