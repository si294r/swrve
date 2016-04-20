<?php

(php_sapi_name() === "cli") OR exit("Script access is only allowed from command line");

$db_name = 'userdbs_ios';

/*

  // key moved to include file

  $api_key = "...";
  $personal_key = "...";

 */
include "/var/www/swrve_billionaire_ios_key.php";

$url_userdbs_json = "https://dashboard.swrve.com/api/1/userdbs.json";

exec("rm -r /var/www/html/swrve/{$db_name}-*"); // cleanup old download

$content = exec("curl -G -k \"$url_userdbs_json?api_key=$api_key&personal_key=$personal_key\"");
$json = json_decode($content);

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

                exec("gunzip -k {$GLOBALS['dir']}/$filename");

                if (!is_file(str_replace(".csv.gz", ".csv", "{$GLOBALS['dir']}/$filename"))) {
                    goto redownload;
                }
            }
        }
    }
}

download_file($json);

