function alertMessage() {

    var ck_email = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;

    var ime = document.uploadform.ime.value;
    var email = document.uploadform.email.value;
    var fileurl = document.uploadform.image.value;
    var fileext = (String)(/[^.]+$/.exec(fileurl)).toLowerCase();
    var code =  document.uploadform.norobot.value;

    var result = true;

    if (ime == ""){
        document.getElementById('nameErr').innerHTML = "You must enter your full name!";
        result = false;
    } else
        document.getElementById('nameErr').innerHTML = " ";


    if (email == ""){
        document.getElementById('emailErr').innerHTML = "You must enter your email address!";
        result = false;
    }else if (!ck_email.test(email)){
        document.getElementById('emailErr').innerHTML = "You must enter a valid email address!";
        result = false;
    }else
        document.getElementById('emailErr').innerHTML = " ";

    if (fileurl == "") {
        document.getElementById('imgErr').innerHTML = "You must choose your image!<br/>";
        result = false;
    }else if (fileext != "gif"
        && fileext != "jpeg"
        && fileext != "jpg"
        && fileext != "png" ){
        document.getElementById('imgErr').innerHTML = "Wrong file extension!<br/>";
        result = false;
    }else
        document.getElementById('imgErr').innerHTML = " ";

    if (code == "") {
        document.getElementById('codeErr').innerHTML = "You must enter security code!";
        result = false;
    }else
        document.getElementById('codeErr').innerHTML = " ";

    if(result){
        document.uploadform.submit.disabled = true;
        document.getElementById('message').innerHTML = "Please be patient, this may take some time to upload...";
    }

    return result;
}