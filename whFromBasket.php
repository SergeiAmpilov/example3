<?php
/**
 * Copyright Maxim Bykovskiy © 2018.
 */

/**
 * Created by PhpStorm.
 * User: sherh
 * Date: 27.12.2018
 * Time: 16:13
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

$UTM = [
    "1" => "UF_CRM_1546095410",
    "3" => "UTM_CAMPAIGN",
    "4" => "UTM_CONTENT",
    "5" => "UTM_MEDIUM",
    "6" => "UTM_SOURCE",
    "7" => "UTM_TERM"
];

$b24 = new \b24\webHook("biosphere.bitrix24.ru", "wb72v3opc2g689zb", "273");

//$data = $_REQUEST;

//parse_str("ID=841&ORDER_PROP%5BNAME%5D=%D0%A2%D0%B5%D1%81%D1%82%D0%BE%D0%B2%D1%8B%D0%B9+%D0%97%D0%B0%D0%BA%D0%B0%D0%B7&ORDER_PROP%5BEMAIL%5D=test2%40test.ru&ORDER_PROP%5BPHONE%5D=78005553535&ORDER_PROP%5BADDRESS%5D=%D0%9C%D0%B0%D0%BB%D0%B0%D1%8F+%D0%9A%D0%B0%D0%BB%D1%83%D0%B6%D1%81%D0%BA%D0%B0%D1%8F+%D1%83%D0%BB%D0%B8%D1%86%D0%B0&PRICE=20400&PAY_SYSTEM=%D0%9D%D0%B0%D0%BB%D0%B8%D1%87%D0%BD%D1%8B%D0%B9+%D1%80%D0%B0%D1%81%D1%87%D0%B5%D1%82&DELIVERY=%D0%94%D0%BE%D1%81%D1%82%D0%B0%D0%B2%D0%BA%D0%B0+%D0%BA%D1%83%D1%80%D1%8C%D0%B5%D1%80%D0%BE%D0%BC+%D0%B2+%D0%BF%D1%80%D0%B5%D0%B4%D0%B5%D0%BB%D0%B0%D1%85+%D0%9C%D0%9A%D0%90%D0%94&COMMENT=wow&COMAGIC=1815479286.2786831883.1546068012&YANDEX=1545899106101966412&GOOGLE=GA1.2.1398559485.1546068012&ITEMS%5B0%5D%5BPRODUCT_NAME%5D=%D0%91%D0%B0%D0%BD%D1%8F+%D0%BF%D0%BE-%D1%86%D0%B0%D1%80%D1%81%D0%BA%D0%B8&ITEMS%5B0%5D%5BPRICE%5D=19900.0000&ITEMS%5B0%5D%5BQUANTITY%5D=1.0000&ITEMS%5B1%5D%5BPRODUCT_NAME%5D=%D0%9D%D0%BE%D0%B2%D0%BE%D0%B3%D0%BE%D0%B4%D0%BD%D1%8F%D1%8F+%D1%83%D0%BF%D0%B0%D0%BA%D0%BE%D0%B2%D0%BA%D0%B0+%D1%81%D0%B5%D1%80%D1%82%D0%B8%D1%84%D0%B8%D0%BA%D0%B0%D1%82%D0%B0+%D1%81+%D1%88%D0%BE%D0%BA%D0%BE%D0%BB%D0%B0%D0%B4%D0%BD%D0%BE%D0%B9+%D1%84%D0%B8%D0%B3%D1%83%D1%80%D0%BA%D0%BE%D0%B9+%D0%B2%D0%BD%D1%83%D1%82%D1%80%D0%B8&ITEMS%5B1%5D%5BPRICE%5D=500.0000&ITEMS%5B1%5D%5BQUANTITY%5D=1.0000&ITEMS%5B2%5D%5BPRODUCT_NAME%5D=%D0%94%D0%BE%D1%81%D1%82%D0%B0%D0%B2%D0%BA%D0%B0+%D0%BA%D1%83%D1%80%D1%8C%D0%B5%D1%80%D0%BE%D0%BC+%D0%B2+%D0%BF%D1%80%D0%B5%D0%B4%D0%B5%D0%BB%D0%B0%D1%85+%D0%9C%D0%9A%D0%90%D0%94&ITEMS%5B2%5D%5BPRICE%5D=0&ITEMS%5B2%5D%5BQUANTITY%5D=1", $data);

$resur = json_decode(file_get_contents("orders.json"), true);
file_put_contents("orders.json", "[]");

foreach ($resur as $data) {

    file_put_contents("bstests.txt", print_r($data, 1), FILE_APPEND);

//print_r($data); //die();

    if (!empty($data)) {
        $metki = [];
        if (!empty($data['YANDEX'])) {
            $met = getCurl(["ids" => "45800373",
                "filters" => "ym:s:clientID==" . $data['YANDEX'],
                "dimensions" => "ym:s:clientID,ym:s:firstTrafficSource,ym:s:browser,ym:s:UTMCampaign,ym:s:UTMContent,ym:s:UTMMedium,ym:s:UTMSource,ym:s:UTMTerm",
                "metrics" => "ym:s:users"
            ]);
            //print_r($met);
            if (!empty($met['data'][0]['dimensions'])) {
                $fromYa = $met['data'][0]['dimensions'];
                foreach ($UTM as $key => $val) {
                    if (!empty($fromYa[$key]))
                        $metki[$val] = $fromYa[$key]['name'];
                }
            }
        }

        //print_r($metki); die();

        $filter = [
            "order" => ["ID" => "DESC"],
            "filter" => ["PHONE" => cleanPhone($data["ORDER_PROP"]["PHONE"])]
        ];
        $find = $b24->GetList("contact", $filter);

        $idc = 0;

        if (!empty($find->result)) {
            $ids = $find->result[0]->ID;
        } else {
            $arr = [
                "NAME" => $data['ORDER_PROP']['NAME'],
                "PHONE" => [["VALUE_TYPE" => "WORK", "VALUE" => cleanPhone($data['ORDER_PROP']["PHONE"])]],
                "EMAIL" => [["VALUE_TYPE" => "WORK", "VALUE" => $data['ORDER_PROP']["EMAIL"]]],
                "ADDRESS" => $data['ORDER_PROP']['ADDRESS']
            ];

            if (!empty($metki))
                $arr = array_merge($arr, $metki);

            $rez = $b24->add("contact", ["fields" => $arr]);
            if (!empty($rez->result)) $ids = $rez->result;
        }

        echo $ids;

        if (!empty($ids)) {
            $arr = [
                "TITLE" => "Заказ из корзины №" . $data['ID'],
                "UF_CRM_1546073565" => $data['ID'],
                "CONTACT_ID" => $ids,
                "CATEGORY_ID" => 1,
                "COMMENTS" => $data['COMMENT'],
                "UF_CRM_1546071847" => $data['DELIVERY'],
                "UF_CRM_1546071878" => $data['PAY_SYSTEM'],
                "UF_CRM_5C246C6F12ECF" => $data['COMAGIC'],
                "UF_CRM_5C246C6F1C4E6" => $data['YANDEX'],
                "UF_CRM_5C246C6F258BD" => $data['GOOGLE']
            ];

            if (!empty($metki)) {
                $metki['UF_CRM_1546095446'] = $metki['UF_CRM_1546095410'];
                unset($metki['UF_CRM_1546095410']);
                $arr = array_merge($arr, $metki);
            }

            $idd = 0;
            $rez = $b24->add("deal", ["fields" => $arr]);
            print_r($rez);
            if (!empty($rez->result)) $idd = $rez->result;

            if (!empty($idd) && !empty($data['ITEMS'])) {
                $rez = $b24->setgoods($idd, ["rows" => $data['ITEMS']]);
                print_r($rez);
                if(!empty(cleanPhone($data['ORDER_PROP']["PHONE"]))) {
                    $rez = $b24->lineStart([
                        "FROM_LINE" => "74951183914",
                        "TO_NUMBER" => cleanPhone($data['ORDER_PROP']["PHONE"]),
                        "TEXT_TO_PRONOUNCE" => "Я звоню тебе, потому что пришел новый заказ с корзины сайта."
                    ]);
                    print_r($rez);
                }
            }
        }
    }
}