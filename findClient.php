<?php
/**
 * Copyright Maxim Bykovskiy © 2019.
 */

/**
 * Created by PhpStorm.
 * User: sherh
 * Date: 12.04.2019
 * Time: 18:57
 */

require_once ("classes/oneC.php");
require_once ("conf/config.php");

set_time_limit(0);
error_reporting(0);
date_default_timezone_set('Europe/Moscow');

function format_phone($phone = '')
{
//    $tel = $phone;
    $phone = preg_replace('/[^0-9]/', '', $phone); // вернет 79851111111

    if(empty($phone[0])) {
        //print_r($tel);
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

    $format_phone = '7' . $phone_number['code'] . $phone_number['phone_arr1'] . $phone_number['phone_arr2'];

    return $format_phone;
}

if(!empty($_REQUEST['phone']) && format_phone($_REQUEST['phone']) !== false) {
    $onec = new \onec\oneC(oneCURL, oneCKey);
    $rez = $onec->findClientByNum(format_phone($_REQUEST['phone']));
    echo json_encode($rez);
} else {
    echo json_encode(["error" => "Incorrect phone"]);
}