<div class="footer pure-u-1-1 pure-u-md-3-4">
    <ul>
        <li>&copy; 2016 iShareRyde</li>
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