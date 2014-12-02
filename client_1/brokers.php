<?php
	require("../includes/config.php");
	require('../templates/header.php');
?>
</head>
	
	<script type="text/javascript">
		function update(){
			// document.getElementById("update_button").value = "Please wait...";
			var xmlhttp;
			if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
				xmlhttp=new XMLHttpRequest();
			} else {// code for IE6, IE5
				xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
			}
			
			xmlhttp.onreadystatechange=function() {
				if (xmlhttp.readyState==1 || xmlhttp.readyState==2 || xmlhttp.readyState==3) {
					document.getElementById("as_of_date").innerHTML = xmlhttp.responseText;
				} else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
					response = xmlhttp.responseText.split("[BRK]");					
					document.getElementById("as_of_date").innerHTML = response[0];
					document.getElementById("update_button").value = response[1];
				}
			}
		
			xmlhttp.open("GET","brokers/update_db.php",true);
			xmlhttp.send();
		}
	
		function download(){
			var xmlhttp;
			if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
				xmlhttp=new XMLHttpRequest();
			} else {// code for IE6, IE5
				xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
			}
			
			xmlhttp.onreadystatechange=function() {
				if (xmlhttp.readyState==4 && xmlhttp.status==200) {
					document.getElementById("download_link").innerHTML=xmlhttp.responseText;
				}
			}
		
			xmlhttp.open("GET","brokers/download.php",true);
			xmlhttp.send();
		}
	</script>
	
    <body>
		<h2>Zillow - paid subscribers statistics</h2>
		
	<p id="as_of">
		Most recent update:<br>
		<span id="as_of_date">
			<?php
				$mr_time = query('real_estate_z2', "SELECT MAX( TIME ) AS time FROM main");
				echo ($mr_time[0]['time']);
				echo ' ET';
			?>
		</span>
	</p>
		<!-- <input id="update_button" type="button" value="Update now" onclick="update()"></input>  -->
		<br>
		<input id="download_button" type="button" value="Download statistics" onclick="download()"></input>
	
	<div id="download_link"></div>
	<div id="bottom">
		<a href="homepage.php">Go back</a>
		
	</div>
<?php require('../templates/footer.php');?>
