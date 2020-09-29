<?php
require_once 'functions.php';
if (isset($_POST['msisdn']) && isset($_POST['password'])) {
    if (loginUsingPassword($_POST['msisdn'], $_POST['password'])===true) {
        $_SESSION['msisdn'] = $_POST['msisdn'];
        $_SESSION['password'] = $_POST['password'];
        if ($_SERVER['SERVER_NAME']!=="0.0.0.0") header("Location: /");
        else header("Location: /smsmotion/index.php");
    }
    exit();
}

if (!isset($_SESSION['msisdn'])||!isset($_SESSION['password'])) {
    require_once 'template/login.html';
    exit();
}
else {
    if (!isset($_GET['out'])) {$msgs = getSMS($_SESSION['msisdn'], 19700101010001, 2); $tmpl = 'template/sms/inbox.html';}
    else {$msgs = getSMS($_SESSION['msisdn'], 19700101010001, 1); $tmpl = 'template/sms/outbox.html';}
    if (!isset($msgs[0])) {$msgs = array($msgs);}
    echo "<a href=\"./send.php\">Отправить</a>";

    require_once $tmpl;
}