zillow_scraper
==============
Final project for the online computer science class (Harvard's CS50x) in April 2014.

The general infrastructure (page rendering and MySQL integration) is not mine, it was adopted from one of the CS50x projects. The core scraping, parsing and MySQL processing functionality (my bread and butter) is in `client_1/brokers/`. Workflow for weekly cron job is `update_db`, then `update_retention`; `download` is user-driven whenever he wants to download updated results. 

The live app is at http://www.non-descript.net/, password "hbs". Scraping functionality no longer works, after Zillow re-designed its website in September 2014 and there was no business need to continue scraping. Weekly data collected between May and September 2014 is still stored and is available for download.

Code for the second part of the app (Intuitive Surgical scraping) is not posted on GitHub because it's just a much simpler version of Zillow's - single source page, no MySQL processing and no retention stats.


**Background information - implementation aspect**

Zillow's website at the time (April 2014) included a broker directory section, organized in scopes at four levels: national (1 scope), states (56), cities (~540), neighborhoods (~3,700). Each scope had up to 25 pages with up to 10 broker profiles per page. Broker's position within a scope was based on ratings, and one broker could be listed in several scopes (e.g. on once at the state level, once at the city level and then 4 times in different neighborhoods in that city).

The script (`update_db.php`) was set up as a cron job (LAMP stack) to scan the entire website every week, going from page to page in every possible scope, parsing broker profiles and separating those with free subscription from those with paid one (based on profile tokens). For example, one update in July traversed 17,448 webpages and found 61,290 brokers (the count grew slowly). The script was deliberately slowed down to take several seconds between each http request, so as to not alert the target, the above-mentioned July scan took almost 28 hours to complete.

After initial parsing, broker names and location are stored in a MyQSL database, cleaned up (removing duplicate entries for same broker in several scopes) and organized by state and scan date. After that, a separate script (`update_retention.php`) calculates retention metrics - average subsription length and % of brokers who cancelled subscription compared to a week ago and a month ago - by state and overall.  With over 20,000 brokers for each weekly scan and elaborate `join`'s to match this required some tricky looping to keep the shared-hosted MySQL from stalling.

Ultimately, the database was available to the user via `download.php` script with some Ajax whereby the user visited the page and could donwload the most recent statistics in csv format. Additionally, and it is not apparent from the website, the database was made accessible to the user's Excel/PowerPivot (with no administration required on the user end) -- for fully customizable reports and analytics.


**Background information - business aspect**

Zillow is a publicly traded company, and about 80% of its revenue at the time of project came from selling subscriptions to real estate brokers (less than 5% of brokers on Zillow have paid subscrition). Having a real-time sense of the subscriber count (all subscriptions, and paid ones in particular) and retention is a valuable information for a Zillow investor.
