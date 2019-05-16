<?php
/**
 * Copyright Maxim Bykovskiy © 2019.
 */

/**
 * Created by PhpStorm.
 * User: sherh
 * Date: 07.03.2019
 * Time: 13:58
 */

namespace onec;


class oneC
{
    private $domain;
    private $token;

    private function postCurl($url, $postData, $header = false){
        $url = $this->domain . $this->token . "/" . $url;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($header == true){
            curl_setopt($ch, CURLOPT_HEADER, TRUE);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_USERAGENT , "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru-RU; rv:1.7.12) Gecko/20050919 Firefox/1.0.7");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
//		curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . "/cookie.log");
//		curl_setopt($ch, CURLOPT_COOKIEFILE,  __DIR__ . "/cookie.log");
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }

    private function getCurl($url, $header = false){//print_r($url); die();
        $url = $this->domain . $this->token . "/" . $url;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        if ($header == true){
            curl_setopt($ch, CURLOPT_HEADER, TRUE);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//		curl_setopt($ch, CURLOPT_COOKIEJAR,  __DIR__ . "/cookie.log");
//		curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . "/cookie.log");
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }

    function __construct($domain, $token){
        $this->domain = $domain;
        $this->token = $token;
    }

    private function cleanPhone($phone) {
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

    public function getItems() {
        $rez = $this->getCurl("items");
        $result = json_decode($rez, true);
        return $result;
    }

    public function getUsers() {
        $rez = $this->getCurl("users");
        $result = json_decode($rez, true);
        return $result;
    }

    public function getWhatsApp() {
        $rez = $this->getCurl("whatsapp");
        $result = json_decode($rez, true);
        return $result;
    }

    public function getWhatsAppDay() {
        $rez = $this->getCurl("waday");
        $result = json_decode($rez, true);
        return $result;
    }

    public function getWhatsAppHour() {
        $rez = $this->getCurl("wahour");
        $result = json_decode($rez, true);
        return $result;
    }

    public function getNowUser($date) {
        $date = date("YmdHis", $date);
        $rez = $this->getCurl("getNowUsers/?" . http_build_query(["date" => $date]));
        $result = json_decode($rez, true);
        return $result;
    }

    public function findUser($UID) {
        $rez = $this->getCurl("findUser/?" . http_build_query(["UID" => $UID]));
        $result = json_decode($rez, true);
        return $result;
    }

    public function findClient($bitrix_id, $phone) {
        $rez = $this->getCurl("findClient/?" . http_build_query(["bitrix_id" => $bitrix_id, "phone" => $phone]));
        $result = json_decode($rez, true);
        return $result;
    }

    public function findClientByNum($phone) {
        $rez = $this->getCurl("findClientByPhone/?" . http_build_query(["phone" => $phone]));
        $result = json_decode($rez, true);
        return $result;
    }

    public function createClient($user_id, $name, $last_name, $second_name, $phone, $email, $UTM_SOURCE, $UTM_MEDIUM, $UTM_CAMPAIGN, $UTM_CONTENT) {
        $rez = $this->postCurl("createClient", json_encode([
            "user_id" => $user_id,
            "name" => $name,
            "last_name" => $last_name,
            "second_name" => $second_name,
            "phone" => $phone,
            "email" => $email,
            "UTM" => [
                "UTM_SOURCE" => $UTM_SOURCE,
                "UTM_MEDIUM" => $UTM_MEDIUM,
                "UTM_CAMPAIGN" => $UTM_CAMPAIGN,
                "UTM_CONTENT" => $UTM_CONTENT
            ]
        ]));
        $result = json_decode($rez, true);
        return $result;
    }

    public function createVisit($deal_id, $client_id, $user_id) {
        $rez = $this->postCurl("createVisit", json_encode([
            "deal_id" => $deal_id,
            "client_id" => $client_id,
            "user_id" => $user_id
        ]));
        $result = json_decode($rez, true);
        return $result;
    }

    public function createMessHistory($deal_id, $message_id, $type) {
        $rez = $this->postCurl("createMessHistory", json_encode([
            "deal_id" => $deal_id,
            "message_id" => $message_id,
            "type" => $type
        ]));
        $result = json_decode($rez, true);
        return $result;
    }

    public function successMessage($message_id) {
        $rez = $this->getCurl("successMessage/?" . http_build_query(["message_id" => $message_id]));
        $result = json_decode($rez, true);
        return $result;
    }

    public function createCert($mass) {
        $rez = $this->postCurl("addCert", json_encode($mass));
        $result = json_decode($rez, true);
        return $result;
    }

    public function createSale($deal_id, $client_id, $user_id) {
        $rez = $this->postCurl("createSale", json_encode([
            "deal_id" => $deal_id,
            "client_id" => $client_id,
            "user_id" => $user_id
        ]));
        $result = json_decode($rez, true);
        return $result;
    }
}