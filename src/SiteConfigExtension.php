<?php

namespace dljoseph\MaintenanceMode;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;


/**
 * Add settings fields to SiteConfig to control maintenance mode
 *
 * @package maintenancemode
 *
 * @author Darren-Lee Joseph <darrenleejoseph@gmail.com>
 */
class SiteConfigExtension extends DataExtension
{

	/**
	 * Add database field for flag to either display or hide under construction pages.
	 *
	 * @var array
	 */
	private static $db = array(
		'MaintenanceMode' => 'Boolean'
	);

	/**
	 * @param FieldList $fields
	 */
	public function updateCMSFields(FieldList $fields)
	{
		//create new tabs in SiteConfig
		$fields->addFieldToTab('Root.Access',
		    FieldGroup::create(
			    new CheckboxField(
			        'MaintenanceMode',
			        _t('MaintenanceMode.SETTINGSACTIVATE', 'Activate Offline/Maintenance Mode')
		        )
		    )->setTitle(
		        _t('MaintenanceMode.SETTINGSHEADING', 'Offline/Maintenance Mode')
	        )
	    );
	} //end updateCMSFields
} //end class SiteConfigExtension
