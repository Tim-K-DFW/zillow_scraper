<?php
	require("../includes/config.php");
	require('../templates/header.php');
?>

		<style>
        	#sections {
        		text-align: left;        		
        	}
        	
        	#bottom {
				top: 120px;
			}
			
			.links {
				text-align: center;
				
			}
        </style>

    </head>

    <body>
		<div id="sections">
		
			<div id="surgeons">
				<h2>Intuitive Surgical - statistics of robot-trained surgeons</h2>
				<p>Total world-wide count of robot-trained surgeons by speciality, updated weekly since 4/21/2014.</p>
				<p>The company migrated and re-designed the list in early May 2014, splitting cardiothoracic category into cardiac and thoracic ones. Additionally, there are no more surgeons with "null" category, which were previously categorized as "unspecified".</p>
				<p>Regular scanning began on 4/21/2014. The data as of 8/15/2013 was scanned and added in May 2014 from a separate webpage which specifies "Updated as of Aug 2013"; currently this webpage is not part of the company's "official" surgeon list.</p>
				<p class="links">
					<a href="surgeons.php">Use</a><br>
				</p>
			</div><br><hr>
			<div id="brokers">
				<h2>Zillow - statistics of paid agent subscriptions</h2>
				<p>"Premier" (i.e. paid) agent accounts, aggregated by state, updated weekly since 5/03/2014. Two output files track absolute subscriber count and retention.</p>
				<p>Because of website issues around 6/08/2014, the data for that day was copied from the previous week (5/31/2014). It should not make material difference for the overall picture.<p>				
				<p>Due to the structure of the source website, there is a slight possibility that the count is understated, since the website only shows first 253 agents (free and premier) in any given scope (specific neighborhood, city, state). The agents are displayed in descending order of their "rating" which is based on combination of amount and quality of client reviews. The order is not affected by an agent's premier status. Agents who haven't made it to the top 253, or first 25 pages, in any given scope, are not displayed and can not be accessed, at least not from the agents section. However, we believe that the share of such potential "shadow" premier agents relative to the identified ones is not material.</p>
				<p>Output definitions for retention file:
					<ul>
						<li><b>Average subscription length, % of total time</b> - calculated for every premier agent by dividing the number of weeks the agent was found in the roster by the number of weeks since scanning began.</li>
						<li><b>% of subscribers who cancelled last week</b> - ratio of premier agents who cancelled their subscription during most recent week, to number of premier agents at the beginning of the week.</li>
					</ul>
				
				</p>
				
				<p class="links">
					<!-- This section is currently under update. Please check back later. -->
					<a href="brokers.php">Use</a><br>
					<!-- <a href="brokers/practice.php">Test...</a><br> -->
				</p>
			</div>
	</div>
	<div id="bottom">
		<a href="../../public/logout.php">Log out</a>
	</div>
	
<?php require('../templates/footer.php');?>
