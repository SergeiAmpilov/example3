<?php

require_once ("../b24ClassWebHook.php");
require_once ("../classes/oneC.php");
require_once ("../conf/config.php"); // здесь содержится сопоставление пользователей
require_once ("../commonScripts.php");

set_time_limit(0);
date_default_timezone_set('Europe/Moscow');

file_put_contents('leadChange.log', date('l jS \of F Y h:i:s A') . "\n", FILE_APPEND); // временные метки в файл
file_put_contents("leadChange.log", print_r($_REQUEST, 1), FILE_APPEND);

$b24 = new \b24\webHook(bitrixURL, bitrixKey, bitrixID);
$onec = new \onec\oneC(oneCURL, oneCKey);

$userIdSet = \commonScripts\getListOfBitrixUserIdFrom1C();
$countOfUsers = count($userIdSet);
if ($countOfUsers === 0) {
    file_put_contents("leadCreate.log", "\nNo users found", FILE_APPEND);
    die("No users found");
}
$newIdItem = floor(rand(1, $countOfUsers));
$newUserId = $userIdSet[$newIdItem - 1];

$leadId = $_REQUEST['data']['FIELDS']['ID']; // id того лида, который инициировал вебхук
// $leadId = "6315"; // debug

// обновляем лид по ид
$resUpd = $b24->updateFields("lead", $leadId,
    [
        "id" => $leadId,
        "fields" => ["ASSIGNED_BY_ID" => $newUserId],
        "params" => ["REGISTER_SONET_EVENT" => "N"],
    ]);
    file_put_contents('leadChange.log', "Change user in lead (id={$leadId}). New user id={$newUserId}", FILE_APPEND);

print_r($resUpd);

