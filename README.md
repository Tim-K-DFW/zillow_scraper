zillow_scraper
==============
Final project for the online computer science class (Harvard's CS50x) in April 2014, the first programming class I took.

The general infrastructure (page rendering and MySQL integration) is not mine, it was adopted from one of the CS50x projects. The core scraping, parsing and MySQL processing functionality (my bread and butter) is in `client_1/brokers/`. Workflow for weekly cron job is `update_db`, then `update_retention`; `download` is user-driven whenever he wants to download updated results. 


### Background - implementation

Zillow website at the time (April 2014) included a broker directory section, organized in scopes at four levels: national (1 scope), states (56), cities (~560), neighborhoods (~3,900). Each scope had up to 25 pages with up to 10 broker profiles per page. Broker's position within a scope was based on ratings, and one broker could be listed in several scopes (e.g. on once at the state level, once at the city level and then 4 times in different neighborhoods in that city). <a href="https://gist.github.com/Tim-K-DFW/5f086120a41e90fdb445">This log</a> illustrates the hierarchy traversal.

The script (`update_db.php`) was set up as a cron job (LAMP stack) to scan the entire website every week, going from page to page in every possible scope, parsing broker profiles and separating those with free subscription from those with paid one (based on profile tokens). For example, one of July 2014 updates (whose log is above) traversed 18,107 webpages and found 64,420 brokers (the count grew slowly between April and August). The script was deliberately slowed down to take several seconds between each http request, so as to not alert the target, the above-mentioned July scan took over 29 hours to complete.

After initial parsing, broker names and location are stored in a MyQSL database, cleaned up (removing duplicate entries for same broker in several scopes) and organized by state and scan date. After that, a separate script (`update_retention.php`) calculates retention metrics - average subsription length and % of brokers who cancelled subscription compared to a week ago and a month ago - by state and overall.  With over 20,000 brokers for each weekly scan and elaborate `join`'s to match this required some tricky looping to keep the shared-hosted MySQL from stalling.

Ultimately, the database was available to the user via `download.php` script with some Ajax whereby the user visited the page and could donwload the most recent statistics in csv format. Additionally, and it is not apparent from the website, the database was made accessible to the user's Excel/PowerPivot (with no administration required on the user end) -- for fully customizable reports and analytics.


### Background - business aspect

Zillow is a publicly traded company, and about 80% of its revenue at the time of project came from selling subscriptions to real estate brokers (less than 5% of brokers on Zillow have paid subscrition). Having a real-time sense of the subscriber count (all subscriptions, and paid ones in particular) and retention is a valuable information for a Zillow investor.
