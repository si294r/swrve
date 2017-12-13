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
        echo "downloading...";
        exec("aws s3 cp s3://swrveexternal-alegrium/app-$swrve_app_id/$filename $current_dir/$filename");
        echo "extract...";
        exec("gunzip -f $current_dir/$filename");
//        echo "truncate...";
//        exec("psql --host=$rhost --port=$rport --username=$ruser --no-password --echo-all $rdatabase  -c \"TRUNCATE TABLE temp_json; \"", $out_import);
//        echo implode("\n", $out_import) . "\n\n";
//        echo "copy tempjson...";
        $filename = str_replace(".gz", "", "$current_dir/$filename");
//        exec("psql --host=$rhost --port=$rport --username=$ruser --no-password --echo-all $rdatabase  -c \"\\COPY temp_json FROM '$filename'; \"", $out_import);
//        echo implode("\n", $out_import) . "\n\n";
//        echo "insert...";
        exec("psql --host=$rhost --port=$rport --username=$ruser --no-password --echo-all $rdatabase  -c \"
create temporary table temp_json (values text) on commit drop;
\\COPY temp_json FROM '$filename';
    
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
        echo implode("\n", $out_import) . "\n\n";
//        echo "done".PHP_EOL;
    } else {
        echo "$filename found in $tableLogName".PHP_EOL;
    }
    die;
}
