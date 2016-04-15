<?php

(php_sapi_name() === "cli") OR exit("Script access is only allowed from command line");

$db_name = 'userdbs';

/*
  
// key moved to include file
 
$api_key = "...";
$personal_key = "...";

 */
include "/var/www/swrve_{$db_name}_key.php";

$url_userdbs_json = "https://dashboard.swrve.com/api/1/userdbs.json";

exec("rm -r {$db_name}-*"); // cleanup old download

$content = exec("curl -G -k \"$url_userdbs_json?api_key=$api_key&personal_key=$personal_key\"");
$json = json_decode($content);

$dir = "{$db_name}-".$json->date;
if (!is_dir($dir)) {
    mkdir($dir);
}

function download_file($object) {
    foreach ($object as $value) {
        if (is_object($value) || is_array($value)) {
            download_file($value);
        } else if (is_string($value)) {
            if (strpos($value, "https://") !== FALSE) {
                $temp = explode("/", $value);
                $filename = array_pop($temp);

                exec("wget --no-check-certificate --output-document=./{$GLOBALS['dir']}/$filename "
                        . "\"$value?api_key={$GLOBALS['api_key']}&personal_key={$GLOBALS['personal_key']}\"");
                exec("gunzip -k ./{$GLOBALS['dir']}/$filename");
            }
        }
    }
}

download_file($json);

