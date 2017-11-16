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
    exec("find /var/www/html/swrve -path */{$GLOBALS['db_name']}-*.csv", $output); // find all csv
    asort($output);
    $output = array_values($output);
    return $output;
}

function get_file_sql() {
    exec("find /var/www/html/swrve -path */{$GLOBALS['db_name']}-*mysql.sql", $output); // find all csv
    return $output[0];
}

function get_file_psql() {
    exec("find /var/www/html/swrve -path */{$GLOBALS['db_name']}-*redshift.sql", $output); // find all csv
    return $output[0];
}

/*
 * Configuration
 */

include 'config.php';

include "/var/www/mariadb-config.php";

include "/var/www/redshift-config2.php";

load_config();


// DROP TABLE, CREATE TABLE SCRIPT REDSHIFT
$file_psql = get_file_psql();
$content = file_get_contents($file_psql);
$content = str_replace("DOUBLE", "DOUBLE PRECISION", $content);
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

// 3. IMPORT CSV
$list_filename = get_list_filename();
//print_r($list_filename);
$text = "";
foreach ($list_filename as $filename) {
    $table_name = get_table_name($filename);

    $temp = explode("/", $filename);
    $filename = array_pop($temp);
    $pcmd = "psql --host=$rhost --port=$rport --username=$ruser --no-password --echo-all $rdatabase  -c \"COPY {$table_name}{$table_suffix} FROM 's3://user-db/{$folder_s3}/{$filename}.gz' CREDENTIALS 'aws_access_key_id={$aws_access_key_id};aws_secret_access_key={$aws_secret_access_key}' DELIMITER ',' IGNOREHEADER 1 ACCEPTINVCHARS ESCAPE GZIP ;\"";
    $output = array();
    exec($pcmd, $output);
    echo implode("\n", $output) . "\n\n";

    $text = $text . str_replace("\"", "", implode("\n", $output) . "\n\n\n\n");
    
//    break; // execute one file csv
}

$ses_cmd = "aws ses send-email --from $email_from --to $email_to --subject \"$email_subject\"  --text \"$text\"";
exec($ses_cmd);

