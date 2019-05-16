<?php
/**
 * Copyright Maxim Bykovskiy © 2018.
 */

/**
 * Created by PhpStorm.
 * User: sherh
 * Date: 29.12.2018
 * Time: 12:21
 */

require_once("b24ClassWebHook.php");

set_time_limit(0);
date_default_timezone_set('Europe/Moscow');

$b24 = new \b24\webHook("biosphere.bitrix24.ru", "wb72v3opc2g689zb", "273");

$data = $_REQUEST;

//parse_str("id=843&value=Y", $data);

file_put_contents("paytests.txt", print_r($data, 1), FILE_APPEND);

//print_r($data); //die();

if (!empty($data) && $data['value'] == "Y") {
    $filter = [
        "order" => ["ID" => "DESC"],
        "filter" => ["UF_CRM_1546073565" => $data['id']]
    ];
    $find = $b24->GetList("deal", $filter);

    if (!empty($find->result[0])) {
        $ids = $find->result[0]->ID;
        $rez = $b24->updateFields("deal", $ids, ["fields" => [
            "UF_CRM_1546073847" => $data['value'],
            "STAGE_ID" => "C1:WON",
            "UF_CRM_1554056181" => true,
            "UF_CRM_1554059341" => $data['cert']
        ]]);
        print_r($rez);
    }
}