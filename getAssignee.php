<?php
/**
 * Copyright Maxim Bykovskiy © 2019.
 */

/**
 * Created by PhpStorm.
 * User: sherh
 * Date: 16.04.2019
 * Time: 19:55
 */

error_reporting(0);
require_once ("classes/oneC.php");
require_once ("conf/config.php"); // здесь содержится сопоставление пользователей

// $b24 = new \b24\webHook(bitrixURL, bitrixKey, bitrixID);


function getBitrixUserIdFrom1C($time = NULL) {
    $onec = new \onec\oneC(oneCURL, oneCKey);
    $usersActual = $onec->getNowUser($time === NULL ? time() : $time);

    $userIdSet = [];
    foreach ($usersActual["result"] as $itemUser) {
        $curUserId = array_search($itemUser["user_id"], users);
        if ($curUserId === false) {
            print_r("Not found user with id=".$itemUser["user_id"]."\n");
            continue;
        }
        $userIdSet[] = $curUserId;
    }

    $countOfUsers = count($userIdSet);
    if ($countOfUsers === 0) {
        return 0;
    }

    // случайным образом определяем пользователя, если их несколько

    $newIdItem = floor(rand(1, $countOfUsers));
    return $userIdSet[$newIdItem - 1];
}

echo getBitrixUserIdFrom1C(time());