<?php
$iPod    = stripos($_SERVER['HTTP_USER_AGENT'],"iPod");
$iPhone  = stripos($_SERVER['HTTP_USER_AGENT'],"iPhone");
$Android = stripos($_SERVER['HTTP_USER_AGENT'],"mobile");

if( $iPod || $iPhone ){
    header('Location: https://itunes.apple.com/in/app/ishareryde/id1073560784?ls=1&mt=8');
}else if($Android){
    header('Location: https://play.google.com/store/apps/details?id=com.clubmycab');
}