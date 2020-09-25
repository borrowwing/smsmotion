<?php
require_once 'functions.php';
if (isset($_POST['msisdn']) && isset($_POST['password'])) {
    if (loginUsingPassword($_POST['msisdn'], $_POST['password'])===true) {
        $_SESSION['msisdn'] = $_POST['msisdn'];
        $_SESSION['password'] = $_POST['password'];
        header("Location: /");
    }
    exit();
}

if (!isset($_SESSION['msisdn'])||!isset($_SESSION['password'])) {
    require_once 'template/login.html';
    exit();
}
else {
    $msgs = getSMS($_SESSION['msisdn']);
    if (!isset($msgs[0])) {$msgs = array($msgs);}
    require_once 'template/sms/index.html';
}