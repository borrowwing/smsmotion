<?php
require_once 'functions.php';
if (!isset($_SESSION['msisdn'])||!isset($_SESSION['password'])) {
    header("Location: /");
    exit();
}
else {
    if (!isset($_POST['sms_to'])||!isset($_POST['sms_text'])) {
        require_once 'template/sms/send.html';
        exit();
    }
    else {
        $to = $_POST['sms_to'];
        $text = $_POST['sms_text'];
        $mid = sendSMS($_SESSION['msisdn'],$to,$text);
        if ($mid["@attributes"]['resultCode']=="0") {
            echo "SMS отправлено. Проверьте получателя.";
            require_once 'template/sms/inbox.html';
        }
    }
}