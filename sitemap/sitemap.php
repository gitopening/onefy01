<?php
require_once(dirname(dirname(__FILE__)). '/common.inc.php');
header("Content-type: text/xml");

$content = file_get_contents(dirname(dirname(__FILE__)).'/tmp/sitemap/' . $url_name . '_sitemap.xml');

$content   = trim($content , "\xEF\xBB\xBF" );
echo $content;