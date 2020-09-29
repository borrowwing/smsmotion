<?php
require_once 'functions.php';
if (isset($_POST['msisdn']) && isset($_POST['password'])) {
    if (loginUsingPassword($_POST['msisdn'], $_POST['password'])===true) {
        $_SESSION['msisdn'] = $_POST['msisdn'];
        $_SESSION['password'] = $_POST['password'];
        header("Location: /balance.php");
    }
    exit();
}

if (!isset($_SESSION['msisdn'])||!isset($_SESSION['password'])) {
    require_once 'template/login.html';
    exit();
}
else {
    $gb = getBalance($_SESSION['msisdn'],$_SESSION['password']);
    echo "Ваш баланс: {$gb} руб.";
}