<?php
session_start();
if (!isset($_SESSION['key'])) $_SESSION['key'] = md5(mt_rand(0,20000).mt_rand(0,10000).mt_rand(0,90000));
echo "<pre>".print_r($_SESSION,true)."</pre>";
function curl($method, $data = array(), $custom_prepend = "https://emotion.megalabs.ru/api/v15/") {
	global $_SESSION;
    $ch = curl_init();
    $settings = array(
        CURLOPT_URL => "{$custom_prepend}{$method}",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_PROXY => "socks5://127.0.0.1:9050",
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_HTTPHEADER => array(
          "Content-Type: {$data['type']}",
        ),
	CURLOPT_HTTPHEADER => array(
          "Content-Type: application/json",
          "X-Application: ru.megalabs.multifon",
          "X-Application-Id: f88f423",
          "X-Os: android",
          "X-Version: 4.1.1"
	),
        CURLOPT_COOKIEFILE => "/tmp/{$_SESSION['key']}.cookie",
        CURLOPT_COOKIEJAR => "/tmp/{$_SESSION['key']}.cookie",
    );
    if (!empty($data['data'])) {
        $settings[CURLOPT_POSTFIELDS] = $data['data'];
    }    
    curl_setopt_array($ch, $settings);
    $e = curl_exec($ch);
    return $e;
}

function getCode($msisdn) {
    $resp = curl("ident/{$msisdn}",["type"=>"application/json"]);
    $c = json_decode($resp,true);
    if ($c['code']=='0') return true;
    else {echo "Ошибка №{$c['code']} при ident"; die();}
}

function checkCode($msisdn, $code) {
    $resp = curl("verify",["type"=>"application/json","data"=>json_encode(["code"=>$code, "msisdn"=>$msisdn])]);
    $c = json_decode($resp,true);
    if ($c['code']=='0') return true;
    else {echo "Ошибка №{$c['code']} при verify"; die(PHP_EOL.print_r($scope,true));}
}

function getPasswordAfterThat($msisdn) {
    $resp = curl("login",["type"=>"application/json",'data'=>json_encode(["msisdn"=>$msisdn])]);
    $c = json_decode($resp,true);
    if ($c['code']=='0') return $c['password'];
    else {echo "Ошибка №{$c['code']} при login"; die();}
}

/* sms interface */
function loginUsingPassword($msisdn, $password) {
    $resp = curl("login",["type"=>"application/json","data"=>json_encode(["msisdn"=>$msisdn, "password"=>$password])]);
    $c = json_decode($resp,true);
    if ($c['code']=='0' && $c['password'] == $password) return true;
    else {echo "Ошибка {$c['code']} при авторизации."; die();}
}

function getSMS($msisdn, $timepicker = 19700101010001, $ctlg = 2, $dFlg = 0, $eNum = 100) {
    $ar = [
        "account"=>$msisdn, 
        "bTime"=>$timepicker, 
        "bNum"=>"1",
        "ctlg"=>$ctlg, 
        "dFlg"=>$dFlg, 
        "eNum"=>$eNum, 
        "impt"=>"-1",
        "lType"=>"0",
        "mType"=>6213, 
        "numFlg"=>"0", 
        "srtDr"=>"0",
        "srt"=>"0",
        "t"=>""
    ];
    $data = array2xml("getUniMsg", $ar);
    $resp = curl("msg",["type"=>"application/xml","data"=>$data]);
    $class = new SimpleXMLElement($resp);
    $detox = xml2array($class);
    return $detox['uniMsgSet']['msgLst']['uniMsg'];
}
function sendSMS($from, $to, $text) {
    $data = [
        "sendSMSParam" => [
            "attime"=>"",
            "cpId"=>"",
            "dlvType"=>"0",
            "dispType"=>"0",
            "flashflag"=>"0",
            "ctn"=>$text,
            "recver"=>[
                "item"=>$to
            ],
            "sender"=>$from,
            "serviceType"=>"",
            "sign"=>"",
            "smstype"=>"1",
            "type"=>"0"
        ]
    ];
    $a2x = array2xml("sendSMS", $data);
    return xml2array(new SimpleXMLElement(curl("msg",["type"=>"application/xml","data"=>$a2x])));

}
function array2xml($start, $data = array()) {
    if ($start!=="none") $begin = "<{$start}>".PHP_EOL;
    foreach($data as $k=>$v) {
        if (is_array($v)) $v = array2xml("none", $v);
        /* антибаг от smsок */
        if ($k == "recver") $begin .= "<{$k} length=\"".count($v)."\">{$v}</{$k}>".PHP_EOL;
        else $begin .= "<{$k}>{$v}</{$k}>".PHP_EOL;
    }
    if ($start!=="none") $begin .= "</{$start}>";
    return $begin;
}
function xml2array($data) {
    if (is_object($data)) $data = get_object_vars($data);
    return (is_array($data)) ? array_map(__FUNCTION__,$data) : $data;
}

function getBalance($msisdn, $password) {
    $resp = file_get_contents("https://emotion.megalabs.ru/sm/client/balance?login={$msisdn}@multifon.ru&password={$password}");
    $xml = new SimpleXMLElement($resp);
    $x2a = xml2array($xml);
    return $x2a['balance'];
}
?>
