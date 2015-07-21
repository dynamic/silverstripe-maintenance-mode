<?php

/**
 * Runs before the init function of every Page_Controller
 * to redirect regular non-admin users to the Utility
 * Page if maintenance mode is switched on.
 *
 * @author Darren-Lee Joseph <darrenleejoseph@gmail.com>
 * @author Patrick Nelson <pat@catchyour.com>
 * @package maintenancemode
 */
class MaintenanceMode_Page_ControllerExtension extends Extension {

    // Set to true after the first execution as an extra measure to prevent infinite recursion, just in case.
    protected static $runOnce = false;

    /**
     * @throws SS_HTTPResponse_Exception
     * @return SS_HTTPResponse
     */
    public function onBeforeInit() {

        if(Permission::check("ADMIN")) return;

        $config = SiteConfig::current_site_config();
        if(!$config->MaintenanceMode) return;

        // Fetch our utility page instance now.
        /** @var Page $utilityPage */
        $utilityPage = UtilityPage::get()->first();
        if (!$utilityPage) return; // We need a utility page before we can do anything.

        // See if we're still configured to redirect...
        if (!$utilityPage->config()->DisableRedirect) {
            //If this is not the utility page, do a temporary (302) redirect to it
            if($this->owner->dataRecord->ClassName != "UtilityPage") {
                return $this->owner->redirect($utilityPage->AbsoluteLink(), 302);
            }
        }

        // No need to execute more than once.
        if ($this->owner instanceof UtilityPage_Controller) return;

        // Additional failsafe, just in case (for some reason) the current controller isn't descended from UtilityPage_Controller.
        if (static::$runOnce) return;
        static::$runOnce = true;

        // Process the request internally to ensure the URL is maintained (instead of redirecting to our maintenance page's URL).
        $controller = ModelAsController::controller_for($utilityPage);
        $response = $controller->handleRequest(new SS_HTTPRequest("GET", "/"), new DataModel());
        throw new SS_HTTPResponse_Exception($response, $response->getStatusCode());
    }

}//end class MaintenanceMode_Page_ControllerExtension



/**
 * Add settings fields to SiteConfig to control maintenance mode
 *
 * @author Darren-Lee Joseph <darrenleejoseph@gmail.com>
 * @package maintenancemode
 */
class MaintenanceMode_SiteConfigExtension extends DataExtension {

    // Add database field for flag to either display or hide under construction pages.
    static $db = array(
        'MaintenanceMode' => 'Boolean'
    );

    public function updateCMSFields(FieldList $fields) {

        //create new tabs in SiteConfig
        $fields->addFieldToTab("Root.Access",
            FieldGroup::create(
                HeaderField::create('MaintenanceModeHeading',
                    _t('MaintenanceMode.SETTINGSHEADING', 'Offline/Maintenance Mode'),
                    $headingLevel = 3),

                CheckboxField::create(
                    'MaintenanceMode',
                    '&nbsp; ' . _t('MaintenanceMode.SETTINGSACTIVATE', 'Activate Offline/Maintenance Mode')
                )
            )
        );

    }//end updateCMSFields

}//end class MaintenanceMode_SiteConfigExtension
