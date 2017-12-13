<?php

(php_sapi_name() === "cli") OR exit("Script access is only allowed from command line");

include 'config.php';
load_config();

$current_dir = dirname(__FILE__);

include "$current_dir/../postgres-config.php";

if (isset($argv[2])) {
//    $date = $argv[2];
    $startdate = $argv[2];
    if (isset($argv[3])) {
        $enddate = $argv[3];
        if ($enddate < $startdate) {
            $enddate = $startdate;
        }
    } else {
        $enddate = date('Y-m-d');
    }
} else {
//    $date = date('Y-m-d');
    $startdate = date('Y-m-d');
    $enddate = date('Y-m-d');
}

$obj_date = DateTime::createFromFormat('Y-m-d', $startdate);

while (true) {

    $date = $obj_date->format('Y-m-d');
    
    exec("aws s3 ls s3://swrveexternal-alegrium/app-$swrve_app_id/$date", $output);

    foreach ($output as $row) {
//        $arr = explode($date, $row);

        $tableName = "events_{$swrve_app_id}";
        $tableLogName = "events_{$swrve_app_id}_log";
        
        $re = '/ '.$date.'.*/';
        preg_match_all($re, $row, $matches, PREG_SET_ORDER, 0);
        $filename = trim($matches[0][0]);
        
        exec("psql --host=$rhost --port=$rport --username=$ruser --no-password --echo-all $rdatabase  -c \"select * from $tableLogName where filename = '$filename';\"", $out_select);

        if (strpos(implode("\n", $out_select), "(0 rows)") !== false) {
            echo "$filename not found in $tableLogName" . PHP_EOL;
            echo "downloading...";
            exec("aws s3 cp s3://swrveexternal-alegrium/app-$swrve_app_id/$filename $current_dir/$filename");
            echo "extract...";
            exec("gunzip -f $current_dir/$filename");
            echo "truncate...";
            exec("psql --host=$rhost --port=$rport --username=$ruser --no-password --echo-all $rdatabase  -c \"TRUNCATE TABLE temp_json; \"", $out_import);
            echo "copy...";
            $logfilename = str_replace(".gz", "", "$current_dir/$filename");
            exec("psql --host=$rhost --port=$rport --username=$ruser --no-password --echo-all $rdatabase  -c \"\\COPY temp_json FROM '$logfilename'; \"", $out_import);
            echo "insert...";
            exec("psql --host=$rhost --port=$rport --username=$ruser --no-password --echo-all $rdatabase  -c \"
insert into events_30088
select values->>'app_version' as app_version,
       values->>'type' as event_type,
       cast(values->>'time' as bigint) as event_time,
       cast(values->>'client_time' as bigint) as client_time,
       values->>'user' as event_user,
       values->>'parameters' as parameters,
       values->>'payload' as payload
from   
(
    select values::json as values from   temp_json
) a;\"", $out_import);
            echo PHP_EOL . implode(PHP_EOL, $out_import) . PHP_EOL . PHP_EOL;
            $out_import = [];

            exec("psql --host=$rhost --port=$rport --username=$ruser --no-password --echo-all $rdatabase  -c \"Insert into $tableLogName (filename, status) VALUES ('$filename', 'done');\"");
        } else {
            echo "$filename found in $tableLogName" . PHP_EOL;
        }
//    die;
    }
    
    //cleanup
    exec("rm $current_dir/*.log");
    
    if ($obj_date->format('Y-m-d') == $enddate) {
        break;
    } else {
        $obj_date->add(new DateInterval("P1D"));
    }
}