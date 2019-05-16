<?php
/**
 * Copyright Maxim Bykovskiy © 2018.
 */

/**
 * Created by PhpStorm.
 * User: sherh
 * Date: 17.08.2018
 * Time: 16:02
 */

namespace imaper;

class emails
{
    private $mbox;

    private $html;

    private $text;

    private $time;

    private $parseRules = [
        '/\s+/' => ' ', //Remove HTML's whitespaces
        '/<(img)\b[^>]*alt=\"([^>"]+)\"[^>]*>/Uis' => '($2)', //Parse image tags with alt
        '/<(img)\b[^>][^>]*>/Uis' => '', // Remove image tags without alt
        '/<a(.*)href=[\'"](.*)[\'"]>(.*)<\/a>/Uis' => '$3 ($2)', //Parse links
        '/<hr(.*)>/Uis' => "\n==================================\n", //Parse lines
        '/<br(.*)>/Uis' => "\n", //Parse breaklines
        '/<(.*)br>/Uis' => "\n", //Parse broken breaklines
        '/<p(.*)>(.*)<\/p>/Uis' => "\n$2\n", //Parse alineas

        //Lists
        '/(<ul\b[^>]*>|<\/ul>)/i' => "\n\n",
        '/(<ol\b[^>]*>|<\/ol>)/i' => "\n\n",
        '/(<dl\b[^>]*>|<\/dl>)/i' => "\n\n",

        '/<li\b[^>]*>(.*?)<\/li>/i' => "\t* $1\n",
        '/<dd\b[^>]*>(.*?)<\/dd>/i' => "$1\n",
        '/<dt\b[^>]*>(.*?)<\/dt>/i' => "\t* $1",
        '/<li\b[^>]*>/i' => "\n\t* ",

        //Parse table columns
        '/<tr>(.*)<\/tr>/Uis' => "\n$1",
        '/<td>(.*)<\/td>/Uis' => "$1\t",
        '/<th>(.*)<\/th>/Uis' => "$1\t",
        //Parse markedup text
        '/<em\b[^>]*>(.*?)<\/em>/i' => "$2",
        '/<b>(.*)<\/b>/Uis' => '**$1**',
        '/<strong(.*)>(.*)<\/strong>/Uis' => '**$2**',
        '/<i>(.*)<\/i>/Uis' => '*$1*',
        '/<u>(.*)<\/u>/Uis' => '_$1_',
        //Headers
        '/<h1(.*)>(.*)<\/h1>/Uis' => "\n### $2 ###\n",
        '/<h2(.*)>(.*)<\/h2>/Uis' => "\n## $2 ##\n",
        '/<h3(.*)>(.*)<\/h3>/Uis' => "\n## $2 ##\n",
        '/<h4(.*)>(.*)<\/h4>/Uis' => "\n## $2 ##\n",
        '/<h5(.*)>(.*)<\/h5>/Uis' => "\n# $2 #\n",
        '/<h6(.*)>(.*)<\/h6>/Uis' => "\n# $2 #\n",
        //Surround tables with newlines
        '/<table(.*)>(.*)<\/table>/Uis' => "\n$2\n",
    ];

    public function __construct($host, $port, $protokol, $sha, $box, $username, $pass, $time)
    {
        $this->time = $time;

        $this->mbox = imap_open('{' . $host . ':' . $port . '/' . $protokol . (!empty($sha) ? '/' . $sha : '') . '}' . $box, $username, $pass, OP_READONLY);

        if(!empty(imap_last_error())) {
            $this->error('Ошибка соединения с почтовым ящиком: ' . imap_last_error());
        }
    }

    public function getIds($time){
        try {
            $mailsIds = imap_search($this->mbox, 'SINCE '.date("Y-m-d", $time));
        } catch (\Exception $e) {
            $this->error('Ошибка соединения с почтовым ящиком: ' . $e);
        }

        return $mailsIds;
    }

    public function getPhone($text) {
        preg_match_all('/(\+?\d*)?[\s\-\.]?((\(\d+\)|\d+)[\s\-\.]?)?(\d[\s\-\.]?){6,7}/', trim($text), $matchesphone);
        foreach ($matchesphone[0] as $k=>$v) {
            if(strripos($v, ".") != false)
                unset($matchesphone[0][$k]);
        }

        $matchesphone[0] = array_values($matchesphone[0]);

        return (!empty($matchesphone[0]) ? preg_replace("/(\s)/", "", $matchesphone[0][0]) : "");
    }

    public function getEmail($text) {
        $output = array();

        preg_match_all('~[^\s]+@[^\s]+~', trim($text), $matches);

        foreach($matches[0] as $key => $val) {
            $email = filter_var($val, FILTER_VALIDATE_EMAIL);
            if($email) {
                $output[] = $email;
            }
        }

        return (!empty($output[0]) ? $output[0] : "");
    }

    public function getBody($id) {
        $msg_body = imap_fetchbody($this->mbox, $id, 1, FT_PEEK);
        $struct = imap_fetchstructure($this->mbox, $id);

        $body = '';

        $recursive_data = $this->recursive_search($struct);

        if($recursive_data["encoding"] == 0 ||
            $recursive_data["encoding"] == 1){
            $body = $msg_body;
        }

        if($recursive_data["encoding"] == 4 || $recursive_data["encoding"] == 3 || $recursive_data["encoding"] == 2){

            $body = $this->structure_encoding($recursive_data["encoding"], $msg_body);
        }

        if(!$this->check_utf8($recursive_data["charset"])){

            $body = $this->convert_to_utf8($recursive_data["charset"], $msg_body);
        }

        if(!empty($body))
            return preg_replace("/((?=@media)(.*)(?<=}))/", "", $this->parseString($body));
        else {
            $this->error('Email id: ' . $id . '. Error: empty message.');
        }
    }

    public function getMessages(){
        $arr = ['count' => 0];
        $mass = $this->getIds($this->time);

        if(empty($mass)) {
            die("Нет статистики за выбранный период");
        }

        foreach ($mass as $mailNum) {
            $header = imap_header($this->mbox, $mailNum);
            $subject = $this->get_imap_title($header->subject);
            //print_r($header); echo $subject; die();
            $from = $header->from[0]->mailbox . "@" . $header->from[0]->host;
            $msgHour = strtotime($header->MailDate);

            if($msgHour < $this->time || strripos($subject, "MediSPA центр BIOSFERA: Новый заказ") !== false || strripos($subject, "Электронные копии сертификатов") !== false)
                continue;

            $text_mail = $this->getBody($mailNum);

            $email = $this->getEmail($text_mail);
            $tel = $this->getPhone($text_mail);

            $arr['result'][] = [
                "id" => $mailNum,
                "date" => $msgHour,
                "subject" => $subject,
                "text" => $text_mail,
                "tel" => $tel,
                "email" => $email
            ];

            $arr['count']++;
        }

        if(empty($arr['result'])) {
            die("Нет статистики за выбранный период");
        }

        return $arr;
    }

    public function setParseRule($rule, $value) {
        $this->parseRules[$rule] = $value;
    }

    public function removeParseRule($rule) {
        if (array_key_exists($rule, $this->parseRules)) {
            unset($this->parseRules[$rule]);
        }
    }

    public function parseString($string) {
        $this->setHtml($string);
        $this->parse();
        return $this->getText();
    }

    private function check_utf8($charset){

        if(strtolower($charset) != "utf-8" && strtolower($charset) != "default"){
            return false;
        }

        return true;
    }

    private function convert_to_utf8($in_charset, $str){

        return iconv(strtolower($in_charset), "utf-8", $str);
    }

    private function recursive_search($structure){

        $encoding = "";

        if($structure->subtype == "HTML" ||
            $structure->type == 0){

            if($structure->parameters[0]->attribute == "charset"){

                $charset = $structure->parameters[0]->value;
            }

            return array(
                "encoding" => $structure->encoding,
                "charset"  => strtolower($charset),
                "subtype"  => $structure->subtype
            );
        }else{

            if(isset($structure->parts[0])){

                return $this->recursive_search($structure->parts[0]);
            }else{

                if($structure->parameters[0]->attribute == "charset"){

                    $charset = $structure->parameters[0]->value;
                }

                return array(
                    "encoding" => $structure->encoding,
                    "charset"  => strtolower($charset),
                    "subtype"  => $structure->subtype
                );
            }
        }
    }

    private function get_imap_title($str){

        $mime = imap_mime_header_decode($str);

        $title = "";

        foreach($mime as $key => $m){

            if(!$this->check_utf8($m->charset)){

                $title .= $this->convert_to_utf8($m->charset, $m->text);
            } else {

                $title .= $m->text;
            }
        }

        return $title;
    }

    private function structure_encoding($encoding, $msg_body){

        switch((int) $encoding){

            case 4:
                $body = imap_qprint($msg_body);
                break;

            case 3:
                $body = imap_base64($msg_body);
                break;

            case 2:
                $body = imap_binary($msg_body);
                break;

            case 1:
                $body = imap_8bit($msg_body);
                break;

            case 0:
                $body = $msg_body;
                break;

            default:
                $body = "";
                break;
        }

        return $body;
    }

    private function parse() {
        $string = $this->getHtml();

        foreach ($this->parseRules as $rule => $output) {
            $string = preg_replace($rule, $output, $string);
        }

        $string = html_entity_decode($string);
        $string = strip_tags($string);
        $string = preg_replace('/(  *)/', ' ', $string);
        $string = preg_replace('/\n /', "\n", $string);
        $string = preg_replace('/ \n/', "\n", $string);
        $string = preg_replace('/\t /', "\t", $string);
        $string = preg_replace('/\t \n/', "\n", $string);
        $string = preg_replace('/\t\n/', "\n", $string);
        $string = preg_replace('/\n/', "\r\n", $string);

        $this->setText($string);
    }

    private function getHtml()
    {
        return $this->html;
    }

    private function setHtml($string)
    {
        $this->html = $string;
        return $this;
    }

    private function getText()
    {
        return $this->text;
    }

    private function setText($string)
    {
        $this->text = $string;
        return $this;
    }

    public function error($txt) {
        file_put_contents(__DIR__ . "/error.log", "\n\nError at " . date("Y-m-d H:i:s") . ":\n" . $txt, FILE_APPEND);
        file_put_contents(__DIR__ . "/time", $this->time);
        die($txt);
    }
}