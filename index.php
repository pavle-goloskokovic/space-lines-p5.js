<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <!-- charset must remain utf-8 to be handled properly by Processing -->
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>KestenPire with Processing</title>
    <link rel="shortcut icon" href="favicon.ico">
    <link rel="stylesheet" href="css/style.css" type="text/css" media="screen" charset="utf-8">
    <script type="text/javascript">
        var _gaq = _gaq || [];
        _gaq.push(['_setAccount', 'UA-29779590-1']);
        _gaq.push(['_trackPageview']);

        (function() {
            var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
        })();
    </script>
</head>
<body>
<div id="fb-root"></div>
<script>(function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));</script>
<?php

$img_url = "default.jpg";
$img_url_thumb = "default_thumb.jpg";
$ime_prezime = "Kesten Pire";

//$con = mysql_connect('mysql204.loopia.se', 'processing@k5834', 'prcsng.123');
$con = mysql_connect('localhost', 'root', '');
if ($con) {
    //mysql_select_db("kestenpire_com", $con);
    mysql_select_db("processing", $con);

    if(isset($_GET["p"])){

        $id = $_GET["p"];

        if (is_numeric($id) && is_int($id + 0)) {

            //echo $id . " == int";
            $result = mysql_query( "SELECT img_url, ime FROM processing WHERE id = ".$id );
            $row = mysql_fetch_array($result);
            if($row){
                $img_url = $row['img_url'];
                $img_url_thumb =  substr($img_url,0,strrpos($img_url,".")) . "_thumb.jpg";
                $ime_prezime = $row['ime'];
            }
        }//else echo $id . " != int";

    }else{

        $offset_result = mysql_query( "SELECT FLOOR(RAND() * COUNT(*)) AS offset FROM processing");
        $offset_row = mysql_fetch_object( $offset_result );
        $offset = $offset_row->offset;
        $result = mysql_query( "SELECT img_url, ime FROM processing LIMIT $offset, 1" );

        $row = mysql_fetch_array($result);
        //echo $row['img_url'];
        $img_url = $row['img_url'];
        $img_url_thumb =  substr($img_url,0,strrpos($img_url,".")) . "_thumb.jpg";
        $ime_prezime = $row['ime'];
    }

    mysql_close($con);
}
?>
<div id="content">
    <h1 id="heading">
        <a href="http://processing.kestenpire.com">
            KestenPire
            <br />
            with Processing
        </a>
    </h1>
    <?php if(!isset($_GET["p"])){ ?>
    <div id="headline">
        Want to see your images get generated from nothing but colored lines...
        and want it to take place in space?!?
        We got just the thing for you!
    </div>
    <?php } ?>
    <div id="processing_container">
        <canvas id="spaceLines" data-processing-img="<?php echo $img_url; ?>"
                width="500" height="500">
            Your browser doesn't support canvas element... so old school!
        </canvas>
        <div id="status-wrapper">
            <span id="status">Initialising...</span> | <a id="save" href="javascript:void(0);"> Save image</a>
        </div>
        <div id="social">
            <a href="https://twitter.com/share" class="twitter-share-button" data-dnt="true">Tweet</a>
            <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
            <div class="fb-like" data-href="http://processing.kestenpire.com/" data-width="The pixel width of the plugin" data-height="The pixel height of the plugin" data-colorscheme="light" data-layout="button_count" data-action="like" data-show-faces="true" data-send="false"></div>
            <div class="g-plusone" data-size="medium"></div>
        </div>
    </div>
    <div id="info" class="clearfix">
        <div id="uploader">
            <img id="thumb" src="<?php echo $img_url_thumb; ?>" width="50" height="50" alt="Thumbnail"/>
            Uploaded by
            <br/>
            <div id="uploader-name">
                <?php echo $ime_prezime; ?>
            </div>
        </div>
        <div id="upload">
            <a href="upload">
                Upload your own image
            </a>
        </div>
    </div>
    <div id="contact" class="clearfix">
        For more info contact us at: <div id="mail"></div>
    </div>
    <div id="footer">
        Copyright © 2012-<?php echo date("Y");?> KestenPire. All rights reserved.
    </div>
</div>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/processing.js/1.4.1/processing-api.min.js"></script>
<script type="text/javascript">
    if(typeof Processing == 'undefined')document.write(decodeURI("%3Cscript%20type=%22text/javascript%22%20src=%22js/processing-1.4.1.min.js%22%3E%3C/script%3E"));
</script>
<script type="text/javascript" src="js/main.js"></script>
<script type="text/javascript">
    (function() {
        var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
        po.src = 'https://apis.google.com/js/plusone.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
    })();
</script>
</body>
</html>

