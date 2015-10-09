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

Installation can be done either by composer or by manually downloading a release.

### Via composer

`composer require "thisisbd/silverstripe-maintenance-mode:*"`

### Manually

 1.  Download the module from [the releases page](https://github.com/thisisbd/silverstripe-maintenance-mode/releases).
 2.  Extract the file (if you are on windows try 7-zip for extracting tar.gz files
 3.  Make sure the folder after being extracted is named 'maintenance-mode'
 4.  Place this directory in your sites root directory. This is the one with framework and cms in it.
 5. Visit `<yoursite.com>/dev/build/?flush` to rebuild the database.


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

### Redirecting vs. Displaying at any URL

By default, the current functionality is to redirect users to a separate URL which you can configure within the CMS (e.g. `/offline/`), however it may be useful for you to simply display the maintenance message at any URL that the user may visit (to ensure user does not lose the page that they're currently at, in case maintenance window is very short).

To disable redirects, drop the following lines into your site's `config.yaml` file:

```yaml
UtilityPage:
  DisableRedirect: true
```

### Command Line

You can toggle maintenance mode either `on` or `off` via the command line easily by simply running the `MaintenanceMode` task. For example:

```bash
# Via Sake:
sake dev/tasks/MaintenanceMode on

# Via the CLI script directly:
php framework/cli-script.php dev/tasks/MaintenanceMode on
```


### Allowing specific IP addresses
You can configure the module to also allow specific IP addresses pass the maintenance page.
To add IP's add the following lines into your site's `config.yaml` file:

```yaml
Page_Controller:
  allowed_ips:
    - '127.0.0.1'
    - '::1'
```

Known Issues
------------
There are no known issues.
