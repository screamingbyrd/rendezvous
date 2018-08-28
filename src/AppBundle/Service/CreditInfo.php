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
    private $boostOffers = 1;
    private $featuredOffer = 2;
    private $featuredEmployer = 2;
    private $buySlot = 5;
    private $oneCredit = 234;
    private $oneCreditWithoutVAT = 200;
    private $fiveCredit = 1111.5;
    private $fiveCreditWithoutVAT = 950;
    private $tenCredit = 2223;
    private $tenCreditWithoutVAT = 1900;

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
    public function getFeaturedEmployer()
    {
        return $this->featuredEmployer;
    }

    /**
     * @param int $featuredEmployer
     * @return CreditInfo
     */
    public function setFeaturedEmployer($featuredEmployer)
    {
        $this->featuredEmployer = $featuredEmployer;
        return $this;
    }

    /**
     * @return int
     */
    public function getBuySlot()
    {
        return $this->buySlot;
    }

    /**
     * @param int $buySlot
     * @return CreditInfo
     */
    public function setBuySlot($buySlot)
    {
        $this->buySlot = $buySlot;
        return $this;
    }

    /**
     * @return int
     */
    public function getOneCredit()
    {
        return $this->oneCredit;
    }

    /**
     * @param int $oneCredit
     * @return CreditInfo
     */
    public function setOneCredit($oneCredit)
    {
        $this->oneCredit = $oneCredit;
        return $this;
    }

    /**
     * @return int
     */
    public function getFiveCredit()
    {
        return $this->fiveCredit;
    }

    /**
     * @param int $fiveCredit
     * @return CreditInfo
     */
    public function setFiveCredit($fiveCredit)
    {
        $this->fiveCredit = $fiveCredit;
        return $this;
    }

    /**
     * @return int
     */
    public function getTenCredit()
    {
        return $this->tenCredit;
    }

    /**
     * @param int $tenCredit
     * @return CreditInfo
     */
    public function setTenCredit($tenCredit)
    {
        $this->tenCredit = $tenCredit;
        return $this;
    }

    /**
     * @return int
     */
    public function getOneCreditWithoutVAT()
    {
        return $this->oneCreditWithoutVAT;
    }

    /**
     * @param int $oneCreditWithoutVAT
     * @return CreditInfo
     */
    public function setOneCreditWithoutVAT($oneCreditWithoutVAT)
    {
        $this->oneCreditWithoutVAT = $oneCreditWithoutVAT;
        return $this;
    }

    /**
     * @return float
     */
    public function getFiveCreditWithoutVAT()
    {
        return $this->fiveCreditWithoutVAT;
    }

    /**
     * @param float $fiveCreditWithoutVAT
     * @return CreditInfo
     */
    public function setFiveCreditWithoutVAT($fiveCreditWithoutVAT)
    {
        $this->fiveCreditWithoutVAT = $fiveCreditWithoutVAT;
        return $this;
    }

    /**
     * @return int
     */
    public function getTenCreditWithoutVAT()
    {
        return $this->tenCreditWithoutVAT;
    }

    /**
     * @param int $tenCreditWithoutVAT
     * @return CreditInfo
     */
    public function setTenCreditWithoutVAT($tenCreditWithoutVAT)
    {
        $this->tenCreditWithoutVAT = $tenCreditWithoutVAT;
        return $this;
    }






}