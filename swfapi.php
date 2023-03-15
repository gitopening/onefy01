&curveKey=price&
<?
require_once(dirname(__FILE__) . '/config.cfg.php');


$dbh = mysql_connect($cfg['db']['host'],$cfg['db']['user'],$cfg['db']['password']); 
mysql_select_db($cfg['db']['name']); 

$query = "select * from fke_housesell_trend order by time desc limit 6"; 


$res = mysql_query($query, $dbh); 


$i=6;
while (($row = mysql_fetch_array($res)))
{
$i--;

echo "&num".$i."=".$row["number"];

}
echo "&\r\n";


$res = mysql_query($query, $dbh); 
$i=6;
while (($row = mysql_fetch_array($res)))
{
$i--;

echo "&price".$i."=".$row["price"];

}
echo "&\r\n";


$res = mysql_query($query, $dbh); 
$i=6;
while (($row = mysql_fetch_array($res)))
{
$i--;

echo "&area".$i."=".$row["area"];

}
echo "&\r\n";


$res = mysql_query($query, $dbh); 
$i=6;
while (($row = mysql_fetch_array($res)))
{
$i--;

echo "&date".$i."=".$row["time"];

}
echo "&";



?>
