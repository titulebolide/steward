<!DOCTYPE html>
<html>
<head>
  <link href="lib/video-js.css" rel="stylesheet">
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

        var src = "testavi.avi"

        var urlAPI = "http://127.0.0.1:5000/"

        var video= videojs('video');

        var killerTimeout = 10;

        let id = makeid(10)

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
            id = makeid(10)
            video.pause()
            //video.load()
            $.get(urlAPI + "transcode/" + encodeURIComponent(src) + "/" + time + "/" + id)
            video.src("hls/" + id + ".m3u8");
            video.play();
        };
        $.get(urlAPI + 'duration/' + encodeURIComponent(src), function(rep){
          video.theDuration= parseInt(rep);
        })
        $.get(urlAPI + "transcode/" + encodeURIComponent(src) + "/0/" + id)
        setTimeout(function () {
          video.src("hls/" + id + ".m3u8");
          video.play();
        }, 1000);

        function keepAlive() {
          $.get(urlAPI + 'keepalive/' + id)
          setTimeout(keepAlive, killerTimeout*1000)
        }

        keepAlive()
    </script>
</body>
