<?php
/**
 * Copyright Maxim Bykovskiy © 2018.
 */

/**
 * Created by PhpStorm.
 * User: sherh
 * Date: 13.12.2018
 * Time: 13:18
 */

$rez = json_decode(file_get_contents("to1c.json"), true);
file_put_contents("to1c.json", json_encode([]));

echo json_encode(["count" => count($rez), "rows" => $rez]);