<?php
/**
 * Copyright Maxim Bykovskiy © 2018.
 */

/**
 * Created by PhpStorm.
 * User: sherh
 * Date: 26.12.2018
 * Time: 17:28
 */

require_once("b24ClassWebHook.php");

set_time_limit(0);
date_default_timezone_set('Europe/Moscow');

function getCurl($url, $header = false){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api-metrika.yandex.net/stat/v1/data?" . http_build_query($url));
    if ($header == true){
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: OAuth AQAAAAAFXG4QAAVehmefPv7XQ0VGrmTG0UQn8L0',
        "Content-Type: application/x-yametrika+json"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//    curl_setopt($ch,CURLOPT_USERAGENT , "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru-RU; rv:1.7.12) Gecko/20050919 Firefox/1.0.7");
//    curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . "/cookie.txt");
//    curl_setopt($ch, CURLOPT_COOKIEFILE,  __DIR__ . "/cookie.txt");
    $res = curl_exec($ch);
//    file_put_contents("gets.txt", print_r($res, 1), FILE_APPEND);
    curl_close($ch);
    return json_decode($res, true);
}

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

$prop = [
    "NAME" => "NAME",
    "PHONE" => "PHONE",
    "EMAIL" => "EMAIL",
    "SERVICE" => "UF_CRM_1545835289",
    "MESSAGE" => "COMMENTS",
    "URL" => "UF_CRM_1545835393",
    "FIO" => "NAME",
    "FNAME" => "TITLE",
    "SPECIALIZATION" => "UF_CRM_1545835565",
    "DATE" => "UF_CRM_1545835313",
    "NEED_PRODUCT" => "UF_CRM_1545835289",
    "COMAGIC" => "UF_CRM_1545835338",
    "YANDEX" => "UF_CRM_1545835355",
    "GOOGLE" => "UF_CRM_1545835372"
];

$UTM = [
    "1" => "UF_CRM_1546095385",
    "3" => "UTM_CAMPAIGN",
    "4" => "UTM_CONTENT",
    "5" => "UTM_MEDIUM",
    "6" => "UTM_SOURCE",
    "7" => "UTM_TERM"
];

$block = [
    "98" => "Заказать услугу",
    "99" => "Отправить резюме",
    "101" => "Обратный звонок",
    "102" => "Нужна консультация?",
    "105" => "Запись онлайн",
    "103" => "Письмо генеральному директору",
    "104" => "Запись онлайн",
    "100" => "Задать вопрос"
];

$YAID = "382cb9c967064cf892a5318a84f09f33";
$YAKEY = "d099cdff98bd4f339d6bb1e98af89f8b";

//$_REQUEST = [
//    "NAME" => 'Тестовый',
//    "PHONE" => '+7 (800) 555-35-35',
//    "EMAIL" => 'test2@test.ru',
//    "NEED_PRODUCT" => 'Лазерная эпиляция',
//    "MESSAGE" => Array
//    (
//        "TEXT" => 'тесто',
//        "TYPE" => 'HTML'
//    ),
//    "URL" => 'https://spa-bio.ru/services/apparatnaya-kosmetologiya/lazernaya-epilyatsiya/',
//    "IBLOCK_ID" => 100,
//    "RESULT" => 6370,
//    "FNAME" => 'Сообщение формы от 26.12.2018',
//    "COMAGIC" => '1813712496.2779520157.1545829289',
//    "YANDEX" => '1545902008780754137',
//    "GOOGLE" => 'GA1.2.1753521512.1545829288'
//];

//$data = $_REQUEST;

$b24 = new \b24\webHook("biosphere.bitrix24.ru", "wb72v3opc2g689zb", "273");

$resur = json_decode(file_get_contents("forms.json"), true);
file_put_contents("forms.json", "[]");

foreach ($resur as $data) {

    file_put_contents("sttests.txt", print_r($data, 1), FILE_APPEND);

    if (!empty($data)) {
        $arr = [];

        foreach ($prop as $key => $val) {
            if (!empty($data[$key])) {
                $arr[$val] = (isset($data[$key]['TEXT']) ? $data[$key]['TEXT'] : $data[$key]);
                if ($key == "PHONE")
                    $arr[$val] = [["VALUE" => cleanPhone($data[$key]), "VALUE_TYPE" => "WORK"]];
                elseif ($key == "EMAIL")
                    $arr[$val] = [["VALUE" => $data[$key], "VALUE_TYPE" => "WORK"]];
            }
        }

        $arr["ASSIGNED_BY_ID"] = "216";
        $arr["TITLE"] .= " (" . $block[$data['IBLOCK_ID']] . ")";

//    print_r($arr);

        $filter = [
            "order" => ["ID" => "DESC"],
            "filter" => ["PHONE" => $arr["PHONE"][0]["VALUE"]]
        ];
        $find = $b24->GetList("contact", $filter);

        if (!empty($find->result)) $arr['UF_CRM_1545907548'] = true;

        if (!empty($arr["UF_CRM_1545835355"])) {
            $met = getCurl(["ids" => "45800373",
                "filters" => "ym:s:clientID==" . $arr["UF_CRM_1545835355"],
                "dimensions" => "ym:s:clientID,ym:s:firstTrafficSource,ym:s:browser,ym:s:UTMCampaign,ym:s:UTMContent,ym:s:UTMMedium,ym:s:UTMSource,ym:s:UTMTerm",
                "metrics" => "ym:s:users"
            ]);
            //print_r($met);
            if (!empty($met['data'][0]['dimensions'])) {
                $fromYa = $met['data'][0]['dimensions'];
                foreach ($UTM as $key => $val) {
                    if (!empty($fromYa[$key]))
                        $arr[$val] = $fromYa[$key]['name'];
                }
            }
        }

        print_r($arr);
        $rez = $b24->add("lead", ["fields" => $arr]);
        print_r($rez);
        if(!empty($rez->result) && !empty(cleanPhone($data['PHONE']))) {
            $rez = $b24->lineStart([
                "FROM_LINE" => "74951183914",
                "TO_NUMBER" => cleanPhone($data['PHONE']),
                "TEXT_TO_PRONOUNCE" => "Я звоню тебе, потому что пришел новый лид с FaceBook."
            ]);
            print_r($rez);
        }
    }
}