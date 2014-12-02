<?php

     /**
     * Logs out current user, if any.  Based on Example #1 at
     * http://us.php.net/manual/en/function.session-destroy.php.
     */
    function logout()
    {
        // unset any session variables
        $_SESSION = array();

        // expire cookie
        if (!empty($_COOKIE[session_name()]))
        {
            setcookie(session_name(), "", time() - 42000);
        }
       
		// destroy session
        session_destroy();
    }

    // your database's password
    define("PASSWORD", "...");

    // your database's server
    define("SERVER", "...");

    // your database's username
    define("USERNAME", "...");

	ini_set("display_errors", true);
    error_reporting(E_STRICT);
	
	/*
     * Executes SQL statement, possibly with parameters, returning
     * an array of all rows in result set or false on (non-fatal) error.
     */
    function query(/* $sql [, ... ] */)
    {
        //database name
		$database = func_get_arg(0);
		
		// SQL statement
        $sql = func_get_arg(1);

        // parameters, if any
        $parameters = array_slice(func_get_args(), 2);

		// try to connect to database
        static $handle;
        if (!isset($handle))
        {
            try
            {
                // connect to database
                $handle = new PDO("mysql:dbname=" . $database . ";host=" . SERVER, USERNAME, PASSWORD);

                // changed to TRUE from CS50's "false" after problems with brokers execution
                $handle->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
				
				// added for brokers
				$handle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				// echo 'Connection was successful...<br>';
				
            }
            catch (PDOException $e) 	// was "exception" instead of "PDOException"
            {
                // trigger (big, orange) error
                trigger_error($e->getMessage(), E_USER_ERROR);
				exit;
            }
        }

        // prepare SQL statement
        $statement = $handle->prepare($sql);
		if ($statement === false)
        {
            // trigger (big, orange) error;
            // trigger_error($handle->errorInfo()[2], E_USER_ERROR);
			echo "Exiting because statement PREPARATION failed...<br>";
			echo "\nPDO::errorInfo():\n";
			print_r($handle->errorInfo());
			
			exit;
        } else
			// echo 'Statement prepared successfully...<br>';
		
	

		// execute SQL statement
		try {		
			// $results = $handle->exec($sql);         // executes raw sql, without prepared statement
			// echo 'Raw SQL executed, no exceptions caught...<br>';
			
			$results = $statement->execute($parameters);
		}
		catch (exception $f)
		{
			echo "Exiting because statement EXECUTION failed...<br>";
			echo $f->getMessage() . "<br>";
			echo ($f->getTraceAsString() . "<br><br>");
			var_dump ($f->getTrace());
            exit;
		}
		
		// echo '<br>Statement executed as follows:<br>';
		// echo "PDO::errorInfo():\n<br>";
		// print_r($handle->errorInfo());		
		// echo '<br>Statement results are as follows: ';
		// $t = $statement->fetchAll(PDO::FETCH_ASSOC);
		// var_dump($t);
				
		
		// return result set's rows, if any
		$handle = NULL;
		return $statement->fetchAll(PDO::FETCH_ASSOC);
		
		/*
		if ($results !== false)
        {
            $handle = NULL;
			return $statement->fetchAll(PDO::FETCH_ASSOC);
        }
		
		 else
        {
            $handle = NULL;
			return false;
        } */
    }


    /**
     * Redirects user to destination, which can be
     * a URL or a relative path on the local host.
     *
     * Because this function outputs an HTTP header, it
     * must be called before caller outputs any HTML.
     */
    function redirect($destination)
    {
        // echo "destination is " . $destination . "<br>";
		
		// handle URL
        if (preg_match("/^https?:\/\//", $destination))
        {
            header("Location: " . $destination);
        }

        // handle absolute path
        else if (preg_match("/^\//", $destination))
        {
            // echo 'looks like it is absolute path...';
			// sleep(3);
			
			$protocol = (isset($_SERVER["HTTPS"])) ? "https" : "http";
            $host = $_SERVER["HTTP_HOST"];
			// echo 'header output: Location: ' . $protocol . '://' . $host . $path . "/" . $destination;
            header("Location: $protocol://$host$destination");
            // sleep(5);
        }

        // handle relative path
        else
        {
            // adapted from http://www.php.net/header
            // echo 'looks like it is relative path...<br>';
			// sleep(3);
			
			$protocol = (isset($_SERVER["HTTPS"])) ? "https" : "http";
			// echo 'protocol: ' . $protocol . '<br>';
            $host = $_SERVER["HTTP_HOST"];
			// echo 'host: ' . $host . '<br>';
			$path = rtrim(dirname($_SERVER["PHP_SELF"]), "/\\");
			// echo 'current path: ' . $path . '<br>';
            
			
			$target = $protocol . '://' . $host. "/" . $destination;
			// echo 'trying to redirect to '.$target;
						
			header("Location: $target");
			
			
        }

        // exit immediately since we're redirecting anyway
        exit;
    }

    /**
     * Renders template, passing in values.
     */
    function render($template, $values = array())
    {
        // extract variables into local scope
        extract($values);
		// print_r($values);
		// echo ("<br>It's looking for: ../templates/" . $values["client"] . $template . "<br>");
		// echo "Current script is " .  $_SERVER["PHP_SELF"] . "<br>";
		// echo "Current directory is " . __DIR__ . "<br>";
		
		// if (file_exists("../templates/$template"))
		// if template exists, render it
		if (file_exists("../templates/" . $values["client"] . "$template"))
        {
            // render header
            require("../templates/header.php");

            // render template
            require("../templates/" . $values['client'] . "$template");

            // render footer
            require("../templates/footer.php");
        }

        // else err
        else
        {
            echo "Template NOT exists!";
			sleep(5);
			trigger_error("Invalid template: $template", E_USER_ERROR);
        }
    }

?>