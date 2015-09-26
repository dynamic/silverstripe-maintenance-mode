<?php

/**
 * Utility Page which can be used as a Down for Maintenance,
 * Under Construction or Coming Soon Page
 *
 * @author Darren-Lee Joseph <darrenleejoseph@gmail.com>
 * @package maintenancemode
 */
class UtilityPage extends ErrorPage {

    private static $singular_name = 'Utility Page';
    private static $plural_name = 'Utility Pages';
    private static $description = 'Use this to create a Down for Maintenance, Under Construction or Coming Soon Page';
    private static $icon = "maintenance-mode/images/tools-icon.png";

    private static $db = array(
        'RenderingTemplate' => 'Varchar(64)'
    );

    private static $has_one = array();

    private static $has_many = array();

    private static $defaults = array(
        'ErrorCode' => '503'
    );


    public function canCreate($member = null) {
        // Only allow one of this Page type to be created in the CMS.
        return !DataObject::get_one($this->ClassName);
    }


    /**
     * Create default Utility Page setup
     * Ensures that there is always a 503 Utility page by checking if there's an
     * instance of ErrorPage with a 503 error code. If there is not,
     * one is created when the DB is built.
     */
    public function requireDefaultRecords() {
        parent::requireDefaultRecords();

        // Skip creation of default records
        if(!self::config()->create_default_pages) return;

        //Only create a UtilityPage on dev/build if one does not already exist.
        if(!UtilityPage::get()->exists()) {

            $page = UtilityPage::create(array(
                'Title' => _t('MaintenanceMode.TITLE', 'Undergoing Scheduled Maintenance'),
                'URLSegment' => _t('MaintenanceMode.URLSEGMENT', 'offline'),
                'MenuTitle' => _t('MaintenanceMode.MENUTITLE', 'Utility Page'),
                'Content' => _t('MaintenanceMode.CONTENT', '<h1>We&rsquo;ll be back soon!</h1>'
                    .'<p>Sorry for the inconvenience but '
                    .'our site is currently down for scheduled maintenance. '
                    .'If you need to you can always <a href="mailto:#">contact us</a>, '
                    .'otherwise we&rsquo;ll be back online shortly!</p>'
                    .'<p>&mdash; The Team</p>'),
                'ParentID' => 0,
                'Status' => 'Published'
            ));
            $page->write();
            $page->publish('Stage', 'Live');

            DB::alteration_message('Utility Page created','created');
        }
    }


    public function getCMSFields() {
        $fields = parent::getCMSFields();

        $fields->addFieldToTab('Root.Main',

            $templateDropdownField = DropdownField::create(
                'RenderingTemplate',
                _t('MaintenanceMode.RENDERINGTEMPLATE', 'Template to render with'),
                self::get_top_level_templates()
            ), 'Content'

        );

        $templateDropdownField->setEmptyString(_t('MaintenanceMode.DEFAULTTEMPLATE', '(Use default template)'));


        $this->extend('updateCMSFields', $fields);
        return $fields;
    }


    /**
     * This function returns an array of top-level theme templates
     * @return array
     */
    public static function get_top_level_templates() {

        $ss_templates_array = array(); //initialise empty array
        $current_theme_path = THEMES_PATH . '/' . Config::inst()->get('SSViewer', 'theme');

        //theme directories to search
        $search_dir_array = array(
            MAINTENANCE_MODE_PATH .'/templates',
            $current_theme_path   .'/templates'
            //$current_theme_path   .'/templates/Layout' //we only want top level templates
        );

        foreach($search_dir_array as $directory) {

            //Get all the SS templates in the directory
            foreach(glob("{$directory}/*.ss") as $template_path) {

                //get the template name from the path excluding the ".ss" extension
                $template = basename($template_path, '.ss');

                //Add the key=>value pair to the ss_template_array
                $ss_templates_array[$template] = $template;
            }

        }

        return $ss_templates_array;

    }//end get_top_level_templates()


}


/**
 * Displays a utility page to users who are not logged in as admin
 *
 * @author Darren-Lee Joseph <darrenleejoseph@gmail.com>
 * @package maintenancemode
 */
class UtilityPage_Controller extends Page_Controller {

    public $templates; //required for template overrides

    private static $allowed_actions = array();

    public function init() {
        parent::init();

        $config = $this->SiteConfig();

        //regular non-admin users should only be able to see this utility page in maintenance mode
        if(!$config->MaintenanceMode && !Permission::check("ADMIN")) {
            return $this->redirect(BASE_URL); //redirect to home page

        };

        if($this->dataRecord->RenderingTemplate) {
            $this->templates['index'] = array($this->dataRecord->RenderingTemplate, 'Page');
        }
        $this->response->setStatusCode($this->ErrorCode);

    }

}