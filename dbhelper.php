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

function dbhelper_add($table, $column, $val, $conds) {
	if(0 == $val) return true;

	$db = MysqliDb::getInstance();
	if(!$db) return false;

	foreach($conds as $cond) {
		$db->where($cond['c'], $cond['v']);
	}

	$e = Array();
	if($val > 0)
		$e[$column] = $db->inc($val);
	else
		$e[$column] = $db->dec($val);

	$db->update($table, $e); 
}


function dbhelper_delete($table, $column, $val) {
	$db = MysqliDb::getInstance();
	$db->where($column, $val);
	$db->delete($table);
}

function dbhelper_getrow($query) {
	$db = MysqliDb::getInstance();
	if(!$db) return false;
    
	return $db->rawQueryOne($query);
}

function dbhelper_getvalue($query, $column) {
	$db = MysqliDb::getInstance();
	if(!$db) return false;
    
	$r = $db->rawQueryOne($query);
	if(!$r) return false;
	return $r[$column];
}

function dbhelper_getvalue_p($table, $conds, $column) {
	$db = MysqliDb::getInstance();
	if(!$db) return false;

	foreach($conds as $cond) {
		$db->where($cond['c'], $cond['v'], $op);
	}
	return $db->getValue($table, $column);
}

function dbhelper_setvalue($table, $conds, $column, $value) {
	$db = MysqliDb::getInstance();
	if(!$db) return false;

	foreach($conds as $cond) {
		$db->where($cond['c'], $cond['v']);
	}

	$e = Array();
	$e[$column] = $value;

	return $db->update($table, $e);
}

function dbhelper_count($table, $conds) {
	$count = dbhelper_getvalue($table, $conds, 'count(1)');
	if(!$count) return 0;
	return $count;
}

function dbhelper_insert($table, $values, $duplicates) {
	$db = MysqliDb::getInstance();
	if(!$db) return false;
	
	if($duplicates)
		$db->onDuplicate($duplicates, null);
	$db->insert($table, $values);
	return ($db->count !=  1); //true for insert
}

function dbhelper_rawquery($query) {
	$db = MysqliDb::getInstance();
	if(!$db) return false;
	
	$db->rawQuery($query);
	return $db->count;
}
