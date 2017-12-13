<?php

(php_sapi_name() === "cli") OR exit("Script access is only allowed from command line");

include 'config.php';
load_config();

$current_dir = dirname(__FILE__);

include "$current_dir/../postgres-config.php";

if (isset($argv[2])) {
    $date = $argv[2];
} else {
    $date = date('Y-m-d');
}

exec("aws s3 ls s3://swrveexternal-alegrium/app-$swrve_app_id/$date", $output);

foreach ($output as $row) {
    $arr = explode($date, $row);

    $tableName = "events_{$swrve_app_id}";
    $tableLogName = "events_{$swrve_app_id}_log";
    $filename = $date.$arr[2];
    exec("psql --host=$rhost --port=$rport --username=$ruser --no-password --echo-all $rdatabase  -c \"select * from $tableLogName where filename = '$filename';\"", $out_select);
    
    if (strpos(implode("\n", $out_select), "(0 rows)") !== false) {
        echo "$filename not found in $tableLogName".PHP_EOL;
    } else {
        echo "$filename found in $tableLogName".PHP_EOL;
    }
}
