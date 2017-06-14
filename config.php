<?php

function load_config() 
{
    global $argv, $db_name, $file_key_php, $folder_s3, $table_suffix, $email_subject, $config;

    list($db_name, $file_key_php, $folder_s3, $table_suffix) = $config[$argv[1]];
    
    $email_subject = "Swrve " . str_replace("_", " ", $argv[1]) . " Result Import";
}

$email_from = "heru@alegrium.com";
$email_to = "heru@alegrium.com";

$config['Billionaire_Android']['db_name'] = "userdbs";
$config['Billionaire_Android']['file_key_php'] = "swrve_billionaire_key.php";
$config['Billionaire_Android']['folder_s3'] = "android";
$config['Billionaire_Android']['table_suffix'] = "_android";
