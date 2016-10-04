# Clash Tracker
## Clan &amp; Player Statistics Tracker for Clash of Clans

An instance of this website is hosted at <a href='http://clashtracker.ca'>clashtracker.ca</a>. I recommend using that site so that all data gets congregated together and can be used by everyone. However, if you'd like to host your own instance of this site, the instructions are as follows.

Before starting, <a href='https://help.github.com/articles/fork-a-repo/'>fork</a> this repository so you have your own version of the code that you may work on. 

## Hosting
I've hosted my website using Heroku, using a few of the add-ons for sending emails, the SQL DB and for API calls to the Clash of Clans API. Go to <a href='http://heroku.com'>heroku.com</a> and create an account, if you do not already have one. Create a new app and deploy your forked repo to the app; you can even hook it up in Heroku so that any new commits to a specific branch will automatically deploy to your app.

## Add-ons
Add the following add-ons to your app:
 - <a href='https://elements.heroku.com/addons/cleardb'>ClearDB MySQL</a>
 - <a href='https://elements.heroku.com/addons/sendgrid'>SendGrid</a>
 - <a href='https://elements.heroku.com/addons/fixie'>Fixie</a>*
 - <a href='https://elements.heroku.com/addons/quotaguardstatic'>QuotaGuard Static</a>*

*Only need one of these, but it's good to have both

## Config Vars
In Heroku, go to your App Settings and click on 'Reveal Config Vars', there you can see your MySQL DB creds so you can log in. Add a new config var named 'PRODUCTION' and give it any value you want. The clashtracker app looks for that config variable to determine what do to in some circumstances. 

## Setting up the DB
Using the credentials found in the config vars, you can log into your MySQL server and run SQL queries on your DB. You should now run the `sql/master.sql` file on your DB to set it up for the Clash Tracker app.

## Creating an Admin User
The first user that gets created in the DB gets assigned the admin role so they can access the developer page; go to the `/signup.php` page, create your account and sign in.

## Setting up API tokens
Go to <a href="https://developer.clashofclans.com">Clash of Clan's developer website</a> and log in or create an account, if you don't already have one. Once logged in, go to <a href="https://developer.clashofclans.com/#/account">My Account</a> and <a href="https://developer.clashofclans.com/#/new-key">Create a Key</a>. Give it a name and a description and then you'll need the static IP addresses from <a href="https://dashboard.usefixie.com/#/account">Fixie</a> and/or <a href="https://www.quotaguard.com/dashboard/static">QuotaGuard Static</a>. Each of these add-ons give you 2 IP addresses; make sure you add both of them to the same key. 
Once you've created the API key, copy it and the IP addresses and go to the `/dev.php` page. Under the API Keys section, add the IPs and their associated API keys. You can add multiple IP addresses to the same API Key by putting all IP addresses divided by spaces in the IPs input.

## Setting up Proxy Information
On the `/dev.php` page, add the different static IP add-ons that you're using. For each one you've added, you'll need the config variable name, the monthly limit and one of the IP addresses for that add-on. Here are the monthly limits for the base plans of the add-ons mentioned above:

Add-on | Config Var Name | Monthly Limit of Base Plan
------------ | ------------ | -------------
<a href='https://elements.heroku.com/addons/fixie'>Fixie</a> | FIXIE_URL | 500
<a href='https://elements.heroku.com/addons/quotaguardstatic'>QuotaGuard Static</a> | QUOTAGUARDSTATIC_URL | 250

## Contributing
Clash Tracker is mainly developed by <a href="https://github.com/alexinman">myself</a>, however, feel free to fork and fix or add to Clash Tracker in any way you'd like and please submit a pull request to develop for any changes you make so that everyone can benefit from your hard work :)

## Miscellaneous
A player's war score is calculated based on performance in wars. There are some penalties to scores for various reasons that might not be intuitive. For example, one penalty is the new player penalty; a player gets docked slightly for only being in a few wars. This is to give a more realistic first impression for players that just joined and had just a couple good wars to start off with. This penalty can be calculated from the table below:
Number of Completed Wars | % of Total Score
------------------------ | ----------------
1* | 82%
2 | 85%
3 | 88%
4 | 91%
5 | 94%
6 | 97%
7 | 100%
* This is technically what is used when score players that have only been in 1 completed war, however, they will not be shown on the war stats page due to not very much information to show anything really useful.