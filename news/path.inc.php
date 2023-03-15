<?php
require_once(dirname(dirname(__FILE__)) . '/common.inc.php');
if ($url_name != 'www') {
    header('Location://www.' . $cfg['page']['basehost'] . $_SERVER['REQUEST_URI']);
    exit();
}
$currentColumn = 'news';