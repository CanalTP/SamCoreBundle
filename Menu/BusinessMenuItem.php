<?php

namespace CanalTP\SamCoreBundle\Menu;

use CanalTP\SamEcoreApplicationManagerBundle\Menu\AbstractBusinessMenuItem;

/**
 * Description of BusinessMenuItem
 *
 * @author Rémy Abi Khalil <remy.abikhalil@canaltp.fr>
 * @author Kévin ZIEMIANSKI <kevin.ziemianski@canaltp.fr>
 */
class BusinessMenuItem extends AbstractBusinessMenuItem
{
    protected $action;
    protected $children = array();
    protected $id;
    protected $name;
    protected $route;
    protected $parameters;

    public function setAction($action)
    {
        $this->action = $action;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function addChild($child)
    {
        $this->children[] = $child;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setRoute($route)
    {
        $this->route = $route;
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    public function getParameters()
    {
        return $this->parameters;
    }
}
