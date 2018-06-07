<?php
/**
 * Created by PhpStorm.
 * User: Altea IT
 * Date: 29/05/2018
 * Time: 10:53
 */

namespace AppBundle\Service;

use AppBundle\Entity\User;

class CreditInfo
{

    private $publishOffer = 1;
    private $boostOffers = 3;
    private $featuredOffer = 10;
    private $featuredCompany = 10;

    public function __construct()
    {
    }

    /**
     * @return int
     */
    public function getPublishOffer()
    {
        return $this->publishOffer;
    }

    /**
     * @param int $publishOffer
     * @return CreditInfo
     */
    public function setPublishOffer($publishOffer)
    {
        $this->publishOffer = $publishOffer;
        return $this;
    }

    /**
     * @return int
     */
    public function getBoostOffers()
    {
        return $this->boostOffers;
    }

    /**
     * @param int $boostOffers
     * @return CreditInfo
     */
    public function setBoostOffers($boostOffers)
    {
        $this->boostOffers = $boostOffers;
        return $this;
    }

    /**
     * @return int
     */
    public function getFeaturedOffer()
    {
        return $this->featuredOffer;
    }

    /**
     * @param int $featuredOffer
     * @return CreditInfo
     */
    public function setFeaturedOffer($featuredOffer)
    {
        $this->featuredOffer = $featuredOffer;
        return $this;
    }

    /**
     * @return int
     */
    public function getFeaturedCompany()
    {
        return $this->featuredCompany;
    }

    /**
     * @param int $featuredCompany
     * @return CreditInfo
     */
    public function setFeaturedCompany($featuredCompany)
    {
        $this->featuredCompany = $featuredCompany;
        return $this;
    }


}