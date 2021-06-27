<?php
	
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
		LogError("$type: $message in $file on line $line");

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
