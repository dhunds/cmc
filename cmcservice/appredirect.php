<?php

$iPod    = stripos($_SERVER['HTTP_USER_AGENT'],"iPod");
$iPhone  = stripos($_SERVER['HTTP_USER_AGENT'],"iPhone");
$Android = stripos($_SERVER['HTTP_USER_AGENT'],"Android");

if( $iPod || $iPhone ){
    echo 'Iphone app comming soon!';die;
    //header('Location: https://play.google.com/store/apps/details?id=com.clubmycab');
}else if($Android){
    header('Location: https://play.google.com/store/apps/details?id=com.clubmycab');
}