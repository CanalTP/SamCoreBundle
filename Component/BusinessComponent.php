<?php

namespace CanalTP\SamCoreBundle\Component;

use CanalTP\SamEcoreApplicationManagerBundle\Component\AbstractBusinessComponent;
use CanalTP\SamEcoreApplicationManagerBundle\Permission\BusinessPermissionManagerInterface;
use CanalTP\SamCoreBundle\Menu\BusinessMenuItem;

/**
 * Description of BusinessComponent
 *
 * @author Rémy Abi Khalil <remy.abikhalil@canaltp.fr>
 */
class BusinessComponent extends AbstractBusinessComponent
{
    private $businessPermissionManager;
    private $container;

    public function __construct(
        BusinessPermissionManagerInterface $businessPermissionManager,
        $serviceContainer
    ) {
        $this->businessPermissionManager = $businessPermissionManager;
        $this->container = $serviceContainer;
    }

    public function getId()
    {
        return 'sam_business_component';
    }

    public function getName()
    {
        return 'Sam Business component';
    }

    public function getMenuItems()
    {
        $client = new BusinessMenuItem();
        $client->setAction('#');
        $client->setName($this->container->get('translator')->trans('menu.clients'));
        $client->setRoute('sam_customer_list');

        $user = new BusinessMenuItem();
        $user->setAction('#');
        $user->setName($this->container->get('translator')->trans('menu.users'));
        $user->setRoute('sam_user_list');

        $role = new BusinessMenuItem();
        $role->setAction('#');
        $role->setName($this->container->get('translator')->trans('menu.roles'));
        $role->setRoute('sam_role');

        $perm = new BusinessMenuItem();
        $perm->setAction('#');
        $perm->setName($this->container->get('translator')->trans('menu.permissions'));
        $perm->setRoute('sam_security_business_right_edit');

        $menu = array();
        if ($this->container->get('security.context')->isGranted('BUSINESS_VIEW_USER')
            || $this->container->get('security.context')->isGranted('BUSINESS_MANAGE_USER')) {
            $menu[] = $user;
        }

        if ($this->container->get('security.context')->isGranted('BUSINESS_VIEW_ROLE')
            || $this->container->get('security.context')->isGranted('BUSINESS_MANAGE_ROLE')) {
            $menu[] = $role;
        }

        if ($this->container->get('security.context')->isGranted('BUSINESS_MANAGE_PERMISSION')) {
            $menu[] = $perm;
        }

        if ($this->container->get('security.context')->isGranted('BUSINESS_MANAGE_CLIENT')) {
            $menu[] = $client;
        }

        return $menu;
    }

    public function getPerimetersManager()
    {
        throw new \Exception(sprintf("%s method not implemented", __METHOD__), 1);
    }

    public function getPermissionsManager()
    {
        return $this->businessPermissionManager;
    }
}
