--TEST--
mysqli_connect()
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');

if (!$IS_MYSQLND)
	die("skip: test applies only to mysqlnd");

if (!$link = my_mysqli_connect($host, $user, $passwd, $db, $port, $socket))
	die("skip Cannot connect to the server");

/* skip test for mysql versions <= 5.5*/
if (!$res = mysqli_query($link, "select version() as version;")) 
    die("skip select version() query failed");

$tmp = mysqli_fetch_assoc($res);
mysqli_free_result($res);
$version = explode(".", $tmp['version']);
if($version[0]<5 || ($version[0]==5 && $version[1]<=5)) {
    mysqli_close($link);
    die("skip mysql does not support session_connect_attrs table yet");
}
mysqli_close($link);
?>
--FILE--
<?php
	require_once("connect.inc");

	$tmp    = NULL;
	$link   = NULL;
    $res    = NULL;
	if (!$link = mysqli_connect($host, $user, $passwd, $db, $port, $socket))
		printf("[001] Cannot connect to the server using host=%s, user=%s, passwd=***, dbname=%s, port=%s, socket=%s\n",$host, $user, $db, $port, $socket);
    
    if (!$res = mysqli_query($link, "select * from performance_schema.session_connect_attrs where ATTR_NAME='_server_host' and processlist_id = connection_id()")) {
        printf("[002] [%d] %s\n", mysqli_errno($link), mysqli_error($link));
    }
    else {
        $tmp = mysqli_fetch_assoc($res);
        mysqli_free_result($res);
        if ($tmp['ATTR_VALUE'] !== $host) {
        printf("[003] _server_host value mismatch\n") ;
        }
    }

    if (!$res = mysqli_query($link, "select * from performance_schema.session_connect_attrs where ATTR_NAME='_client_name' and processlist_id = connection_id()")) {
        printf("[004] [%d] %s\n", mysqli_errno($link), mysqli_error($link));
    }
    else {
        $tmp = mysqli_fetch_assoc($res);
        mysqli_free_result($res);
        if ($tmp['ATTR_VALUE'] !== "mysqlnd") {
            printf("[005] _client_name value mismatch\n") ;
        }
    }

    printf("done!");
?>
--EXPECTF--
done!
