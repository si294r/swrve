<?php

/*
 * Function Definition
 */

function get_table_name($filename) {
    $pos = strpos($filename, "_");
    $pos = strpos($filename, "_", $pos + 1);
    $pos = strpos($filename, "_", $pos + 1);
    $pos_dot = strpos($filename, ".", $pos);
    $table_name = substr($filename, $pos + 1, $pos_dot - $pos - 1);
    return $table_name;
}

function get_list_filename() {
    exec("find . -path *userdbs-*.csv", $output); // find all csv
    asort($output);
    $output = array_values($output);
    return $output;
}

/*
 * Configuration
 */

$db_host = 'mariadb_server';
$db_user = 'root';
$db_pass = 'password';
$db_name = 'swrve';

/*
 * Main Script
 */

//load data local infile './userdbs-2016-04-12/all-users_02618_2016-04-12_abtest_exposure.1_0.1.csv' into table swrve.abtest_exposure fields terminated by ',' enclosed by '\"' lines terminated by '\n' ignore 1 rows

$pdo = new PDO("mysql:dbname=$db_name;host=$db_host", $db_user, $db_pass);
$pdo->exec("SET FOREIGN_KEY_CHECKS=0");
$pdo->exec("PURGE BINARY LOGS BEFORE NOW()");

$list_filename = get_list_filename();
print_r($list_filename);
foreach ($list_filename as $filename) {
    $table_name = get_table_name($filename);

    $sql = "load data infile '$filename' into table $db_name.$table_name "
            . "fields terminated by ',' enclosed by '\"' lines "
            . "terminated by '\\n' ignore 1 rows";
//    file_put_contents("load_data_$table_name", $sql);
//    exec("mysql --local-infile -h $db_host -u $db_user --password=$db_pass < load_data_$table_name ");

    echo $sql;
    if ($pdo->exec($sql)) {
        
    } else {
        $error = $pdo->errorInfo()[2];
        $error = str_replace("'", "''", $error);
        $error = str_replace("\\", "\\\\", $error);
        $pdo->exec("INSERT INTO error_message (table_name, message) VALUES "
                . "('$table_name', '$error')");
    }

//    $i = 0;
//    $handle = fopen($filename, "r");
//    while (($buffer = fgets($handle, 10 * 1024 * 1024)) !== false) {
//        if ($i == 0) {
//            $columns = explode(",", $buffer);
//            $update_columns = $columns;
//            foreach ($update_columns as $k => $v) {
//                $update_columns[$k] = $v . "=VALUES(" . $v . ")";
//            }
//        } else {
//            $buffer = str_replace(",", ",,", $buffer);
//            $buffer = str_replace("\,,", ",", $buffer);
//            $row = explode(",,", $buffer);
//            foreach ($row as $k => $v) {
//                if ($v == "\\N")
//                    $row[$k] = "NULL";
//                $row[$k] = str_replace("'", "''", $row[$k]);
//                $row[$k] = str_replace("\\", "\\\\", $row[$k]);
//            }
//            $sql = "INSERT INTO $table_name (" . implode(",", $columns) . ") "
//                    . " VALUES ('" . implode("','", $row) . "') "
//                    . " ON DUPLICATE KEY UPDATE " . implode(",", $update_columns);
//
//            if (($i % 100) == 0) {
//                echo $i . "=" . $row[0] . "\r\n";
//            }
//            if ($pdo->exec($sql)) {
//                
//            } else {
////                var_dump($pdo->errorInfo());
//                $error = $pdo->errorInfo()[2];
//                if (stripos($error, "column") > 0) {
//                    $alter_column = str_replace("Unknown column '", "", $error);
//                    $alter_column = str_replace("' in 'field list'", "", $alter_column);
//                    $sql = "ALTER TABLE  `$table_name` ADD  `$alter_column` VARCHAR( 100 ) NULL DEFAULT NULL ;";
//                    $pdo->exec($sql);
//                    break;
//                } else {
//                    $error = str_replace("'", "''", $error);
//                    $error = str_replace("\\", "\\\\", $error);
//                    $pdo->exec("INSERT INTO error_message (table_name, message) VALUES "
//                            . "('$table_name', '$error')");
//                }
//            }
////            break; // execute one line data
//        }
//        $i++;
//    }
//    if (!feof($handle)) {
//        echo "Error: unexpected fgets() fail\n";
//    }
//    fclose($handle);

    break; // execute one file csv
}

//var_dump($table_name);
//die;
//
//$content = file_get_contents("/var/www/html/swrve/userdbs-2016-04-12/all-users_02618_2016-04-12_abtest_exposure.1_0.1.csv");
//
//$array = explode("\n", $content);
//
//$columns = explode(",", $array[0]);
//$update_columns = $columns;
//foreach ($update_columns as $k => $v) {
//    $update_columns[$k] = $v . "=VALUES(" . $v . ")";
//}
//
//try {
//
//    for ($i = 1; $i < count($array); $i++) {
//        $array[$i] = str_replace(",", ",,", $array[$i]);
//        $array[$i] = str_replace("\,,", ",", $array[$i]);
//        $row = explode(",,", $array[$i]);
//        foreach ($row as $k => $v) {
//            if ($v == "\\N")
//                $row[$k] = "NULL";
//            $row[$k] = str_replace("'", "''", $row[$k]);
//            $row[$k] = str_replace("\\", "\\\\", $row[$k]);
//        }
//        $sql = "INSERT INTO $table_name (" . implode(",", $columns) . ") "
//                . " VALUES ('" . implode("','", $row) . "') "
//                . " ON DUPLICATE KEY UPDATE " . implode(",", $update_columns);
//
//        echo $i . "=" . $row[0] . "\r\n";
//        if ($pdo->exec($sql)) {
//            
//        } else {
////            var_dump($pdo->errorInfo());
//            $error = $pdo->errorInfo()[2];
//            if (stripos($error, "column") > 0) {
//                $alter_column = str_replace("Unknown column '", "", $error);
//                $alter_column = str_replace("' in 'field list'", "", $alter_column);
//                $sql = "ALTER TABLE  `$table_name` ADD  `$alter_column` VARCHAR( 100 ) NULL DEFAULT NULL ;";
//                $pdo->exec($sql);
//                break;
//            } else {
//                $error = str_replace("'", "''", $error);
//                $error = str_replace("\\", "\\\\", $error);
//                $pdo->exec("INSERT INTO error_message (table_name, message) VALUES "
//                        . "('$table_name', '$error')");
//            }
//        }
//    }
//} catch (Exception $ex) {
//    echo $ex->getMessage() . "\r\n";
//} finally {
//    
//}

