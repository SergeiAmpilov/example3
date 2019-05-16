<?php
/**
 * Copyright Maxim Bykovskiy © 2018.
 */

/**
 * Created by PhpStorm.
 * User: sherh
 * Date: 13.12.2018
 * Time: 12:38
 */

require_once ("b24ClassWebHook.php");

set_time_limit(0);
date_default_timezone_set('Europe/Moscow');

$b24 = new \b24\webHook("biosphere.bitrix24.ru", "wb72v3opc2g689zb", "273");

function format_phone($phone = '')
{
    $tel = $phone;
    $phone = preg_replace('/[^0-9]/', '', $phone); // вернет 79851111111

    if(empty($phone[0])) {
        print_r($tel);
        return false;
    }

    if (strlen($phone) != 11 and ($phone[0] != '7' or $phone[0] != '8')) {
        return FALSE;
    }

    $phone_number['dialcode'] = substr($phone, 0, 1);
    $phone_number['code']  = substr($phone, 1, 3);
    $phone_number['phone'] = substr($phone, -7);
    $phone_number['phone_arr1'] = substr($phone_number['phone'], 0, 3);
    $phone_number['phone_arr2'] = substr($phone_number['phone'], 3, 4);
//    $phone_number['phone_arr3'] = substr($phone_number['phone'], 5, 2);

    $format_phone = '+7' . $phone_number['code'] . $phone_number['phone_arr1'] . $phone_number['phone_arr2'];

    return $format_phone;
}

//$_REQUEST['data']['FIELDS']['ID'] = 1443;

file_put_contents("into1c.txt", print_r($_REQUEST, 1), FILE_APPEND);

$id = $_REQUEST['data']['FIELDS']['ID'];

$data = $b24->getById("deal", $id)->result;

//print_r($data);die();

if($data->STAGE_ID == "WON" && !empty($data->CONTACT_ID) && $data->CONTACT_ID != 0 && (empty($data->UF_CRM_1544798367) || $data->UF_CRM_1544798367 == 0) && empty($data->UF_CRM_1544798500)) {
    $contact = $b24->getById("contact", $data->CONTACT_ID)->result;
    //print_r($contact);
    if(empty($contact->PHONE[0]->VALUE)) die("FAIL");

    $mass = [
        "deal_id" => $id,
        "user_id" => $contact->ID,
        "name" => $contact->NAME,
        "last_name" => $contact->LAST_NAME,
        "second_name" => $contact->SECOND_NAME,
        "phone" => (!empty($contact->PHONE[0]->VALUE) ? $contact->PHONE[0]->VALUE : ""),
        "email" => (!empty($contact->EMAIL[0]->VALUE) ? $contact->EMAIL[0]->VALUE : ""),
        'UTM' => (!empty($data->UTM_SOURCE) ? "UTM источник " . $data->UTM_SOURCE . "; " : "") . (!empty($data->UTM_MEDIUM) ? "UTM канал " . $data->UTM_MEDIUM . "; " : "") . (!empty($data->UTM_CAMPAIGN) ? "UTM кампания " . $data->UTM_CAMPAIGN . "; " : "") . (!empty($data->UTM_CONTENT) ? "UTM содержание компании " . $data->UTM_CONTENT : "")
    ];
    //print_r($mass); die();
    $rez = json_decode(file_get_contents("to1c.json"), true);
    $rez[] = $mass;
    file_put_contents("to1c.json", json_encode($rez));
    $update = [
        'fields' => [
            "UF_CRM_1544798500" => true
        ]
    ];
    $rez = $b24->updateFields("deal", $id, $update);
    echo "OK";
} elseif($data->STAGE_ID == "WON" && !empty($data->CONTACT_ID) && $data->CONTACT_ID != 0 && !empty($data->UF_CRM_1544798367) && $data->UF_CRM_1544798367 != 0 && !empty($data->UF_CRM_1544798500)) {
    $update = [
        'fields' => [
            "OPPORTUNITY" => $data->UF_CRM_1544798367,
            "UF_CRM_1544798367" => 0
        ]
    ];
    $rez = $b24->updateFields("deal", $id, $update);
    $update = [
        'fields' => [
            "UF_CRM_1544738077" => $data->UF_CRM_1544798367
        ]
    ];
    $rez = $b24->updateFields("contact", $data->CONTACT_ID, $update);
    echo "OK";
}