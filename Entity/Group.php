<?php
namespace Novuscom\CMFUserBundle\Entity;

use FOS\UserBundle\Model\Group as BaseGroup;

/**
 * Group
 */
class Group extends BaseGroup
{

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $sites;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->sites = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add sites
     *
     * @param \Novuscom\Bundle\CMFBundle\Entity\Site $sites
     * @return Group
     */
    public function addSite(\Novuscom\Bundle\CMFBundle\Entity\Site $sites)
    {
        $this->sites[] = $sites;
    
        return $this;
    }

    /**
     * Remove sites
     *
     * @param \Novuscom\Bundle\CMFBundle\Entity\Site $sites
     */
    public function removeSite(\Novuscom\Bundle\CMFBundle\Entity\Site $sites)
    {
        $this->sites->removeElement($sites);
    }

    /**
     * Get sites
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSites()
    {
        return $this->sites;
    }
}