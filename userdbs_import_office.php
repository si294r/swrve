<?php

(php_sapi_name() === "cli") OR exit("Script access is only allowed from command line");

/*
 * Function Definition
 */

function get_table_name($filename) {
    $pos = strrpos($filename, "/");
//    $pos = strpos($filename, "/", $pos + 1);
    $pos = strpos($filename, "_", $pos + 1);
    $pos = strpos($filename, "_", $pos + 1);
    $pos = strpos($filename, "_", $pos + 1);
    $pos_dot = strpos($filename, ".", $pos);
    $table_name = substr($filename, $pos + 1, $pos_dot - $pos - 1);
    return $table_name;
}

function get_list_filename() {
    exec("find /home/alegrium/www/swrve -path */{$GLOBALS['db_name']}-*.csv", $output); // find all csv
    asort($output);
    $output = array_values($output);
    return $output;
}

function get_file_psql() {
    exec("find /home/alegrium/www/swrve -path */{$GLOBALS['db_name']}-*redshift.sql", $output); // find all csv
    return $output[0];
}

/*
 * Configuration
 */

include 'config.php';

include "/home/alegrium/www/postgres-config.php";

load_config();


// DROP TABLE, CREATE TABLE SCRIPT REDSHIFT
$file_psql = get_file_psql();
$content = file_get_contents($file_psql);
$content = str_replace("DOUBLE", "DOUBLE PRECISION", $content);
$content = str_replace("distkey", "", $content);
$arr_content = explode("\n", $content);
foreach ($arr_content as $k => $value) {
    if (strpos($value, "CREATE TABLE") !== FALSE) {
        $temp = str_replace(" (", "$table_suffix (", $value);
        $temp = str_replace(" (", ";", str_replace("CREATE TABLE", "DROP TABLE IF EXISTS", $temp)) . "\n" . $temp;
        $arr_content[$k] = $temp;
    }
}
$file_new_psql = str_replace("redshift.sql", "redshift_new.sql", $file_psql);
file_put_contents($file_new_psql, implode("\n", $arr_content));
$output = array();
exec("psql --host=$rhost --port=$rport --username=$ruser --no-password --echo-all $rdatabase < " . $file_new_psql, $output);
echo implode("\n", $output) . "\n\n";

//die;

// 3. IMPORT CSV
$list_filename = get_list_filename();
//print_r($list_filename);
$text = "";
foreach ($list_filename as $filename) {
    $table_name = get_table_name($filename);

    $csv_content = file_get_contents($filename);
    
    $arr_csv = explode(PHP_EOL, $csv_content);
    $re = '/(?<=^|,)(((?<=\\\\),|[^,|])*)(?:$|,)/';
    foreach ($arr_csv as $k_row=>$csv_row) {
        $matches = [];
        preg_match_all($re, $csv_row, $matches, PREG_SET_ORDER, 0);
        foreach ($matches as $k=>$value) {
            if (strpos($value[1], "\\,") !== FALSE || strpos($value[1], "\"") !== FALSE) {
                $value[1] = str_replace(["\\,", "\""], [",", "\\\""], $value[1]);
            }
            $matches[$k] = $value[1];
        }
        $arr_csv[$k_row] = implode(",", $matches);
    }
    $csv_content = implode(PHP_EOL, $arr_csv);

//    $csv_content = preg_replace('/,([^,]+)\\\\,([^,]+),/', ',"$1,$2",', $csv_content);
//    $csv_content = preg_replace('/,([^,]+)\\\\,([^,]+),/', ',"$1,$2",', $csv_content);
    file_put_contents($filename, $csv_content);
    
//    $temp = explode("/", $filename);
//    $filename = array_pop($temp);
//    $pcmd = "psql --host=$rhost --port=$rport --username=$ruser --no-password --echo-all $rdatabase  -c \"COPY {$table_name}{$table_suffix} FROM 's3://user-db/{$folder_s3}/{$filename}.gz' CREDENTIALS 'aws_access_key_id={$aws_access_key_id};aws_secret_access_key={$aws_secret_access_key}' DELIMITER ',' IGNOREHEADER 1 MAXERROR 100 ESCAPE GZIP ;\"";
    $pcmd = "psql --host=$rhost --port=$rport --username=$ruser --no-password --echo-all $rdatabase  -c \"\\COPY {$table_name}{$table_suffix} FROM '$filename' DELIMITER ',' NULL '\\N' QUOTE '\\\"' CSV HEADER ;\"";
    $output = array();
    exec($pcmd, $output);
    echo implode("\n", $output) . "\n\n";

    $text = $text . str_replace("\"", "", implode("\n", $output) . "\n\n\n\n");
    
//    break; // execute one file csv
}

//$ses_cmd = "aws ses send-email --from $email_from --to $email_to --subject \"$email_subject\"  --text \"$text\"";
//exec($ses_cmd);

