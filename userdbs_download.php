<?php

(php_sapi_name() === "cli") OR exit("Script access is only allowed from command line");

$db_name = 'userdbs';

include "/var/www/mariadb-config.php";

/*
  
// key moved to include file
 
$api_key = "...";
$personal_key = "...";

 */
include "/var/www/swrve_billionaire_key.php";

$url_userdbs_json = "https://dashboard.swrve.com/api/1/userdbs.json";

exec("rm -r {$db_name}-*"); // cleanup old download

$content = exec("curl -G -k \"$url_userdbs_json?api_key=$api_key&personal_key=$personal_key\"");
$json = json_decode($content);

$dir = "{$db_name}-".$json->date;
if (!is_dir($dir)) {
    mkdir($dir);
}

$pdo = new PDO("mysql:host=$db_host;dbname=swrve_log", $db_user, $db_pass);

function download_file($object) {
    global $pdo;
    
    foreach ($object as $value) {
        if (is_object($value) || is_array($value)) {
            download_file($value);
        } else if (is_string($value)) {
            if (strpos($value, "https://") !== FALSE) {
                $pdo->exec("INSERT IGNORE INTO download_log SET download_url='$value', create_date=NOW()");
                $temp = explode("/", $value);
                $filename = array_pop($temp);
                
                $output = array();
                $result = system("wget --no-check-certificate --verbose "
                        . "--output-document=./{$GLOBALS['dir']}/$filename "
                        . "\"$value?api_key={$GLOBALS['api_key']}&personal_key={$GLOBALS['personal_key']}\"", $output);
                var_dump($result);
                var_dump($output);
//                $result = str_replace("'", "''", implode("\n", $output));
                $result = str_replace("'", "''", $result);
                $pdo->exec("UPDATE download_log SET download_result='$result', update_date=NOW() WHERE download_url='$value'");
                
                $output = array();
                $result = exec("gunzip -k --verbose ./{$GLOBALS['dir']}/$filename", $output);
//                $result = str_replace("'", "''", implode("\n", $output));
                $result = str_replace("'", "''", $result);
                $pdo->exec("UPDATE download_log SET gunzip_result='$result', update_date=NOW() WHERE download_url='$value'");
                die();
            }
        }
    }
}

download_file($json);

