<?php

/**
 * Runs before the init function of every Page_Controller
 * to redirect regular non-admin users to the Utility
 * Page if maintenance mode is switched on.
 *
 * @package maintenancemode
 *
 * @author Darren-Lee Joseph <darrenleejoseph@gmail.com>
 * @author Patrick Nelson <pat@catchyour.com>
 */

namespace dljoseph\MaintenanceMode;

use SilverStripe\CMS\Controllers\ModelAsController;
use SilverStripe\Control\HTTP;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Extension;
use SilverStripe\Security\Permission;
use SilverStripe\SiteConfig\SiteConfig;

class PageControllerExtension extends Extension
{

    /**
     * Allowed IP addresses
     *
     * @var array
     */
    private static $allowed_ips = array();

    /**
     * @return HTTPResponse
     */
    public function onBeforeInit()
    {

        $config = SiteConfig::current_site_config();

        // If Maintenance Mode is Off, skip processing
        if (!$config->MaintenanceMode) {
            return;
        }


        // Check if the visitor is Admin OR if they have an allowed IP.
        if (Permission::check('VIEW_SITE_MAINTENANCE_MODE') || Permission::check('ADMIN') || $this->hasAllowedIP()) {
            return;
        }

        // Are we already on the UtilityPage? If so, skip processing.
        if ($this->owner instanceof UtilityPageController) {
            return;
        }


        //Is visitor trying to hit the admin URL?  Give them a chance to log in.
        if(strstr("/Security/login", $this->owner->RelativeLink())) {
            return;
        }


        // Fetch our utility page instance now.
        /**
         * @var Page $utilityPage
         */
        $utilityPage = UtilityPage::get()->first();
        if (!$utilityPage) {
            return;
        }
        // We need a utility page before we can do anything.

        // Are we configured to prevent redirection to the UtilityPage URL?
        if ($utilityPage->config()->DisableRedirect) {

            // Process the request internally to ensure that the URL is maintained
            // (instead of redirecting to the maintenance page's URL) and skip any further processing.

            $controller = ModelAsController::controller_for($utilityPage);
            $response = $controller->handleRequest(new HTTPRequest('GET', ''));

            HTTP::add_cache_headers($response);
            $response->output();

            die();
        }

        // Default: Skip any further processing and immediately respond with a redirect to the UtilityPage.
        $response = new HTTPResponse();
        $response->redirect($utilityPage->AbsoluteLink(), 302);

        HTTP::add_cache_headers($response);
        $response->output();

        die();
    }

    /**
     * Check if the visitors IP is in the array of allowed IP's
     *
     * @return boolean
     */
    public function hasAllowedIP()
    {
        return in_array($this->getClientIP(), $this->owner->config()->allowed_ips);
    }

    /**
     * Get the visitors IP based on the following
     *
     * @return string
     */
    public function getClientIP()
    {
        return $this->owner->getRequest()->getIP();
    }

} //end class PageControllerExtension
