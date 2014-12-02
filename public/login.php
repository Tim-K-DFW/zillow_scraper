<?php

    // configuration
    require("../includes/config.php"); 

    // if form was submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
        // validate submission
        if (empty($_POST["password"]))
        {
            render("login_form.php", array("title" => "Log In"));
        }

        // query database for user
        $rows = query('...', "SELECT * FROM access WHERE hash = ?", $_POST["password"]);

        // if we found user, store the user's ID in session
        if (count($rows) == 1)
        {
            // first (and only) row
            $row = $rows[0];

            // remember that user's now logged in by storing user's ID in session
            $_SESSION["client"] = $row["client_no"];

            // redirect to homepage
            redirect("../" . $_SESSION["client"] . "homepage.php");
            
        }

        // else render login form
        render("login_form.php", array("title" => "Log In"));
    }
    else
    {
        // else render login form
        render("login_form.php", array("title" => "Log In"));
    }

?>
