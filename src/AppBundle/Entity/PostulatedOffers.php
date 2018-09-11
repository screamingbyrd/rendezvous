<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PostulatedOffers
 *
 * @ORM\Table(name="postulated_offers")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PostulatedOffersRepository")
 */
class PostulatedOffers
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Client", cascade={"persist"})
     */
    private $candidate;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Offer", cascade={"persist"})
     */
    private $offer;

    /**
     *
     * @ORM\Column(name="coverLetter", type="text")
     */
    private $coverLetter;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var boolean
     *
     * @ORM\Column(name="archived", type="boolean")
     */
    private $archived;


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
     * Set candidate
     *
     * @param integer $candidate
     *
     * @return PostulatedOffers
     */
    public function setClient($candidate)
    {
        $this->candidate = $candidate;

        return $this;
    }

    /**
     * Get candidate
     *
     * @return int
     */
    public function getClient()
    {
        return $this->candidate;
    }

    /**
     * Set offer
     *
     * @param integer $offer
     *
     * @return PostulatedOffers
     */
    public function setOffer($offer)
    {
        $this->offer = $offer;

        return $this;
    }

    /**
     * Get offer
     *
     * @return int
     */
    public function getOffer()
    {
        return $this->offer;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return PostulatedOffers
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return bool
     */
    public function isArchived()
    {
        return $this->archived;
    }

    /**
     * @param bool $archived
     * @return PostulatedOffers
     */
    public function setArchived($archived)
    {
        $this->archived = $archived;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCoverLetter()
    {
        return $this->coverLetter;
    }

    /**
     * @param mixed $coverLetter
     * @return PostulatedOffers
     */
    public function setCoverLetter($coverLetter)
    {
        $this->coverLetter = $coverLetter;
        return $this;
    }



    /**
     * Constructor
     */
    public function __construct()
    {
        $this->archived = 0;
    }
}
