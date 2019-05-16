<?php

require_once ("../b24ClassWebHook.php");
require_once ("../classes/oneC.php");
require_once ("../conf/config.php"); // здесь содержится сопоставление пользователей
require_once ("../commonScripts.php");


// получим лист сделок
set_time_limit(0);
date_default_timezone_set('Europe/Moscow');
file_put_contents('dealChangeCron.log', "\n\n" . date('l jS \of F Y h:i:s A'), FILE_APPEND); // временные метки в файл

// $debugLimit = 5; /////////
$b24 = new \b24\webHook(bitrixURL, bitrixKey, bitrixID);

// фильтр незакрытых сделок
$arrWithParams = array(
    "filter" => [
        "STAGE_SEMANTIC_ID" => "P"
    ]
);
$swap = 0;
$isNext = true;

$userIdSet = \commonScripts\getListOfBitrixUserIdFrom1C();
$countOfUsers = count($userIdSet);
if ($countOfUsers === 0) {
    die("No users found");
}

do {
    $resListOfDeals = $b24->GetList("deal", $arrWithParams, $swap);
    $isNext = property_exists($resListOfDeals, 'next');
    $swap = $isNext ? $resListOfDeals->next : $swap;
    $resArray = $resListOfDeals->result;

    foreach ($resArray as $curDeal) {
        $dealId = $curDeal->ID;

        // $newUserId = \commonScripts\getBitrixUserIdFrom1C();
        $newIdItem = floor(rand(1, $countOfUsers));
        $newUserId = $userIdSet[$newIdItem - 1];       

        // обновляем сделку по ид
        $resUpd = $b24->updateFields("deal", $dealId,
            [
                "id" => $dealId,
                "fields" => ["ASSIGNED_BY_ID" => $newUserId],
                "params" => ["REGISTER_SONET_EVENT" => "N"],
            ]);
        
        $logString = "\nChange user in deal (id={$dealId}). New user id={$newUserId}";
        file_put_contents('dealChangeCron.log', $logString, FILE_APPEND);

        // debug limit
        // $debugLimit -= 1;
        // if ($debugLimit <= 0) {
        //     die("debug limit");
        // }
    }


} while ($isNext);


print_r($resListOfDeals);