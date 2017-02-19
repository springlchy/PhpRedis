<?php

require "../src/MyRedisClient.php";

$redis = new MyRedisClient();

$result = $redis->connect();

if ($result === false) {
	exit("Connect to redis failed" . PHP_EOL);
	echo $redis->getLastError();
	
} else {
	echo "connect to redis success";
}

$redis->close();