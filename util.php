<?php
/* Redirects to $uri if $cond() is true */
function smart_redirect($uri, $cond)
{
    if (stripos($_SERVER['REQUEST_URI'], $uri) == false && $cond()) {
        header('Location: ' . $uri);
    }
}


/*
 Takes an array of error messages and constructs an html error div 
 formatted for twitter bootstrap.
 */
function bootstrap_error_div($errors)
{
    $resp = "<div class='alert alert-danger' role='alert'>";
    foreach ($errors as $error) {
        $resp .= "<p>$error</p>";
    }
    $resp .= "</div>";
    return $resp;
}


?>
