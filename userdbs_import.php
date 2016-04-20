<?php

(php_sapi_name() === "cli") OR exit("Script access is only allowed from command line");

/*
 * Function Definition
 */

function get_table_name($filename) {
    $pos = strpos($filename, "/");
    $pos = strpos($filename, "/", $pos + 1);
    $pos = strpos($filename, "_", $pos + 1);
    $pos = strpos($filename, "_", $pos + 1);
    $pos = strpos($filename, "_", $pos + 1);
    $pos_dot = strpos($filename, ".", $pos);
    $table_name = substr($filename, $pos + 1, $pos_dot - $pos - 1);
    return $table_name;
}

function get_list_filename() {
    exec("find /var/www/html/swrve -path *{$GLOBALS['db_name']}-*.csv", $output); // find all csv
    asort($output);
    $output = array_values($output);
    return $output;
}

function get_file_sql() {
    exec("find /var/www/html/swrve -path *{$GLOBALS['db_name']}-*mysql.sql", $output); // find all csv
    return $output[0];
}

/*
 * Configuration, moved to mariadb-config.php
 */

//$db_host = 'mariadb_server';
//$db_user = 'root';
//$db_pass = 'password';
$db_name = 'userdbs';

include "/var/www/mariadb-config.php";

/*
 * Main Script
 */

// 1. CLEAN LOG, DROP AND CREATE DATABASE
$output = array();
exec("mysql -h $db_host -u $db_user --password=$db_pass -vve \"PURGE BINARY LOGS BEFORE NOW(); DROP DATABASE IF EXISTS $db_name; CREATE DATABASE $db_name;\"", $output);
echo implode("\n", $output)."\n\n";

// 2. EXECUTE CREATE TABLE SCRIPT
$file_sql = get_file_sql();
$content = file_get_contents($file_sql);
$content = "SET default_storage_engine=MYISAM;\n\n" . $content;
$content = substr($content, 0, strpos($content, "ALTER TABLE"));
$file_new_sql = str_replace("mysql.sql", "mysql_new.sql", $file_sql);
file_put_contents($file_new_sql, $content);
$output = array();
exec("mysql -h $db_host -u $db_user --password=$db_pass -vv $db_name < " . $file_new_sql, $output);
echo implode("\n", $output)."\n\n";

// 3. IMPORT CSV
$list_filename = get_list_filename();
//print_r($list_filename);
foreach ($list_filename as $filename) {
    $table_name = get_table_name($filename);

    $cmd = <<<EOD
mysql --local-infile -h $db_host -u $db_user --password=$db_pass -vve "load data local infile '$filename' 
into table $db_name.$table_name fields terminated by ',' enclosed by '\"' lines terminated by '\n' ignore 1 rows"
EOD;
    $output = array();
    exec($cmd, $output);
    echo implode("\n", $output)."\n\n";
    
//    break; // execute one file csv
}


