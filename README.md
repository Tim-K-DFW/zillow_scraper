zillow_scraper
==============
Final project for the online computer science class (Harvard's CS50x) in April 2014.

The general infrastructure (page rendering and MySQL integration) is not mine, it was adopted from one of the CS50x projects. The core scraping, parsing and processing functionality (my bread and butter) is in `client_1/brokers/`. Workflow for weekly cron job is `update_db`, then `update_retention`; `download` is user-driven whenever he wants to download updated results. 

The live app is at http://www.non-descript.net/, password "hbs". Scraping functionality no longer works (after Zillow re-designed its website in September 2014) and there was no business need to continue scraping. Weekly data collected between May and September 2014 is still stored and is available for download.

Code for the second part of the app (Intuitive Surgical scraping) is not posted on GitHub because it's just a much simpler version of Zillow's - single source page, no MySQL processing and no retention stats.


