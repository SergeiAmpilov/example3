<?php
/**
 * Copyright Maxim Bykovskiy © 2019.
 */

/**
 * Created by PhpStorm.
 * User: sherh
 * Date: 08.04.2019
 * Time: 12:35
 */

require_once ("../classes/b24ClassWebHook.php");
require_once ("../classes/oneC.php");
require_once ("../conf/config.php");

set_time_limit(0);
date_default_timezone_set('Europe/Moscow');

$b24 = new \b24\webHook(bitrixURL, bitrixKey, bitrixID);
$onec = new \onec\oneC(oneCURL, oneCKey);

$users = $onec->getNowUser(time());

print_r($users);