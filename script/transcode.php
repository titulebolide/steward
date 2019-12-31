<?php
if (!isset($_GET['src']) || !isset($_GET['ss']) || !isset($_GET['id'])){
  return;
}

$src = $_GET['src'];
$start = $_GET['ss'];
$id = $_GET['id'];

$cmd = "killall ffmpeg ; cd ../hls ; rm *.ts ; rm *.m3u8 ; echo '#EXTM3U' >> $id.m3u8 ; ffmpeg -ss $start -i $src -b:v 1M -g 60 -hls_time 2 -hls_list_size 0 -hls_segment_size 500000 $id.m3u8";
$outputfile = 'ffmpeg.out';
$pidfile = 'ffmpeg.pid';

shell_exec(sprintf("%s > %s 2>&1 & echo $! >> %s", $cmd, $outputfile, $pidfile));
