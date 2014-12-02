	<style>
		#login {
			position: fixed;
			top: 40%;
			width: 100%;		
		}
	</style>
</head>
<body>
		
<form id = "login" action="login.php" method="post">
        <div class="form-group">
            <input class="form-control" name="password" placeholder="Password" type="password" autofocus/>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-default">Log In</button>
        </div>
</form>