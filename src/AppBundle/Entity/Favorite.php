<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Favorite
 *
 * @ORM\Table(name="favorite")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FavoriteRepository")
 */
class Favorite
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Client")
     */
    private $candidate;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Offer")
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
     * Set candidate
     *
     * @param string $candidate
     *
     * @return Favorite
     */
    public function setClient($candidate)
    {
        $this->candidate = $candidate;

        return $this;
    }

    /**
     * Get candidate
     *
     * @return string
     */
    public function getClient()
    {
        return $this->candidate;
    }

    /**
     * Set offer
     *
     * @param string $offer
     *
     * @return Favorite
     */
    public function setOffer($offer)
    {
        $this->offer = $offer;

        return $this;
    }

    /**
     * Get offer
     *
     * @return string
     */
    public function getOffer()
    {
        return $this->offer;
    }
}

