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




require_once('mesiboconfig.php');

$err = false;
if(!isset($apikey))
	$err = true;

if ($err) {
	echo "ERROR: Please define your api key. If you don't have one then signup at https://mesibo.com to get one";
	exit();	
}

/********************************************************************************
 This File contains all functions necessary for sending an API to perform the available
 Mesibo operations as mentioned in the documentation. 
 
 IMPORTANT: All functions are dependent on the a base API URL 
            http://api.mesibo.com/api.php?  which is invoked to perform the operation.
*********************************************************************************/
$host = $_SERVER['HTTP_HOST'];
$mesibobaseurl  = "https://api.mesibo.com/api.php?";

/********************************************************************************
Descripton: Performs the requested API operation 
                                                 
Parameters: $parameters - Parameters specific to the operation
		    $result- Contains the response information. 

Return Values: True- If operation is successful.        
               False- If operation failed.                                                             
*********************************************************************************/
function MesiboAPI($parameters) {
	global $apikey, $apilog, $file, $apptoken;
	//$parameters['key']=$apikey;
	$parameters['token']=$apptoken;
	$apiuri=CreateAPIURL($parameters);
	$response = GetAPIResponse($apiuri);
	return MesiboParseResponse($response);
}

/********************************************************************************
Descripton: Converts the response into function usable format for programming convenience. 
                                                                                                
Parameters: $response - The response to be parsed.                                 
		    $result- Contains the parsed response.    

Return Values: True- If the response was a success.    
               False- If the response was a failure.   
*********************************************************************************/
function MesiboParseResponse($response) {
	$result = json_decode($response, true);
	if(is_null($result)) 
		return false;
	return $result;
}

/********************************************************************************
Descripton: Reads the response from Mesibo URL for the request made.
                                                                                                
Parameters: $url - URL of the API being invoked.                                 

Return Values: Returns the response
*********************************************************************************/
function GetAPIResponse($url) {
    
	$opts = array(
		'http'=>array(
		'method'=>"GET",
		'header'=>"Accept-language: en\r\n" .
				"Cookie: foo=bar\r\n"
					)
		);

	$context = stream_context_create($opts);
	$response = 'FAILED';
	$sock=fopen($url, 'r', false, $context);
	if ($sock) {
		$response='';
		while (!feof($sock))
			$response.=fgets($sock, 4096);

		fclose($sock);
	}
	return $response;
}

/********************************************************************************
Descripton: Creates the request URL for the API being invoked with all the parameters and signature
            appended to the URL as required. 
                                                                                                
Parameters: $params_array - Parameters                                 
		    $privatekey - The private key.      
                                                                                         
Return Values: The URL that is constructed.    
*********************************************************************************/
function CreateAPIURL($params_array) {
	global $mesibobaseurl;
	
	$uri = $mesibobaseurl;
	if (isset($params_array['apiurl']))
		$uri = $params_array['apiurl'];
		
	foreach($params_array as $key=>$val) {   
		$uri .= "$key=" . urlencode($val) . '&';    
	}

	$uri .= "sig=none";        
	return $uri;
}

