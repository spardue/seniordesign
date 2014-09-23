<?php
/*
   Header file that all UI files should include 
*/
require_once('../loginsystem/config/config.php');
require_once('../loginsystem/translations/en.php');
require_once('../loginsystem/libraries/PHPMailer.php');
require_once('../loginsystem/classes/Login.php');
require_once('../config.php');
require_once('../DBInteraction.php');
require_once('../util.php');

$login = new Login();


smart_redirect("login.php", function () {
    return isset($_SESSION["user_name"]) == false;
});
//smart_redirect("admin.php", function () { return $_SESSION["user_name"] === "admin"; });


$db = new DBInteraction();
$conn = $db->getConn();

require_once('../util.php');
require_once('Comment.php');



?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
    <meta content="utf-8" http-equiv="encoding">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">


    <script src="js/jquery.js"></script>
    <script src="bootstrap/js/bootstrap.js"></script>
    <script src="js/jquery.blockUI.js"></script>
    <script src="js/bootstrap-datepicker.js"></script>
    <script>
        //thanks http://stackoverflow.com/questions/7862233/twitter-bootstrap-tabs-go-to-specific-tab-on-page-reload
        // Javascript to enable link to tab
        $(function () {
            var url = document.location.toString();
            if (url.match('#')) {
                $('.nav-tabs a[href=#' + url.split('#')[1] + ']').tab('show');
            }

            // Change hash for page-reload
            $('.nav-tabs a').on('shown', function (e) {
                window.location.hash = e.target.hash;
            })

            $('.input-group.date').datepicker();
        });

    </script>

    <!-- <link rel="shortcut icon" href="../../assets/ico/favicon.ico"> -->

    <title>VA Claim Helper</title>

    <!-- Bootstrap core CSS -->
    <link href="bootstrap/css/bootstrap.css" rel="stylesheet">
    <link href="css/datepicker.css" rel="stylesheet">
    <!-- Custom styles for this template -->
    <link href="starter-template.css" rel="stylesheet">


    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>

<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="/">VA Claim Helper</a>
        </div>
        <div class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <li><a href="index.php">Cases</a></li>
                <?php
                    // Display a link to the Admin console if the current user
                    // is the admin
                    if (isset($_SESSION["user_name"]) && $_SESSION["user_name"] === "admin") { ?>
                        <li><a href="admin.php">Admin</a></li>
                <?php } ?>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <?php
                //if the user is logged in, show their username
                if (isset($_SESSION["user_name"])) {
                    echo "<li><p class='navbar-text'>Hi " . $_SESSION['user_name'] . "</p></li>";
                    echo "<li><a id='logout' href='login.php?logout=true'>Logout</a></li>";
                }
                ?>
            </ul>
        </div>
        <!--/.nav-collapse -->
    </div>
</div>
