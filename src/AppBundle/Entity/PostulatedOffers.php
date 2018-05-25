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
     * @ORM\OneToOne(targetEntity="AI\AppBundle\Entity\Candidate", cascade={"persist"})
     */
    private $candidate;

    /**
     * @ORM\OneToOne(targetEntity="AI\AppBundle\Entity\Offer", cascade={"persist"})
     */
    private $offer;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;


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
    public function setCandidate($candidate)
    {
        $this->candidate = $candidate;

        return $this;
    }

    /**
     * Get candidate
     *
     * @return int
     */
    public function getCandidate()
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
}

