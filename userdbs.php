<?php

/*
  
// key moved to include file
 
$api_key = "...";
$personal_key = "...";

 */
include "/var/www/swrve_billionaire_key.php";

$url_userdbs_json = "https://dashboard.swrve.com/api/1/userdbs.json";

$content = exec("curl -G -k \"$url_userdbs_json?api_key=$api_key&personal_key=$personal_key\"");
$json = json_decode($content);

$dir = $json->date;
if (!is_dir($dir)) {
    mkdir($dir);
}

function download_file($object) {
    foreach ($object as $value) {
        if (is_object($value) || is_array($value)) {
            download_file($value);
        } else if (is_string($value)) {
            if (strpos($value, "https://") !== FALSE) {
                exec("wget --no-check-certificate --directory-prefix=./{$GLOBALS['dir']} "
                        . "\"$value?api_key={$GLOBALS['api_key']}&personal_key={$GLOBALS['personal_key']}\"");
            }
        }
    }
}

download_file($json);

