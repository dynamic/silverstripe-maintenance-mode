# SilverStripe Maintenance Mode Module
Maintainance/Offline Mode Module for SilverStripe.  Allows an 
administrator to put site in offline mode with 503 status to display a 
'Coming Soon', 'Under Construction' or 'Down for Maintenance' Page to 
regular visitors, whilst allowing a logged in admin user to browse and 
make changes to the site.


Maintainer Contacts
-------------------
*  DL Joseph (Nickname: dljoseph) `<darrenleejoseph (at) gmail (dot) com>`

Requirements
------------

* SilverStripe 3.1


Installation Instructions
-------------------------

1. Place this directory in the root of your SilverStripe installation.
2. Visit `<yoursite.com>/dev/build/?flush` to rebuild the database.


Usage Overview
--------------
A "Maintenance Mode" checkbox will be added to the SiteConfig Access settings;
from there you can activate maintenance mode to take the site offline - and a
new Utility Page will be added to the SiteTree in the Pages section of the CMS.
You can edit the content of the Utility Page and/or manually override which
theme template SilverStripe uses to render the page for display.


### Templating
To override the default UtilityPage template, add a template called 
UtilityPage.ss in the templates folder of your theme (above Layouts) and then 
flush the template cache.

If you wish to use a different template for the UtilityPage, there are no
restrictions, simply create a new SilverStripe template file, be sure to place
it directly in the templates folder (above Layouts) and visit <yoursite>/?flush
to flush the template cache.  Afterwards, you must go to the UtilityPage in the
CMS and select the manually select the template from the dropdown to tell
SilverStripe which template to use to render the page for display.


Known Issues
------------
There are no known issues.