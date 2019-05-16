<?php
/**
 * Copyright Maxim Bykovskiy © 2018.
 */

/**
 * Created by PhpStorm.
 * User: sherh
 * Date: 26.12.2018
 * Time: 18:49
 */

require_once("b24ClassWebHook.php");

set_time_limit(0);
date_default_timezone_set('Europe/Moscow');

function cleanPhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone); // вернет 79851111111
    if (strlen($phone) != 11 && ($phone[0] != '7' || $phone[0] != '8')) {
        return FALSE;
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

$data = json_decode(file_get_contents("php://input"), true);
//$data = json_decode("{\"phone_number\": \"+79894654020\", \"ad_id\": \"21579224375820150\", \"form_id\": \"574601735042061\", \"gender\": \"posu\", \"id\": \"461897490576789\", \"raw\": {\"phone_number\": \"+74539718090\", \"gender\": \"beve\", \"email\": \"isape_uzdvyhu13@xajnior.zan\", \"full_name\": \"Iryta Aq-Mcifi\"}, \"full_name\": \"Esuxu Ec-Gbyho\", \"created_time\": \"2018-03-19T15:33:36+00:00\", \"email\": \"ugydo_isfdexy39@cykfayz.wic\"}", true);

file_put_contents("fbtests.txt", print_r($data, 1), FILE_APPEND);

//print_r($data);

if(!empty($data) && !empty($data['phone_number'])) {
    $b24 = new \b24\webHook("biosphere.bitrix24.ru", "wb72v3opc2g689zb", "273");

    $arr = [
        "TITLE" => "Лид из faceBook",
        "NAME" => $data['full_name'],
        "PHONE" => [["VALUE" => cleanPhone($data['phone_number']), "VALUE_TYPE" => "WORK"]],
        "EMAIL" => [["VALUE" => $data['email'], "VALUE_TYPE" => "WORK"]],
        "UF_CRM_1546095385" => "Facebook",
        "UF_CRM_1545906437" => (!empty($data['id']) ? $data['id'] : "") . ";" . (!empty($data['ad_id']) ? $data['ad_id'] : "") . ";" . (!empty($data['form_id']) ? $data['form_id'] : "")
    ];

    print_r($arr);
    $rez = $b24->add("lead", ["fields" => $arr]);
    print_r($rez);
    if(!empty($rez->result) && !empty(cleanPhone($data['phone_number']))) {
        $rez = $b24->lineStart([
            "FROM_LINE" => "74951183914",
            "TO_NUMBER" => cleanPhone($data['phone_number']),
            "TEXT_TO_PRONOUNCE" => "Я звоню тебе, потому что пришел новый лид с FaceBook."
        ]);
        print_r($rez);
    }
}