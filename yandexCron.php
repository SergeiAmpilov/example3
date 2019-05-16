<?php
/**
 * Copyright Maxim Bykovskiy © 2018.
 */

/**
 * Created by PhpStorm.
 * User: sherh
 * Date: 29.12.2018
 * Time: 13:09
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

function YAN($id) {
    GLOBAL $UTM;
    $met = getCurl(["ids" => "45800373",
        "date1" => date("Y-m-d", strtotime('-180 days')),
        "filters" => "ym:s:clientID==$id",
        "dimensions" => "ym:s:clientID,ym:s:firstTrafficSource,ym:s:browser,ym:s:UTMCampaign,ym:s:UTMContent,ym:s:UTMMedium,ym:s:UTMSource,ym:s:UTMTerm",
        "metrics" => "ym:s:users"
    ]);
    //print_r($met);

    $metki = [];
    if (!empty($met['data'])) {
        $fromYa = $met['data'][0]['dimensions'];
        if ($met['data'][0]['dimensions'][1]['id'] == "ad") {
            foreach ($met['data'] as $dimens) {
                if (!empty($dimens['dimensions'][3]['name'])) {
                    $fromYa = $dimens['dimensions'];
                    break;
                }
            }
        }
        foreach ($UTM as $key => $val) {
            if (!empty($fromYa[$key]))
                $metki[$val] = $fromYa[$key]['name'];
        }
    }

    return $metki;
}

$timefrom = time() - 60 * 60 * 24 * 3;

$b24 = new \b24\webHook("biosphere.bitrix24.ru", "wb72v3opc2g689zb", "273");

$UTM = [
    "1" => "SOURCE_DESCRIPTION",
    "3" => "UTM_CAMPAIGN",
    "4" => "UTM_CONTENT",
    "5" => "UTM_MEDIUM",
    "6" => "UTM_SOURCE",
    "7" => "UTM_TERM"
];

print_r("lead");
$filter = [
    "order" => ["ID" => "DESC"],
    "filter" => [">DATE_CREATE" => date("Y-m-d 00:00:00", $timefrom)],
    "select" => ["UF_CRM_1545835355", "DATE_CREATE", "UTM_SOURCE", "UTM_MEDIUM", "UTM_CAMPAIGN", "UTM_CONTENT", "UTM_TERM"]
];
$off = 0;
do {
    $find = $b24->GetList("lead", $filter, $off);
    $off = (!empty($find->next) ? $find->next : 0);
    foreach ($find->result as $lead) {
        if($lead->DATE_CREATE < date("Y-m-d\T00:00:00") && !empty($lead->UF_CRM_1545835355)
        && (empty($lead->UTM_SOURCE) || empty($lead->UTM_MEDIUM) || empty($lead->UTM_CAMPAIGN) || empty($lead->UTM_CONTENT))) {
            $metki = YAN($lead->UF_CRM_1545835355);

            print_r($lead);
            print_r($metki);

            if(!empty($metki)) {
                $metki['UF_CRM_1546095385'] = $metki['SOURCE_DESCRIPTION'];
                unset($metki['SOURCE_DESCRIPTION']);
                $rez = $b24->updateFields("lead", $lead->ID, ["fields" => $metki]);
                //print_r($rez);
            }
        }
    }

} while (!empty($find->next));

print_r("deal");
$filter = [
    "order" => ["ID" => "DESC"],
    "filter" => [">DATE_CREATE" => date("Y-m-d 00:00:00", $timefrom)],
    "select" => ["UF_CRM_5C246C6F1C4E6", "DATE_CREATE", "UTM_SOURCE", "UTM_MEDIUM", "UTM_CAMPAIGN", "UTM_CONTENT", "UTM_TERM"]
];
$off = 0;
do {
    $find = $b24->GetList("deal", $filter, $off);
    $off = (!empty($find->next) ? $find->next : 0);
    foreach ($find->result as $lead) {
        if($lead->DATE_CREATE < date("Y-m-d\T00:00:00") && !empty($lead->UF_CRM_5C246C6F1C4E6)
            && (empty($lead->UTM_SOURCE) || empty($lead->UTM_MEDIUM) || empty($lead->UTM_CAMPAIGN) || empty($lead->UTM_CONTENT))) {
            $metki = YAN($lead->UF_CRM_5C246C6F1C4E6);

            print_r($lead);
            print_r($metki);

            if(!empty($metki)) {
                $metki['UF_CRM_1546095446'] = $metki['SOURCE_DESCRIPTION'];
                unset($metki['SOURCE_DESCRIPTION']);
                $rez = $b24->updateFields("deal", $lead->ID, ["fields" => $metki]);
                //print_r($rez);
            }
        }
    }

} while (!empty($find->next));

print_r("contact");
$filter = [
    "order" => ["ID" => "DESC"],
    "filter" => [">DATE_CREATE" => date("Y-m-d 00:00:00", $timefrom)],
    "select" => ["UF_CRM_5C246C6E8CA50", "DATE_CREATE", "UTM_SOURCE", "UTM_MEDIUM", "UTM_CAMPAIGN", "UTM_CONTENT", "UTM_TERM"]
];
$off = 0;
do {
    $find = $b24->GetList("contact", $filter, $off);
    $off = (!empty($find->next) ? $find->next : 0);
    foreach ($find->result as $lead) {
        if($lead->DATE_CREATE < date("Y-m-d\T00:00:00") && !empty($lead->UF_CRM_5C246C6E8CA50)
            && (empty($lead->UTM_SOURCE) || empty($lead->UTM_MEDIUM) || empty($lead->UTM_CAMPAIGN) || empty($lead->UTM_CONTENT))) {
            $metki = YAN($lead->UF_CRM_5C246C6E8CA50);

            print_r($lead);
            print_r($metki);

            if(!empty($metki)) {
                $metki['UF_CRM_1546095410'] = $metki['SOURCE_DESCRIPTION'];
                unset($metki['SOURCE_DESCRIPTION']);
                $rez = $b24->updateFields("contact", $lead->ID, ["fields" => $metki]);
                //print_r($rez);
            }
        }
    }

} while (!empty($find->next));