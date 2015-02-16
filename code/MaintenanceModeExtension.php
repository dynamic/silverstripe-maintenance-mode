<?php

/**
 * Runs before the init function of every Page_Controller
 * to redirect regular non-admin users to the Utility
 * Page if maintenance mode is switched on.
 *
 * @author Darren-Lee Joseph <darrenleejoseph@gmail.com>
 * @package maintenancemode
 */
class MaintenanceMode_Page_ControllerExtension extends Extension {

    public function onBeforeInit() {

        if(Permission::check("ADMIN")) return;

        $config = SiteConfig::current_site_config();
        if(!$config->MaintenanceMode) return;

        //If this is not the utility page, do a temporary (302) redirect to it
        if($this->owner->dataRecord->ClassName != "UtilityPage") {
            return $this->owner->redirect(UtilityPage::get()->first()->AbsoluteLink(), 302);
        }

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
