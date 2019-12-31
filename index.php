<!DOCTYPE html>
<html>
<head>
  <link href="lib/video-js.css" rel="stylesheet">

  <!--
    -- Include video.js and videojs-contrib-hls in your page
    -->

  <script src="lib/video.js"></script>
  <script src="lib/videojs-flash.js"></script>
  <script src="lib/videojs-contrib-hls.js"></script>

    <script src="lib/jquery-1.9.1.min.js"></script>
</head>
<body>
    <video-js id="video" class="video-js vjs-default-skin" controls preload="auto" width="640" height="264">

   </video-js>
    <script>
        function makeid(length) {
        var result           = '';
        var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        var charactersLength = characters.length;
        for ( var i = 0; i < length; i++ ) {
          result += characters.charAt(Math.floor(Math.random() * charactersLength));
        }
        return result;
        }

        var src = "/home/titus/tst2/testavi.avi"

        var video= videojs('video');


        // hack duration
        video.duration= function() { return video.theDuration; };
        video.start= 0;
        video.oldCurrentTime= video.currentTime;
        video.currentTime= function(time)
        {
            if( time == undefined )
            {
                return video.oldCurrentTime() + video.start;
            }
            video.start= time;
            video.oldCurrentTime(0);
            var id = makeid(10)
            video.pause()
            //video.load()
            $.get("script/transcode.php?src=" + src + "&ss=" + time + "&id=" + id)
            video.src("hls/" + id + ".m3u8");
            video.play();
        };
        $.get('script/duration.php?src=' + src, function(rep){
          video.theDuration= parseInt(rep);
        })
        var id = makeid(10)
        $.get("script/transcode.php?src=" + src + "&ss=0&id=" + id)
        setTimeout(function () {
          video.src("hls/" + id + ".m3u8");
          video.play();
        }, 1000);

    </script>
</body>
