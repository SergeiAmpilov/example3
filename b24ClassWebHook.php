<?php

namespace b24;

class webHook{
	private $bitrix_domain;
	private $bitrixKey;
	private $bitrixid;

	private function postCurl($url, $postData, $header = false){
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
		curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . "/cookie.log");
		curl_setopt($ch, CURLOPT_COOKIEFILE,  __DIR__ . "/cookie.log");
    	$res = curl_exec($ch);
    	curl_close($ch);
    	return $res;
	}

	private function getCurl($url, $header = false){
		$ch = curl_init();
     	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
     	if ($header == true){
     		curl_setopt($ch, CURLOPT_HEADER, TRUE);
     	}
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_COOKIEJAR,  __DIR__ . "/cookie.log");
		curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . "/cookie.log");
		$res = curl_exec($ch);
    	curl_close($ch);
    	return $res;
	}
	
	function __construct($bitrix_domain, $bitrixKey, $bitrixid = 1){
		$this->bitrix_domain = $bitrix_domain;
		$this->bitrixKey = $bitrixKey;
		$this->bitrixid = $bitrixid;
	}
	
	function add($name, $fields){
        $result = $this->getCurl("https://" . $this->bitrix_domain . "/rest/" . $this->bitrixid . "/" . $this->bitrixKey . "/crm." . $name . ".add/?" .  http_build_query($fields));
        $jsonanswer = json_decode($result);
        return $jsonanswer;
	}

    function Get($name, $fields){
        $result = $this->getCurl("https://" . $this->bitrix_domain . "/rest/" . $this->bitrixid . "/" . $this->bitrixKey . "/crm." . $name . ".get/?id=" . $fields);
        $jsonanswer = json_decode($result);
        return $jsonanswer;
    }

	function fields($name){
        $result = $this->getCurl("https://" . $this->bitrix_domain . "/rest/" . $this->bitrixid . "/" . $this->bitrixKey . "/crm." . $name . ".fields/");
        $jsonanswer = json_decode($result);
        return $jsonanswer;
	}

	function GetList($name, $params = array(), $start = 0) {
   		$result = $this->getCurl("https://" . $this->bitrix_domain . "/rest/" . $this->bitrixid . "/" . $this->bitrixKey . "/crm.".$name.".list/?start=$start" . "&" . http_build_query($params));
      	$jsonanswer = json_decode($result);
       	return $jsonanswer;
	}
	
	function setgoods($id, $params = array()) {
   		$result = $this->getCurl("https://" . $this->bitrix_domain . "/rest/" . $this->bitrixid . "/" . $this->bitrixKey . "/crm.deal.productrows.set/?id=$id" . "&" . http_build_query($params));
      	$jsonanswer = json_decode($result);
       	return $jsonanswer;
	}

	function lineStart($params = array()) {
   		$result = $this->getCurl("https://" . $this->bitrix_domain . "/rest/" . $this->bitrixid . "/" . $this->bitrixKey . "/voximplant.callback.start/?" . http_build_query($params));
      	$jsonanswer = json_decode($result);
       	return $jsonanswer;
	}

    function regCall($params = array()) {
        $result = $this->getCurl("https://" . $this->bitrix_domain . "/rest/" . $this->bitrixid . "/" . $this->bitrixKey . "/telephony.externalcall.register/?" . http_build_query($params));
        $jsonanswer = json_decode($result, true);
        return $jsonanswer;
    }

    function showCall($params = array()) {
        $result = $this->getCurl("https://" . $this->bitrix_domain . "/rest/" . $this->bitrixid . "/" . $this->bitrixKey . "/telephony.externalcall.show/?" . http_build_query($params));
        $jsonanswer = json_decode($result, true);
        return $jsonanswer;
    }

    function finishCall($params = array()) {
        $result = $this->getCurl("https://" . $this->bitrix_domain . "/rest/" . $this->bitrixid . "/" . $this->bitrixKey . "/telephony.externalcall.finish/?" . http_build_query($params));
        $jsonanswer = json_decode($result, true);
        return $jsonanswer;
    }

    function attachRecord($params = array()) {
        $result = $this->getCurl("https://" . $this->bitrix_domain . "/rest/" . $this->bitrixid . "/" . $this->bitrixKey . "/telephony.externalcall.attachRecord/?" . http_build_query($params));
        $jsonanswer = json_decode($result, true);
        return $jsonanswer;
	}

	function getListOfAttributes($name, $params = array()) {
		$result = $this->getCurl("https://" . $this->bitrix_domain . "/rest/" . $this->bitrixid . "/" . $this->bitrixKey . "/" . $name ."/?" . http_build_query($params));
        $jsonanswer = json_decode($result, true);
        return $jsonanswer;
	}
	
	function getById($name, $id){
   		$result = $this->getCurl("https://" . $this->bitrix_domain . "/rest/" . $this->bitrixid . "/" . $this->bitrixKey . "/crm." . $name . ".get/?id=" . $id);
      	$jsonanswer = json_decode($result);
       	return $jsonanswer;
	}

    function lineGet(){
        $result = $this->getCurl("https://" . $this->bitrix_domain . "/rest/" . $this->bitrixid . "/" . $this->bitrixKey . "/voximplant.line.get");
//        print_r($result);
        $jsonanswer = json_decode($result);
        return $jsonanswer;
    }
	
	function getTask($name, $id){
   		$result = $this->getCurl("https://" . $this->bitrix_domain . "/rest/" . $this->bitrixid . "/" . $this->bitrixKey . "/" . $name . "/?id=" . $id);
   		$jsonanswer = json_decode($result);
       	return $jsonanswer;
	}

    function sendNot($structid, $t){
	    $to = [2428, 42, 2592, 60, 26];
	    $to = [2572];
	    if($t == 1)
	        $text = 'Новое сообщение от клиента.#BR#https://standart001.bitrix24.ru/crm/contact/details/'.$structid.'/';
	    else if($t == 2)
            $text = 'Новое сообщение от клиента.#BR#https://standart001.bitrix24.ru/crm/lead/details/'.$structid.'/';

	    foreach ($to as $id) {
            $arr = [
                'to' => $id,
                'message' => $text,
                'type' => 'SYSTEM'
            ];
            $result = $this->getCurl("https://" . $this->bitrix_domain . "/rest/" . $this->bitrixid . "/" . $this->bitrixKey . "/im.notify/?" . http_build_query($arr));
        }
        //$jsonanswer = json_decode($result);
        return "OK";
    }
	
	function updateFields($name, $id, $fields = array()){
        $result = $this->getCurl("https://" . $this->bitrix_domain . "/rest/" . $this->bitrixid . "/" . $this->bitrixKey . "/crm." . $name . ".update/?id=" . $id . "&" .  http_build_query($fields));
        $jsonanswer = json_decode($result);
        return $jsonanswer;
	}
}