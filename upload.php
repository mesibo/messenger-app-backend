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



//http://www.openjs.com/articles/ajax/ajax_file_upload/response_data.php
/**
 * A function for easily uploading files. This function will automatically generate a new 
 *        file name so that files are not overwritten.
 * Taken From: http://www.bin-co.com/php/scripts/upload_function/
 * Arguments:    $file_id- The name of the input field contianing the file.
 *                $folder    - The folder to which the file should be uploaded to - it must be writable. OPTIONAL
 *                $types    - A list of comma(,) seperated extensions that can be uploaded. If it is empty, anything goes OPTIONAL
 * Returns  : This is somewhat complicated - this function returns an array with two values...
 *                The first element is randomly generated filename to which the file was uploaded to.
 *                The second element is the status - if the upload failed, it will be 'Error : Cannot upload the file 'name.txt'.' or something like that
 */

function upload_fileinfo($file_id, $types, &$ext, &$filesize, &$error) {
	if(!$_FILES[$file_id]['name']) {
		return false;
	}

	if(!$_FILES[$file_id]['size']) { //Check if the file is made
		$error = "Bad file size";
		return false;
	}

	$filesize = $_FILES[$file_id]['size'];	
	
	$ext_arr = explode(".",basename($_FILES[$file_id]['name']));
	$ext = strtolower($ext_arr[count($ext_arr)-1]); //Get the last extension
	
	if("" != $types) {
		$all_types = explode(",",strtolower($types));
		if(!in_array($ext,$all_types)) {
			$error = "Bad file type";
			return false;
		}
	}

	return $_FILES[$file_id]['name'];
}

function upload($file_id, $folder, $file_name="", $types="", &$ext="") {
	if(!isset($_FILES[$file_id])) {
		return false;
	}

	if(!isset($_FILES[$file_id]['name'])) {
		return false;
	}

	if(!$_FILES[$file_id]['size']) { //Check if the file is made
	//	return false;
	}

	//Get file extension
	if("" == $ext) {
		$ext_arr = explode(".", basename($_FILES[$file_id]['name']));
		$ext = strtolower($ext_arr[count($ext_arr)-1]); //Get the last extension
	}

	//Not really uniqe - but for all practical reasons, it is
	if("" == $file_name) {
		$file_name = time().'-'.substr(md5(uniqid(rand(),1)),0,16);
	}

	if("" != $types) {
		$all_types = explode(",",strtolower($types));
		if(!in_array($ext,$all_types)) {
			return false;
		}
	}

	//Where the file must be uploaded to
	if($folder) $folder .= '/';//Add a '/' at the end of the folder
	if(false === strpos($file_name, ".$ext"))
		$file_name .= ".$ext";

	$uploadfile = $folder . $file_name;

	$result = '';
	//check upload_max_filesize in php.ini if move_uploaded_file fails
	//Move the file from the stored location to the new location
	if (!move_uploaded_file($_FILES[$file_id]['tmp_name'], $uploadfile)) {
		return false;
	} 

	chmod($uploadfile,0777);//Make it universally writable.
	return $file_name;
}


