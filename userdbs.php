<?php

$api_key = "t8iA8dbYYDbnGBSLWoU";
$personal_key = "0fT8OKR0aHcscjS48zAU";

$content = exec("curl -G -k \"https://dashboard.swrve.com/api/1/userdbs.json?api_key=$api_key&personal_key=$personal_key\"");
$json = json_decode($content);

//var_dump($json->data_files);
//var_dump($json->schemas);
//var_dump($json->date);

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

//foreach ($json as $value) {
//    var_dump($value);
//}

/*
 * curl -G -k https://dashboard.swrve.com/api/1/userdbs.json?api_key=t8iA8dbYYDbnGBSLWoU&personal_key=0fT8OKR0aHcscjS48zAU 
 * wget --no-check-certificate --directory-prefix=./$dir "https://dashboard.swrve.com/api/1/userdbs/downloads/2016-04-07/all-users_02618_mysql.sql?api_key=t8iA8dbYYDbnGBSLWoU&personal_key=0fT8OKR0aHcscjS48zAU"
 * 
 * 
 */