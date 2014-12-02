<?php

    // display errors, warnings, and notices
    ini_set("display_errors", true);
    error_reporting(E_ALL);

    // requirements
    require("functions.php");

    // enable sessions
    session_start();

    // require authentication for most pages
    if (!preg_match("{(?:login|logout)\.php$}", $_SERVER["PHP_SELF"]))
    {
        if (empty($_SESSION["client"]))
        {
			redirect("public/login.php");
        }
    }

?>
