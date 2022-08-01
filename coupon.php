<?php
$action = $_GET['action'] ?? 'index';
$param = $_POST;

//引入composer的autoload文件
require './vendor/autoload.php';

//引入公用函数
require './common/functions.php';

//引入优惠券类
require './libs/Coupon.php';

$coupon = new Coupon($param);
$coupon->$action();