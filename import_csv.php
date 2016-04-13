<?php

$db_host = 'mariadb_server';
$db_user = 'root';
$db_pass = 'password';
$db_name = 'swrve';

exec("find . -path *userdbs-*.csv", $output); // find all csv
print_r($output);
die;

//$table_name = "swrve_properties";
//$content = file_get_contents("/var/www/html/all-users_01377_2016-04-05_swrve_properties.1_0.1.csv");

$table_name = "custom_properties";
$content = file_get_contents("/var/www/html/swrve/userdbs-2016-04-12/all-users_02618_2016-04-12_abtest_exposure.1_0.1.csv");

$array = explode("\n", $content);

$columns = explode(",", $array[0]);
$update_columns = $columns;
foreach ($update_columns as $k => $v) {
    $update_columns[$k] = $v . "=VALUES(" . $v . ")";
}

try {
    $pdo = new PDO("mysql:dbname=$db_name;host=$db_host", $db_user, $db_pass);

    for ($i = 1; $i < count($array); $i++) {
        $array[$i] = str_replace(",", ",,", $array[$i]);
        $array[$i] = str_replace("\,,", ",", $array[$i]);
        $row = explode(",,", $array[$i]);
        foreach ($row as $k => $v) {
            if ($v == "\\N")
                $row[$k] = "NULL";
            $row[$k] = str_replace("'", "''", $row[$k]);
            $row[$k] = str_replace("\\", "\\\\", $row[$k]);
        }
        $sql = "INSERT INTO $table_name (" . implode(",", $columns) . ") "
                . " VALUES ('" . implode("','", $row) . "') "
                . " ON DUPLICATE KEY UPDATE " . implode(",", $update_columns);

        echo $i . "=" . $row[0] . "\r\n";
        if ($pdo->exec($sql)) {
            
        } else {
//            var_dump($pdo->errorInfo());
            $error = $pdo->errorInfo()[2];
            if (stripos($error, "column") > 0) {
                $alter_column = str_replace("Unknown column '", "", $error);
                $alter_column = str_replace("' in 'field list'", "", $alter_column);
                $sql = "ALTER TABLE  `$table_name` ADD  `$alter_column` VARCHAR( 100 ) NULL DEFAULT NULL ;";
                $pdo->exec($sql);
                break;
            } else {
                $error = str_replace("'", "''", $error);
                $error = str_replace("\\", "\\\\", $error);
                $pdo->exec("INSERT INTO error_message (table_name, message) VALUES "
                        . "('$table_name', '$error')");
            }
        }
    }
} catch (Exception $ex) {
    echo $ex->getMessage() . "\r\n";
} finally {
    
}



