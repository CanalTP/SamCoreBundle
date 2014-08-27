<?php

namespace CanalTP\SamCoreBundle\Entity;

/**
 * AbstractEntity
 */
abstract class AbstractEntity
{
    /**
     * @var datetime $created
     */
    protected $created;

    /**
     * @var datetime $updated
     */
    protected $updated;

    /**
     * Get creation date
     *
     * @return DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Get updated date
     *
     * @return DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    public function __clone()
    {
        $this->created = new \DateTime();
        $this->updated = new \DateTime();
    }
}
