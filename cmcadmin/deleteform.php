<html>
<head>
<script>
function myFunction() {
    var x;
    if (confirm("Do you Really Want to Delete !!") == true) {
        x = "You pressed OK!";
    } else {
        x = "You pressed Cancel!";
    }
    
}
</script>
</head>
<body>
 
<input type="button" name="submit"  onclick="myFunction()" value="Delete" />
 
</body>
</html>
<?php
if(isset($_POST['submit']))
   {
    

   } 

?>