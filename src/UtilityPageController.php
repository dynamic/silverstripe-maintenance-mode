<?php

namespace dljoseph\MaintenanceMode;

use SilverStripe\Security\Permission;
use SilverStripe\Security\PermissionProvider;

/**
 * Displays a utility page to users who are not logged in as admin
 *
 * @package maintenancemode
 *
 * @author Darren-Lee Joseph <darrenleejoseph@gmail.com>
 */
class UtilityPageController extends \PageController implements PermissionProvider
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
            return $this->renderWith(array($this->dataRecord->RenderingTemplate));
        }

        return $this->renderWith(array('UtilityPage', 'Page'));
    }

    public function providePermissions()
    {
        return array(
            'VIEW_SITE_MAINTENANCE_MODE' => 'Access the site in Maintenance Mode'
        );
    }
}
