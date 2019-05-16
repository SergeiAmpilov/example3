<?php

require_once ("../b24ClassWebHook.php");

set_time_limit(0);
date_default_timezone_set('Europe/Moscow');

$b24 = new \b24\webHook("biosphere.bitrix24.ru", "cgjgxloray9p7dbw", "273");
file_put_contents('callRec.log', date('l jS \of F Y h:i:s A') . "\n", FILE_APPEND); // временные метки в файл
file_put_contents("testIncom.log", print_r($_REQUEST, 1), FILE_APPEND);
$called_id = $_REQUEST['called_id']; // кому звонят
$caller_id = $_REQUEST['caller_id']; // от кого идет звонок
$ext = ["106", "107", "170"]; // внутренние номера, на которые может идти вызов

$res = $b24->regCall(
[
    "USER_ID" => 216,
    "PHONE_NUMBER" => $caller_id,
    "CRM_CREATE" => 1,
    "SHOW" => 0,
    "TYPE" => 2,
]);

$callId = $res["result"]["CALL_ID"];
$userIdArray = [4, 271, 2, 273, 192, 8];
$resShow = $b24->showCall(
    [
        "CALL_ID" => $callId,
        "USER_ID" => $userIdArray,
    ]);
