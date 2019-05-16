<?php

require_once ("../b24ClassWebHook.php");
require_once ("../classes/oneC.php");
require_once ("../conf/config.php"); // здесь содержится сопоставление пользователей
require_once ("../commonScripts.php");

set_time_limit(0);
date_default_timezone_set('Europe/Moscow');

file_put_contents('leadChangeCron.log', "\n\n" . date('l jS \of F Y h:i:s A'), FILE_APPEND); // временные метки в файл

// $debugLimit = 5; /////////
$b24 = new \b24\webHook(bitrixURL, bitrixKey, bitrixID);

// фильтр незакрытых лидов
$arrWithParams = array(
    "filter" => [
        "STATUS_SEMANTIC_ID" => "P"
    ]
);
$swap = 0; //смещение
$isNext = true;

$userIdSet = \commonScripts\getListOfBitrixUserIdFrom1C();
$countOfUsers = count($userIdSet);
if ($countOfUsers === 0) {
    die("No users found");
}

do {

    $resListOfLeads = $b24->GetList("lead", $arrWithParams, $swap);
    $isNext = property_exists($resListOfLeads, 'next');
    $swap = $isNext ? $resListOfLeads->next : $swap;
    $resArray = $resListOfLeads->result;

    foreach ($resArray as $curLead) {        
        $leadId = $curLead->ID;
        // $newUserId = \commonScripts\getBitrixUserIdFrom1C();
        $newIdItem = floor(rand(1, $countOfUsers));
        $newUserId = $userIdSet[$newIdItem - 1];       

        // обновляем лид по ид
        $resUpd = $b24->updateFields("lead", $leadId,
            [
                "id" => $leadId,
                "fields" => ["ASSIGNED_BY_ID" => $newUserId],
                "params" => ["REGISTER_SONET_EVENT" => "N"],
            ]);
        $logString = "\nChange user in lead (id={$leadId}). New user id={$newUserId}";
        file_put_contents('leadChangeCron.log', $logString, FILE_APPEND);

        // debug limit
        // $debugLimit -= 1;
        // if ($debugLimit <= 0) {
        //     die("debug limit");
        // }
    }    
} while ($isNext);