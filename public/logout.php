<?php

    // configuration
    require("../includes/config.php");
    	
    // log out current user, if any
    logout();
    
    // echo "current dir is " . __DIR__;

    // redirect user
    redirect("../index.php");

?>
