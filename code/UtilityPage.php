<?php

/**
 * Utility Page which can be used as a Down for Maintenance,
 * Under Construction or Coming Soon Page
 *
 * @package maintenancemode
 *
 * @author Darren-Lee Joseph <darrenleejoseph@gmail.com>
 */
class UtilityPage extends ErrorPage
{

	private static $singular_name = 'Utility Page';
	private static $plural_name = 'Utility Pages';
	private static $description = 'Use this to create a Down for Maintenance, Under Construction or Coming Soon Page';
	private static $icon = 'maintenance-mode/images/tools-icon.png';

	private static $db = array(
		'RenderingTemplate' => 'Varchar(64)'
	);

	private static $has_one = array();

	private static $has_many = array();

	private static $defaults = array(
		'ErrorCode' => '503'
	);

	/**
	 * @param  Member    $member
	 * @return boolean
	 */
	public function canCreate($member = null)
	{
		// Only allow one of this Page type to be created in the CMS.
		return !UtilityPage::get()->exists();
	}

	/**
	 * Create default Utility Page setup
	 * Ensures that there is always a 503 Utility page by checking if there's an
	 * instance of ErrorPage with a 503 error code. If there is not,
	 * one is created when the DB is built.
	 */
	public function requireDefaultRecords()
	{
		parent::requireDefaultRecords();

		// Skip creation of default records
		if (!self::config()->create_default_pages) {
			return;
		}

		// Ensure that an assets path exists before we do any error page creation
		if (!file_exists(ASSETS_PATH)) {
			mkdir(ASSETS_PATH);
		}

		$code = self::$defaults['ErrorCode'];
		$pagePath = self::get_filepath_for_errorcode($code);
		$page = UtilityPage::get()->first();
		$pageExists = ($page && $page->exists());

		//Only create a UtilityPage on dev/build if one does not already exist.
		if (!$pageExists || !file_exists($pagePath)) {

			if (!$pageExists) {
				$page = UtilityPage::create(array(
					'Title'      => _t('MaintenanceMode.TITLE', 'Undergoing Scheduled Maintenance'),
					'URLSegment' => _t('MaintenanceMode.URLSEGMENT', 'offline'),
					'MenuTitle'  => _t('MaintenanceMode.MENUTITLE', 'Utility Page'),
					'Content'    => _t('MaintenanceMode.CONTENT', '<h1>We&rsquo;ll be back soon!</h1>'
.'<p>Sorry for the inconvenience but '
.'our site is currently down for scheduled maintenance. '
.'If you need to you can always <a href="mailto:#">contact us</a>, '
.'otherwise we&rsquo;ll be back online shortly!</p>'
.'<p>&mdash; The Team</p>'),
					'ParentID'   => 0,
					'Status'     => 'Published'
				));
				$page->write();
				$page->publish('Stage', 'Live');
			}

			// Ensure a static error page is created from latest Utility Page content
			$response = Director::test(Director::makeRelative($page->Link()));
			$written = null;
			if ($fh = fopen($pagePath, 'w')) {
				$written = fwrite($fh, $response->getBody());
				fclose($fh);
			}

			if ($written) {
				DB::alteration_message(
					sprintf('%s error Utility Page created', $code),
					'created'
				);
			} else {
				DB::alteration_message(
					sprintf(
						'%s error Utility page could not be created at %s. Please check permissions',
						$code,
						$pagePath
					),
					'error'
				);
			}

		}
	}

	/**
	 * @return mixed
	 */
	public function getCMSFields()
	{
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
	public static function get_top_level_templates()
	{

		$ss_templates_array = array();
		$current_theme_path = THEMES_PATH.'/'.Config::inst()->get('SSViewer', 'theme');

		//theme directories to search
		$search_dir_array = array(
			MAINTENANCE_MODE_PATH.'/templates',
			$current_theme_path.'/templates'
		);

		foreach ($search_dir_array as $directory) {

			//Get all the SS templates in the directory
			foreach (glob("{$directory}/*.ss") as $template_path) {

				//get the template name from the path excluding the ".ss" extension
				$template = basename($template_path, '.ss');

				//Add the key=>value pair to the ss_template_array
				$ss_templates_array[$template] = $template;
			}

		}

		return $ss_templates_array;

	} //end get_top_level_templates()
}

/**
 * Displays a utility page to users who are not logged in as admin
 *
 * @package maintenancemode
 *
 * @author Darren-Lee Joseph <darrenleejoseph@gmail.com>
 */
class UtilityPage_Controller extends Page_Controller implements PermissionProvider
{

	private static $url_handlers = array(
		'*' => 'index'
	);

	private static $allowed_actions = array();

	public function init()
	{
		parent::init();
	}

	/**
	 * @return mixed
	 */
	public function index()
	{

		$config = $this->SiteConfig();

		//regular non-admin users should only be able to see this utility page in maintenance mode
		if (!$config->MaintenanceMode && !Permission::check('ADMIN')) {
			return $this->redirect(BASE_URL); //redirect to home page
		}

		$this->response->setStatusCode($this->ErrorCode);

		if ($this->dataRecord->RenderingTemplate) {
			return $this->renderWith(array($this->dataRecord->RenderingTemplate, 'Page'));
		}

		return array();
	}

	public function providePermissions()
	{
		return array(
			'VIEW_SITE_MAINTENANCE_MODE' => 'Access the site in Maintenance Mode'
		);
	}
}
