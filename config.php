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

$config['JellyPop_iOS']['db_name'] = "jellypop_userdbs_ios";
$config['JellyPop_iOS']['file_key_php'] = "swrve_jellypop_ios_key.php";
$config['JellyPop_iOS']['folder_s3'] = "jellypop_ios";
$config['JellyPop_iOS']['table_suffix'] = "_jellypop_ios";

$config['Almighty_iOS']['db_name'] = "almighty_userdbs_ios";
$config['Almighty_iOS']['file_key_php'] = "swrve_almighty_ios_key.php";
$config['Almighty_iOS']['folder_s3'] = "almighty_ios";
$config['Almighty_iOS']['table_suffix'] = "_almighty_ios";

$config['Almighty_1.5_iOS']['db_name'] = "almighty15_userdbs_ios";
$config['Almighty_1.5_iOS']['file_key_php'] = "swrve_almighty15_ios_key.php";
$config['Almighty_1.5_iOS']['folder_s3'] = "almighty15_ios";
$config['Almighty_1.5_iOS']['table_suffix'] = "_almighty15_ios";

$config['Conglomerate_iOS']['db_name'] = "conglomerate_userdbs_ios";
$config['Conglomerate_iOS']['file_key_php'] = "swrve_conglomerate_ios_key.php";
$config['Conglomerate_iOS']['folder_s3'] = "conglomerate_ios";
$config['Conglomerate_iOS']['table_suffix'] = "_conglomerate_ios";

$config['Number_Rumble_iOS']['db_name'] = "number_rumble_userdbs_ios";
$config['Number_Rumble_iOS']['file_key_php'] = "swrve_number_rumble_ios_key.php";
$config['Number_Rumble_iOS']['folder_s3'] = "number_rumble_ios";
$config['Number_Rumble_iOS']['table_suffix'] = "_number_rumble_ios";

$config['Number_Rumble_Android']['db_name'] = "number_rumble_userdbs_oid";
$config['Number_Rumble_Android']['file_key_php'] = "swrve_number_rumble_oid_key.php";
$config['Number_Rumble_Android']['folder_s3'] = "number_rumble_oid";
$config['Number_Rumble_Android']['table_suffix'] = "_number_rumble_oid";

$config['IPQ_Reborn_iOS']['db_name'] = "ipq_reborn_userdbs_ios";
$config['IPQ_Reborn_iOS']['file_key_php'] = "swrve_ipq_reborn_ios_key.php";
$config['IPQ_Reborn_iOS']['folder_s3'] = "ipq_reborn_ios";
$config['IPQ_Reborn_iOS']['table_suffix'] = "_ipq_reborn_ios";
