<?php

namespace CanalTP\SamCoreBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use CanalTP\SamEcoreApplicationManagerBundle\Menu\BusinessMenuItemInterface;

class Builder implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function mainMenu(FactoryInterface $factory, array $options)
    {
        $translator = $this->container->get('translator');
        $menu = $factory->createItem('root');
        $menu->setChildrenAttributes(array('class' => 'navbar-nav'));
        $menu->addChild(
            $translator->trans('ctp_user.user._menu'),
            array('route' => 'sam_user_list')
        );
        $menu->addChild(
            "Role",
            array('route' => 'sam_role')
        );

        $menu->addChild(
            "Permission",
            array('route' => 'sam_security_business_right_edit')
        );

        return $menu;
    }

    public function businessMenu(FactoryInterface $factory, array $options)
    {
        $request = $this->container->get('request');
        $businessComponent = $this->container->get('sam.business_component');
        $app = $this->container->get('canal_tp_sam.application.finder')->getCurrentApp();
        $menu = $factory->createItem('root');
        $menu->setCurrentUri($request->getRequestUri());

        if ($app) {
            $businessMenu = $businessComponent->getBusinessComponent($app->getCanonicalName())->getMenuItems();

            $menu->setChildrenAttributes(array('class' => 'navbar-nav'));
            foreach ($businessMenu as $menuItem) {
                $this->generateKnpMenu($menuItem, $menu);
            }
        } else {
            $menu->setChildrenAttributes(array('class' => 'navbar-nav'));
        }

        return $menu;
    }

    protected function generateKnpMenu(BusinessMenuItemInterface $menuItem, $knpMenu, $parentName = null)
    {
        $options = array();
        if (!is_null($menuItem->getRoute()) && $menuItem->getRoute() != '') {
            $options += array('route' => $menuItem->getRoute());
        }
        $options += array('routeParameters' => $menuItem->getParameters());
        $attributes = $menuItem->getAttributes();
        if ($menuItem->isActive($this->container->get('request_stack')->getCurrentRequest()->get('_route'))) {
            $attributes += array('class' => 'active');
        }
        $options += array('attributes' => $attributes);
        if (!is_null($parentName)) {
            $knpMenu[$parentName]->addChild(
                $menuItem->getName(),
                $options
            );
        } else {
            $knpMenu->addChild(
                $menuItem->getName(),
                $options
            );
        }
        foreach ($menuItem->getChildren() as $child) {
            $this->generateKnpMenu($child, $knpMenu, $menuItem->getName());
        }
    }
}
