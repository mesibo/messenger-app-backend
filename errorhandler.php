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



	
	function handle_error($type, $message, $file, $line, $context) {

		switch($type) {
			case E_NOTICE:
				$type = "Notice";
				$color = "white";
				//return;
			break;
			case E_WARNING:
				$type = "Warning";
				$color = "yellow";
			break;
			case E_USER_ERROR:
				$type = "Error";
				$color = "red";
			break;
			default:
				$type = "other";
				$color = "green";
			break;
		}

		error_log("$type: $message in $file on line $line");
		log_error("$type: $message in $file on line $line");

		//print("<table border=1><tr><td bgcolor=$color>$type: $message in $file on line $line</tr></td></table>");
	}
 

function translateErrorReportingConstant2String()
{
 $current = error_reporting();
 $out = "";

  if (($current & E_ERROR          ) == E_ERROR          ){ $out .=" E_ERROR          | "; }
  if (($current & E_WARNING        ) == E_WARNING        ){ $out .=" E_WARNING        | "; }
  if (($current & E_PARSE          ) == E_PARSE          ){ $out .=" E_PARSE          | "; }
  if (($current & E_NOTICE        ) == E_NOTICE        ){ $out .=" E_NOTICE          | "; }
  if (($current & E_CORE_ERROR    ) == E_CORE_ERROR    ){ $out .=" E_CORE_ERROR      | "; }
  if (($current & E_CORE_WARNING  ) == E_CORE_WARNING  ){ $out .=" E_CORE_WARNING    | "; }
  if (($current & E_COMPILE_ERROR  ) == E_COMPILE_ERROR  ){ $out .=" E_COMPILE_ERROR  | "; }
  if (($current & E_COMPILE_WARNING) == E_COMPILE_WARNING){ $out .=" E_COMPILE_WARNING | "; }
  if (($current & E_USER_ERROR    ) == E_USER_ERROR    ){ $out .=" E_USER_ERROR      | "; }
  if (($current & E_USER_WARNING  ) == E_USER_WARNING  ){ $out .=" E_USER_WARNING    | "; }
  if (($current & E_USER_NOTICE    ) == E_USER_NOTICE    ){ $out .=" E_USER_NOTICE    | "; }
  if (($current & E_ALL            ) == E_ALL            ){ $out .=" E_ALL            | "; }
 return $out;
 }
