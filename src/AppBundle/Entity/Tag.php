<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tag
 *
 * @ORM\Table(name="tag")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TagRepository")
 */
class Tag
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Tag")
     * @ORM\JoinColumn(nullable=true)
     */
    private $category;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Candidate", cascade={"persist"})
     */
    private $candidate;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Offer", cascade={"persist"})
     */
    private $offer;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set category
     *
     * @param integer $category
     *
     * @return Tag
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return int
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Tag
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
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->candidate = new \Doctrine\Common\Collections\ArrayCollection();
        $this->offer = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add candidate
     *
     * @param \AppBundle\Entity\Candidate $candidate
     *
     * @return Tag
     */
    public function addCandidate(\AppBundle\Entity\Candidate $candidate)
    {
        $this->candidate[] = $candidate;

        return $this;
    }

    /**
     * Remove candidate
     *
     * @param \AppBundle\Entity\Candidate $candidate
     */
    public function removeCandidate(\AppBundle\Entity\Candidate $candidate)
    {
        $this->candidate->removeElement($candidate);
    }

    /**
     * Get candidate
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCandidate()
    {
        return $this->candidate;
    }

    /**
     * Add offer
     *
     * @param \AppBundle\Entity\Offer $offer
     *
     * @return Tag
     */
    public function addOffer(\AppBundle\Entity\Offer $offer)
    {
        $this->offer[] = $offer;

        return $this;
    }

    /**
     * Remove offer
     *
     * @param \AppBundle\Entity\Offer $offer
     */
    public function removeOffer(\AppBundle\Entity\Offer $offer)
    {
        $this->offer->removeElement($offer);
    }

    /**
     * Get offer
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOffer()
    {
        return $this->offer;
    }
}
