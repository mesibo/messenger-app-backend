<?php

require_once('mesiboconfig.php');

$err = false;
if(!isset($apptoken))
	$err = true;

if ($err) {
	echo "ERROR: Please define your api key. If you don't have one then signup at https://mesibo.com to get one";
	exit();	
}

/********************************************************************************
 This File contains all functions necessary for sending an API to perform the available
 Mesibo operations as mentioned in the documentation. 
 
 IMPORTANT: All functions are dependent on the a base API URL 
            http://api.tringme.com/api.php?  which is invoked to perform the operation.
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

