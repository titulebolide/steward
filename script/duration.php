<?php
if (!isset($_GET['src'])){
  return;
}

$src = $_GET['src'];

exec("ffmpeg -i $src 2>&1", $res);
$match = 'Duration';
foreach ($res as $line) {
  if (substr($line, 2, strlen($match)) === $match){
    preg_match("/Duration: (..):(..):(..).(..)/", $line, $durations);
    $duration = 3600*$durations[1]+60*$durations[2]+$durations[3]+1;
    echo $duration;
  }
}
