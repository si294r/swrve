<?php

(php_sapi_name() === "cli") OR exit("Script access is only allowed from command line");

include 'config.php';
load_config();

include "/var/www/$file_key_php";

$url_userdbs_json = "https://dashboard.swrve.com/api/1/userdbs.json";

getjson:
$content = exec("curl -G -k \"$url_userdbs_json?api_key=$api_key&personal_key=$personal_key\"");
$json = json_decode($content);

if (!is_object($json)) {
    goto getjson;
} else {
    exec("rm -r /var/www/html/swrve/{$db_name}-*"); // cleanup old download
    exec("s3cmd --recursive del s3://user-db/$folder_s3");
}

$dir = "/var/www/html/swrve/{$db_name}-" . $json->date;
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
                
                redownload:
                exec("wget --no-check-certificate --verbose "
                        . "--output-document={$GLOBALS['dir']}/$filename "
                        . "\"$value?api_key={$GLOBALS['api_key']}&personal_key={$GLOBALS['personal_key']}\"");
                
                exec("gunzip -k --verbose {$GLOBALS['dir']}/$filename");
                
                if (!is_file(str_replace(".csv.gz", ".csv", "{$GLOBALS['dir']}/$filename"))) {
                    goto redownload;
                }
                
                exec("s3cmd put {$GLOBALS['dir']}/$filename s3://user-db/$folder_s3/$filename");
            }
        }
    }
}

download_file($json);

