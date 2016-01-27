<?php
$page='login';
include_once('connection.php');

if ((isset($_SESSION['username']) && $_SESSION['username'] !='')){
    header('location:dashboard.php');
}

if (isset($_POST['submit']) && isset($_POST['username']) && $_POST['username'] !='' && isset($_POST['password']) && $_POST['password'] !=''){
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $sql = "SELECT id, username, password FROM clients WHERE username='$username' AND password='$password'";
    $stmt = $con->prepare($sql);
    $stmt->execute();
    $rowCount = (int) $stmt->rowCount();

    if ($rowCount > 0){
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $_SESSION['userId'] = $result['id'];
        $_SESSION['username'] = $result['username'];
        header('location:dashboard.php');
    } else {
        $_REQUEST['err']=1;
    }
}

?>

<?php
      include_once('header.php');
?>
<div class="header-login pure-u-1-1 pure-u-md-3-4">
Login
</div>
</div>
<div class="content pure-u-1-1 pure-u-md-3-4">
    <div class="pure-u-4-4" id="mainContent">
        <!-- Login form start -->
        <div>
            <div style="padding: 15px;text-align: center;">
                <form method="post" action="">
                    <div>
                        <?php if (isset($_REQUEST['err']) && $_REQUEST['err']==1){ ?>
                            <div class="divRight bluetext">Invalid Username / Password</div>
                            <div style="clear:both;"></div>
                            <br/>
                        <?php } ?>
                        <div class="divRight bluetext"><input type="text" name="username" id="username" placeholder="Username"></div>
                        <div style="clear:both;"></div>
                        <br/>

                        <div class="divRight bluetext"><input type="password" name="password" id="password" placeholder="Password">
                        </div>
                        <div style="clear:both;"></div>
                        <br/>

                        <div class="divLeft bluetext"><input type="submit" name="submit" value="Sign in" class="cBtn">
                        </div>
                        <div class="divRight bluetext"></div>
                    </div>
                </form>
            </div>

            <div style="clear:both;"></div>
        </div>
        <!-- Login end -->
    </div>
</div>
<?php
include_once('footer.php');
?>
