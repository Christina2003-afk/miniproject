<?php
include("dbconfig.php");
session_start();
$name=$_POST["uname"];
$email=$_POST["email"];
$password=$_POST["password"];
// die('Success');
$inn="insert into table_reg(name,email,password)values('$name','$email','$password')";
if(mysqli_query($conn,$inn))
{
    header("Location: http://localhost/baker/login.html");
    exit();}
?>