<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/10
 * Time: 10:29
 */
require_once(dirname(dirname(__FILE__)) . '/common/lib/phpqrcode.php');
$url = urldecode($_GET["data"]);
$logo = dirname(dirname(__FILE__)) . '/images/qrcode/qrcode_logo.png';
$margin = $_GET['style'] == 1 ? 0 : 2;
QRcode::png($url, false, QR_ECLEVEL_Q, 8, $margin, false, $logo);