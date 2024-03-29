<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
    <title>iShareRyde Client Access</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="css/combinedpure.css" rel="stylesheet" type="text/css" />
    <link href="css/jquery-ui.css" rel="stylesheet" type="text/css" />
    <link href="css/custom.css" rel="stylesheet" type="text/css" />
    <link href="css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link href="css/pagination.css" rel="stylesheet" type="text/css" />
    <script src="js/jquery.min.js" type="text/javascript"></script>
    <!-- calender -->
    <link rel="stylesheet" type="text/css" media="all" href="Calendar/calendar-blue.css" title="win2k-cold-1">
    <script type="text/javascript" src="Calendar/calendar.js"></script>
    <script type="text/javascript" src="Calendar/lang/calendar-en.js"></script>
    <script type="text/javascript" src="Calendar/calendar-setup.js"></script>
</head>
<body>
<div id="layout" class="pure-g">
    <div class="header pure-u-1-1 pure-u-md-3-4">
        <div style="padding:10px;">
            <div style="text-align: left;width: 50%;float: left;">
                <a href="index.php">
                    <img src="logo/ishareryde.png" alt="iShareRyde"
                         title="iShareRyde" border="0" class="pure-img-responsive-home"/>
                </a>
            </div>
            <div style="text-align: right;width: 50%; float: right;">
                <?php if($_SESSION['logo']){ ?>
                <img src="logo/<?=$_SESSION['logo'];?>" alt=""
                     title="" border="0" class="pure-img-responsive-home" onerror="this.src=''"/>
                <?php } else { ?>
                    <img src="" alt="" title="" border="0" class="pure-img-responsive-home"/>
                <?php } ?>

                
            </div>

        </div>