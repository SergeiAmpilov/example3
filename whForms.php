<?php
/**
 * Copyright Maxim Bykovskiy © 2018.
 */

/**
 * Created by PhpStorm.
 * User: sherh
 * Date: 29.12.2018
 * Time: 12:03
 */

$data = $_REQUEST;

if(!empty($data)) {
    $rez = json_decode(file_get_contents("forms.json"));
    $rez[] = $data;
    file_put_contents("forms.json", json_encode($rez));
}