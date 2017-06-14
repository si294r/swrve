<?php

function load_config() 
{
    global $argv, $db_name, $file_key_php, $folder_s3, $table_suffix, $email_subject, $config;

    $db_name      = $config[$argv[1]]['db_name'];
    $file_key_php = $config[$argv[1]]['file_key_php'];
    $folder_s3    = $config[$argv[1]]['folder_s3'];
    $table_suffix = $config[$argv[1]]['table_suffix'];
    
    $email_subject = "Swrve " . str_replace("_", " ", $argv[1]) . " Result Import";
}

$email_from = "heru@alegrium.com";
$email_to = "heru@alegrium.com";

$config['Billionaire_Android']['db_name'] = "userdbs";
$config['Billionaire_Android']['file_key_php'] = "swrve_billionaire_key.php";
$config['Billionaire_Android']['folder_s3'] = "android";
$config['Billionaire_Android']['table_suffix'] = "_android";

$config['Billionaire_iOS']['db_name'] = "userdbs_ios";
$config['Billionaire_iOS']['file_key_php'] = "swrve_billionaire_ios_key.php";
$config['Billionaire_iOS']['folder_s3'] = "ios";
$config['Billionaire_iOS']['table_suffix'] = "_ios";
