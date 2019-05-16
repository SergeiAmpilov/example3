<?php

require_once ("../b24ClassWebHook.php");
//
// $_REQUEST = array(
//     "rec" => "/20190412/1827/mix_13003_17006__2019_04_12__18_27_03_863.wav",
//     "caller_id" => "79154537487",
//     "called_id" => 107,
//     "external_number" => "17006"
// );

set_time_limit(0);
date_default_timezone_set('Europe/Moscow');

file_put_contents('callRec.log',  print_r(date('l jS \of F Y h:i:s A') . "\n", 1), FILE_APPEND); // временные метки в файл
file_put_contents('callRec.log', print_r($_REQUEST, 1), FILE_APPEND);


$extPhoneCorrect = array( "74956204868", "17006", "17013", "17018");
if (empty($_REQUEST["external_number"]) || !in_array($_REQUEST["external_number"], $extPhoneCorrect)) {
    die("not spa");
}

function format_phone($phone)
{
    $phone = preg_replace('/[^0-9]/', '', $phone); // вернет 79851111111
    if (strlen($phone) != 11 && ($phone[0] != '7' || $phone[0] != '8')) {
        return false;
    }
    $phone_number['dialcode'] = substr($phone, 0, 1);
    $phone_number['code']  = substr($phone, 1, 3);
    $phone_number['phone'] = substr($phone, -7);
    $phone_number['phone_arr1'] = substr($phone_number['phone'], 0, 3);
    $phone_number['phone_arr2'] = substr($phone_number['phone'], 3, 4);
//      $phone_number['phone_arr3'] = substr($phone_number['phone'], 5, 2);
    $format_phone = '+7' . $phone_number['code'] . $phone_number['phone_arr1'] . $phone_number['phone_arr2'];
    return $format_phone;
}

$b24 = new \b24\webHook("biosphere.bitrix24.ru", "cgjgxloray9p7dbw", "273");

// die(); ////

$rec = "http://81.23.9.252:8828" . $_REQUEST['rec'];
$dirPath = getDir($_REQUEST['rec']);
$recName = getFileName($_REQUEST['rec']);
$caller_id = $_REQUEST['caller_id'];

if(format_phone($caller_id) === false) {
    die();
}

//
$phoneCorrectFormated = format_phone($caller_id);
$clientFrom1c = getClientFrom1CByPhoneNumber($caller_id);
if ($clientFrom1c !== false) {
    // сначала пробуем искать имеющийся контакт
    $arrWithParams = array(
        "filter" => [
            "PHONE" => $phoneCorrectFormated
        ]
    );
    $resListOfContacts = $b24->GetList("contact", $arrWithParams, 0);

    if ($resListOfContacts->total === 0) {
       // создаем контакт
       $newFields = array(
       "NAME" => $clientFrom1c["firstName"], 
       "SECOND_NAME" => $clientFrom1c["secondName"], 
        "LAST_NAME" => $clientFrom1c["lastName"], 
        "PHONE" => [ [ "VALUE" => $phoneCorrectFormated, "VALUE_TYPE" => "WORK" ] ] );	
        $newClientId = $b24->add("contact", ["fields" => $newFields, "params" => ["REGISTER_SONET_EVENT" => "N"]]);
    }
}

//

$res = $b24->regCall(
    [
        "USER_ID" => 216,
        "PHONE_NUMBER" => $caller_id,
        "CRM_CREATE" => 1,
        "SHOW" => 0,
        "TYPE" => 2,
    ]);
print_r("Reg call \n");
print_r($res);


$callId = $res["result"]["CALL_ID"];
$finishRes = $b24->finishCall(
    [
        "CALL_ID" => $callId,
        "USER_ID" => 216,
        "DURATION" => 60,
]);
print_r("finish call res \n");
print_r($finishRes);

$recNameConverted = str_replace(".wav", ".mp3", $recName); // получаем имя нового конв файла
$data = json_decode(file_get_contents('http://api.rest7.com/v1/sound_convert.php?url=' . $rec . '&format=mp3'));
print_r("converting \n");
print_r($data);

if (@$data->success !== 1)
{
    die('Failed');
}
$wave = file_get_contents($data->file);
file_put_contents($recNameConverted, $wave);



// подключаемся к FTP
print_r("connecting to ftp... \n");

$ftp_server = "81.23.9.252";
$port = "8897";
$ftp_user_name = "root";
$ftp_user_pass = "Pcgmgy11";

$ftp_conn_id = ftp_connect($ftp_server, $port);
$login_result = ftp_login($ftp_conn_id, $ftp_user_name, $ftp_user_pass);

// включение пассивного режима
ftp_pasv($ftp_conn_id, true);

// проверяем подключение
if ((!$ftp_conn_id) || (!$login_result)) {
    echo "FTP connection has failed!";
    echo "Attempted to connect to $ftp_server for user: $ftp_user_name";
    // exit;
} else {
    echo "Connected to $ftp_server, for user: $ftp_user_name";
}

// грузим файл
$recConvertedPath = $dirPath . $recNameConverted;
print_r("\n\n Tru to upload file");
print_r("\n recConvertedPath=".$recConvertedPath);
print_r("\n recNameConverted=".$recNameConverted);
print_r("\n");
$upload = ftp_put($ftp_conn_id, $recConvertedPath, $recNameConverted, FTP_BINARY);


$recConvertesFullPath = "http://81.23.9.252:8828".$recConvertedPath;

// проверяем статус загрузки
if (!$upload) {
    echo "Error: FTP upload has failed!";
} else {
    echo "Good: Uploaded $recNameConverted to $ftp_server";
}

// закрываем соединение
ftp_close($ftp_conn_id);
sleep(10);

$attachRes = $b24->attachRecord(
    [
        "CALL_ID" => $callId,
        "FILENAME" => $recNameConverted,
        'RECORD_URL' => $recConvertesFullPath,
    ]);

print_r($attachRes);
print_r("\n");

// удаляем локально созданный файл
$deleteRes = unlink($recNameConverted);
print_r($deleteRes);

//////

// function getDirAndFileName($stringWithPath) {
//     $sepStr = "/";
//     $pathParts = explode($sepStr, $stringWithPath);
//     $filename = array_pop($pathParts);
//     $dirFullPath = implode($sepStr, $pathParts).$sepStr;

//     return [$dirFullPath, $filename];
// }

function getDir($stringWithPath) {
    $sepStr = "/";
    $pathParts = explode($sepStr, $stringWithPath);
    $filename = array_pop($pathParts);
    $dirFullPath = implode($sepStr, $pathParts).$sepStr;

    return  $dirFullPath;
}

function getFileName($stringWithPath) {
    $sepStr = "/";
    $pathParts = explode($sepStr, $stringWithPath);
    $filename = array_pop($pathParts);
    // $dirFullPath = implode($sepStr, $pathParts).$sepStr;

    return $filename;
}

function getClientFrom1CByPhoneNumber($phoneNumber = "") {
    $url = 'http://81.23.9.252:8808/findClient.php';

    $myCurl = curl_init();
    curl_setopt_array($myCurl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_POSTFIELDS => http_build_query(["phone" => $phoneNumber])
    ));
    $response = curl_exec($myCurl);
    curl_close($myCurl);
    $result = json_decode($response, true);

    print_r($result);

    if ($result["success"] === false) {
        return false;
    }

    return $result["result"];
}