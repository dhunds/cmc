<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
	<title>ClubMyCab Admin</title>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="css/combinedpure.css" rel="stylesheet" type="text/css" />
    <link href="css/jquery-ui.css" rel="stylesheet" type="text/css" />
	<link href="css/custom.css" rel="stylesheet" type="text/css" />
    <link href="css/pagination.css" rel="stylesheet" type="text/css" />
	 <script src="js/jquery.min.js" type="text/javascript"></script>
</head>
<body>
	<?php include('connection.php');?>
	<div id="layout" class="pure-g">
        <div class="header pure-u-1-1 pure-u-md-3-4">
            <div style="padding:5px;">
                <a href="index.php">
                    <img src="images/logo.png" alt="ClubMyCab"
                        title="ClubMyCab" border="0" class="pure-img-responsive-home"/>
                </a>
            </div>
           <?php include('topmenu.php');?>
        </div>
        <div class="content pure-u-1-1 pure-u-md-3-4">
            <div class="pure-u-4-4" id="mainContent">
                <?php include($page_content);?>
            </div>
        </div>
        <div class="footer pure-u-1-1 pure-u-md-3-4">
            <ul>
                <li>&copy; 2015 ClubMyCab</li>
            </ul>
        </div>
    </div>
</body>
</html>
<script type="text/javascript">
    function wrapperResize() {
        $(".content").css("height", "auto");
        var winHT = document.documentElement.clientHeight;
        var footerHT = $(".footer").outerHeight(true);
        var headerHT = $(".header").outerHeight(true);;
        var wrapperHT = $(".content").outerHeight(true);
        if (winHT > (wrapperHT + footerHT + headerHT)) {
            $(".content").height(winHT - footerHT - headerHT);
        }
    }
    function resizeMe() {
        if (parseInt($("body").width()) < 801) {
            $("#mainRight").removeClass('pure-u-1-5').addClass('pure-u-1-1');
            $("#mainContent").removeClass('pure-u-3-4').addClass('pure-u-1-1');
            $("#demo-horizontal-menu > ul").css("display", "none");
            var menuDown = false;
            $("#demo-horizontal-menu").click(function (e) {
                if (!menuDown) {
                    $("#demo-horizontal-menu > ul").slideDown(500);
                }
                else {
                    $("#demo-horizontal-menu > ul").slideUp(500);
                }
                menuDown = !menuDown;
            });
        }
        else if (parseInt($("body").width()) >= 801) {
            $("#mainRight").removeClass('pure-u-1-1').addClass('pure-u-1-5');
            $("#mainContent").removeClass('pure-u-1-1').addClass('pure-u-4-4');
            $("#demo-horizontal-menu > ul").css("display", "block");
        }
        wrapperResize();
    }

    $(document).ready(function () {
        resizeMe();
        wrapperResize();
        $("ul#std-menu-items li > ul").each(function () {
            var parent = $(this).parent("li");
            parent.addClass("parent").find("> a").append("<span class='arrow-down'></span>");
        });
        $("ul#std-menu-items li > ul li > ul").each(function () {
            var parent = $(this).parent("li");
            parent.addClass("parent").find("> a span").removeClass("arrow-down").addClass("arrow-right");
            $(this).css({"left": parent.parent("ul").width() - 25 + "px"});
        });
        $("a").each(function () {
            if ($(this).attr("href") == "#") {
                $(this).click(function (ev) {
                    ev.preventDefault();
                    ev.stopPropagation();
                });
            }
        });
    });
    window.onresize = resizeMe;
</script>