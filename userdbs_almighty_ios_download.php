<?php

(php_sapi_name() === "cli") OR exit("Script access is only allowed from command line");

$db_name = 'almighty_userdbs_ios';

/*

  // key moved to include file

  $api_key = "...";
  $personal_key = "...";

 */
include "/var/www/swrve_almighty_ios_key.php";

$url_userdbs_json = "https://dashboard.swrve.com/api/1/userdbs.json";

getjson:
$content = exec("curl -G -k \"$url_userdbs_json?api_key=$api_key&personal_key=$personal_key\"");
$json = json_decode($content);

if (!is_object($json)) {
    goto getjson;
} else {
    exec("rm -r /var/www/html/swrve/{$db_name}-*"); // cleanup old download
    exec("s3cmd --recursive del s3://user-db/almighty_ios");
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
                
                exec("s3cmd put {$GLOBALS['dir']}/$filename s3://user-db/almighty_ios/$filename");
            }
        }
    }
}

download_file($json);

