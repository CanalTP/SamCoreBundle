<?php

namespace CanalTP\SamCoreBundle\Entity;

use FOS\UserBundle\Model\Group as FosGroup;
use Doctrine\ORM\Mapping as ORM;

/**
 * Application
 */
class Application
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $canonicalName;

    private $bundleName;

    /**
     * @var array
     */
    private $roles;

    //public $rolesByApplication;

    /**
     * @var string
     */
    private $defaultRoute;

    private $role;

    private $perimeters;

    private $customers;

    /**
     * Constructor
     */
    public function __construct($name)
    {
        $this->name = $name;
        $this->roles = new \Doctrine\Common\Collections\ArrayCollection();
        $this->customers = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Application
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Appeler avant la mise à jour d'un objet en base de donnée
     */
    public function onPostLoad()
    {
        $aRoles = array();
        foreach ($this->getuserRoles() as $userRole) {
            $aRoles[] = $userRole->getRole()->getCanonicalName();
        }
        $this->setRoles($aRoles);
    }

    public function getDefaultRoute()
    {
        return $this->defaultRoute;
    }

    public function setDefaultRoute($defaultRoute)
    {
        $this->defaultRoute = $defaultRoute;
    }

    public function setCanonicalName($canoniName)
    {
        $this->canonicalName = $canoniName;

        return $this;
    }

    public function getCanonicalName()
    {
        return $this->canonicalName;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function setRole(Role $role)
    {
        $this->role = $role;

        return $this;
    }

    public function getPerimeters()
    {
        return $this->perimeters;
    }

    public function setPerimeters($perimeters)
    {
        $this->perimeters = $perimeters;

        return $this;
    }

    public function getCustomers()
    {
        return $this->customers;
    }

    public function setCustomers($customers)
    {
        $this->customers = $customers;

        return $this;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param string $role
     *
     * @return Group
     */
    public function addRole($role)
    {
        $this->roles[] = $role;

        return $this;
    }

    /**
     * @param array $roles
     *
     * @return Group
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;

        return $this;
    }

    public function removeRole($role)
    {
    }

    public function getBundleName()
    {
        return $this->bundleName;
    }

    public function setBundleName($bundleName)
    {
        $this->bundleName = $bundleName;

        return $this;
    }
}
