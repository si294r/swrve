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
 * Configuration, moved to mariadb-config.php
 */

//$db_host = 'mariadb_server';
//$db_user = 'root';
//$db_pass = 'password';
$db_name = 'almighty_userdbs_ios';

include "/var/www/mariadb-config.php";

include "/var/www/redshift-config2.php";

/*
 * Main Script
 */
//goto testing;
// 1. CLEAN LOG, DROP AND CREATE DATABASE
//$output = array();
//exec("mysql -h $db_host -u $db_user --password=$db_pass -vve \"PURGE BINARY LOGS BEFORE NOW(); DROP DATABASE IF EXISTS $db_name; CREATE DATABASE $db_name;\"", $output);
//echo implode("\n", $output)."\n\n";

// 2.1. EXECUTE CREATE TABLE SCRIPT
//$file_sql = get_file_sql();
//$content = file_get_contents($file_sql);
//$content = "SET default_storage_engine=MYISAM;\n\n" . $content;
//$content = substr($content, 0, strpos($content, "ALTER TABLE"));
//$file_new_sql = str_replace("mysql.sql", "mysql_new.sql", $file_sql);
//file_put_contents($file_new_sql, $content);
//$output = array();
//exec("mysql -h $db_host -u $db_user --password=$db_pass -vv $db_name < " . $file_new_sql, $output);
//echo implode("\n", $output)."\n\n";

//testing:
// 2.2. DROP TABLE, CREATE TABLE SCRIPT REDSHIFT
$file_psql = get_file_psql();
$content = file_get_contents($file_psql);
$content = str_replace("DOUBLE", "DOUBLE PRECISION", $content);
$arr_content = explode("\n", $content);
foreach ($arr_content as $k => $value) {
    if (strpos($value, "CREATE TABLE") !== FALSE) {
        $temp = str_replace(" (", "_almighty_ios (", $value);
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

//    goto psql;
    // disable mysql - 20150615
//    $cmd = <<<EOD
//mysql --local-infile -h $db_host -u $db_user --password=$db_pass -vve "load data local infile '$filename' 
//into table $db_name.$table_name fields terminated by ',' enclosed by '\"' lines terminated by '\n' ignore 1 rows"
//EOD;
//    $output = array();
//    exec($cmd, $output);
//    echo implode("\n", $output)."\n\n";
    
//    psql:
    $temp = explode("/", $filename);
    $filename = array_pop($temp);
    $pcmd = "psql --host=$rhost --port=$rport --username=$ruser --no-password --echo-all $rdatabase  -c \"COPY {$table_name}_almighty_ios FROM 's3://user-db/almighty_ios/{$filename}.gz' CREDENTIALS 'aws_access_key_id={$aws_access_key_id};aws_secret_access_key={$aws_secret_access_key}' DELIMITER ',' IGNOREHEADER 1 ESCAPE GZIP;\"";
    $output = array();
    exec($pcmd, $output);
    echo implode("\n", $output) . "\n\n";
    
    $text = $text . str_replace("\"", "", implode("\n", $output) . "\n\n\n\n");
    
//    break; // execute one file csv
}

$ses_cmd = "aws ses send-email --from heru@alegrium.com --to heru@alegrium.com --subject \"Swrve Almighty Result Import\"  --text \"$text\"";
exec($ses_cmd);

