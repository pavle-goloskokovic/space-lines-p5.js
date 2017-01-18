<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>KestenPire with Processing Upload</title>
    <link rel="shortcut icon" href="../favicon.ico">
    <link rel="stylesheet" href="../css/style.css" type="text/css" media="screen" charset="utf-8">
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
<div id="content">
<h1 id="heading">
    <a href="../">
        KestenPire
        <br />
        with Processing
    </a>
</h1>
<?php

session_start();

define ("MAX_SIZE","200");//kb

$ck_email = "/^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i";
$allowedfiletypes = array("gif","jpeg","jpg","png");

$db_uploadfolder =  "images/";
$uploadfolder = "../".$db_uploadfolder;
$thumbnailheight = 50; //in pixels

$ime = "";
$email = "";
$fileurl = "";
$fileext = "";
$code = "";

$imeAlert = "";
$emailAlert = "";
$imageAlert = "";
$codeAlert = "";

$previewlink = "";
$id = "";

$result = false;
$db_success = false;

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $result = true;

    if(isset($_POST['ime'])){
        $ime = $_POST['ime'];
        if(empty($ime)){
            $imeAlert = "You must enter your full name!";
            $result = false;
        }else if(strlen($ime)>80){
            $imeAlert = "Full name must not be longer than 80 characters!";
            $result = false;
        }
    }else{
        $imeAlert = "You must enter your full name!";
        $result = false;
    }

    if(isset($_POST['email'])){
        $email = $_POST['email'];
        if(empty($email)){
            $emailAlert = "You must enter your email address!";
            $result = false;
        }else if(strlen($email)>80){
            $imeAlert = "Email must not be longer than 80 characters!";
            $result = false;
        }else if (!preg_match($ck_email, $email)) {
            $emailAlert = "You must enter a valid email address!";
            $result = false;
        }
    }else{
        $emailAlert = "You must enter your email address!";
        $result = false;
    }

    if(empty($_FILES['image']['name'])){
        $imageAlert = "You must choose your image!<br/>";
        $result = false;
    } else {

        $fileurl = $_FILES['image']['name'];
        $fileext = strtolower(substr($fileurl,strrpos($fileurl,".")+1));

        //get the size of the image in bytes
        //$_FILES['image']['tmp_name'] is the temporary filename of the file
        //in which the uploaded file was stored on the server
        $size = filesize($_FILES['image']['tmp_name']);

        if (!in_array($fileext,$allowedfiletypes)) {
            $imageAlert = "Wrong file extension!<br/>";
            $result = false;
        } else if ($size > MAX_SIZE*1024){
            $imageAlert = "Image size must not be larger than 200kb!<br/>";
            $result = false;
        }
    }

    if(isset($_POST['norobot'])){
        $code = $_POST['norobot'];
        if(empty($code)){
            $codeAlert = "You must enter security code!";
            $result = false;
        }else if (md5($code) != $_SESSION['randomnr2'])	{
            $codeAlert = "Wrong security code! You're a very naughty robot!";
            $result = false;
        }
    }else{
        $codeAlert = "You must enter security code!";
        $result = false;
    }

    $_SESSION['randomnr2']=null;

}

if($result){

    //echo "Passed validation!";

    /* Create a new mysqli object with database connection parameters */
    //$mysqli = new mysqli('mysql204.loopia.se', 'processing@k5834', 'prcsng.123', 'kestenpire_com');
    $mysqli = new mysqli('localhost', 'root', '', 'processing');

    if(!mysqli_connect_errno()) {

        $tempfilename = $_FILES['image']['tmp_name'];

        //echo $tempfilename . "<br/>" . $fileurl;

        $upit = "INSERT INTO processing ( ime, email, img_url, img_name, entered_time ) VALUES ( ?, ?, ?, ?, NOW() )";

        /* Create a prepared statement */
        if($stmt = $mysqli -> prepare($upit)) {

            /* Bind parameters
               s - string, b - boolean, i - int, etc */
            $stmt -> bind_param("ssss", $ime, $email, $tempfilename, $fileurl);

            /* Execute it */
            $stmt -> execute();

            /* Close statement */
            $stmt -> close();

            $upit = "SELECT id FROM processing WHERE img_url = ?";

            if($stmt = $mysqli -> prepare($upit)) {
                $stmt -> bind_param("s", $tempfilename);

                $stmt->execute();

                /* Bind results to variables */
                $stmt->bind_result($id);

                /* fetch values */
                if ($stmt->fetch()) {

                    //echo "\n" . $id . "\n";

                    /* Close the statement */
                    $stmt->close();

                    ////

                    $fulluploadfilename = $uploadfolder.$id.".".$fileext;
                    $db_fulluploadfilename = $db_uploadfolder.$id.".".$fileext;
                    $thumbfilename = $uploadfolder.$id."_thumb.jpg";
                    //echo $fulluploadfilename . "\n" . $thumbfilename;

                    if (move_uploaded_file($_FILES['image']['tmp_name'], $fulluploadfilename)) {

                        switch ($fileext) {
                            case "jpg":
                            case "jpeg":
                                $im = imagecreatefromjpeg($fulluploadfilename);
                                break;
                            case "gif":
                                $im = imagecreatefromgif($fulluploadfilename);
                                break;
                            case "png":
                                $im = imagecreatefrompng($fulluploadfilename);
                                break;
                        }

                        if ($im) {

                            $imw = imagesx($im); // uploaded image width
                            $imh = imagesy($im); // uploaded image height
                            $nh = $thumbnailheight; // thumbnail height
                            //$nw = round(($nh / $imh) * $imw); //thumnail width
                            $newim = imagecreatetruecolor ($nh, $nh);
                            imagecopyresampled ($newim,$im, 0, 0, 0, 0, $nh, $nh, $imw, $imh) ;
                            if(imagejpeg($newim, $thumbfilename)){

                                $upit = "UPDATE processing SET img_url = ? WHERE id = ?";

                                if($stmt = $mysqli -> prepare($upit)) {

                                    $stmt -> bind_param("si", $db_fulluploadfilename, $id);

                                    /* Execute second Query */
                                    $stmt->execute();

                                    /* Close the statement */
                                    $stmt->close();

                                    $db_success = true;

                                    $previewlink = "http://processing.kestenpire.com/?p=".$id;
                                }
                            }
                        }
                    }

                    ////

                }else{
                    /* Close the statement */
                    $stmt->close();
                }
            }
        }

        /* Close connection */
        $mysqli -> close();
    }
}
?>

<?php if (!$result) { ?>
    <div id="form">
        <h2>Upload image</h2>
        <br/>
    <span class="description"> Your email address will not be published. <br/>
    Required fields are marked <span class="form_required" >*</span> </span> <br/>
        <br/>
        <form id="uploadform" name="uploadform" action="" method="post" enctype="multipart/form-data" onSubmit="return alertMessage()">
            <label class="form_field" for="ime">Your Full Name</label>
            <label class="form_required" >*</label>
            <br/>
            <input type="text" name="ime" id="ime" value="<?php echo $ime; ?>" class="text_box" maxlength="80">
            <br/>
            <label class="alert" id="nameErr"><?php echo $imeAlert; ?></label>
            <br/>
            <label class="form_field" for="email">Email Address</label>
            <label class="form_required" >*</label>
            <br/>
            <input type="text" name="email" id="email" value="<?php echo $email; ?>" class="text_box" maxlength="80">
            <br/>
            <label class="alert" id="emailErr"><?php echo $emailAlert; ?></label>
            <br/>
            <label class="form_field">Upload your image</label>
            <label class="form_required" >*</label>
            <br/>
            <input type="file" name="image" id="image" value="" class="text_box">
            <br/>
            <label class="alert" id="imgErr"><?php echo $imageAlert; ?></label>
            <label class="description"> For best results, use a square image. <br/>
                Max allowed file size is 200 kb.<br/>
                Supported extensions are .gif, .jpg, .png. </label>
            <br/>
            <br/>
            <label class="form_field">Security Code:</label>
            <label class="form_required" >*</label>
            <br/>
            <img src="captcha.php" /> <br/>
            <input class="input" type="text" name="norobot" size="14"/>
            <br/>
            <label class="alert" id="codeErr"><?php echo $codeAlert; ?></label>
            <br/>
            <br/>
            <input type="submit" id="submit" name="submit" value="Upload image" />
            <br/>
            <label class="description" id="message"></label>
        </form>
    </div>
<?php } else if(!$db_success) { ?>
    <div id="error">
        <h2>Oops!</h2>
        <p>
            It looks like an error occurred while trying to upload your image.
            <br/>
            Please try again later.
            <br/><br/>
            <a href="../">Go back to generator.</a>
        </p>
    </div>
<?php } else { ?>
    <div id="thankYou">
        <h2>Thank you for your upload!</h2>
        <p>
            You can observe your image being generated <a href="../?p=<?php echo $id; ?>">here</a>.
            <br/><br/>
            Save link for later observation
            <textarea rows="3" cols="44" readonly="readonly"><?php echo trim($previewlink); ?></textarea>
        </p>
    </div>
<?php } ?>

<div id="footer">
    Copyright © 2012-<?php echo date("Y");?> KestenPire. All rights reserved.
</div>
</div>
<script type="text/javascript" src="../js/upload.js"></script>
</body>
</html>