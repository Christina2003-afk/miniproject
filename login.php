<?php
include("dbconfig.php");
session_start();
$email=$_POST["email"];
$password=$_POST["password"];
// die($password);
$_SESSION["email"]=$email;
$userCheckQuery = "select * from table_reg where password='$password' and email ='$email'";
if(mysqli_query($conn,$userCheckQuery))
{
    header("Location: http://localhost/baker/index.php");
    exit();
}
else
{
    alert("email or password is incorrect");
}
?>