<?php

(php_sapi_name() === "cli") OR exit("Script access is only allowed from command line");

include 'config.php';
load_config();

include "/home/alegrium/www/$file_key_php";

$url_userdbs_json = "https://dashboard.swrve.com/api/1/userdbs.json";

getjson:
$content = exec("curl -G -k \"$url_userdbs_json?api_key=$api_key&personal_key=$personal_key\"");
$json = json_decode($content);

if (!is_object($json)) {
    goto getjson;
} else {
    exec("rm -r /home/alegrium/www/swrve/{$db_name}-*"); // cleanup old download
    exec("aws s3 rm --recursive s3://user-db/office/$folder_s3");
}

$dir = "/home/alegrium/www/swrve/{$db_name}-" . $json->date;
if (!is_dir($dir)) {
    mkdir($dir);
}

function download_file($object) {
    
    global $folder_s3;
    
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
                
            }
        }
    }
}

download_file($json);

exec("aws s3 cp --recursive {$GLOBALS['dir']}/ s3://user-db/office/$folder_s3/");

