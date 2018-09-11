<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * logCredit
 *
 * @ORM\Table(name="log_credit")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\LogCreditRepository")
 */
class LogCredit
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
     * @var int
     *
     * @ORM\Column(name="credit", type="integer")
     */
    private $credit;


    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float")
     */
    private $price;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Pro")
     * @ORM\JoinColumn(nullable=false)
     */
    private $employer;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="location", type="string", length=255, nullable=true)
     */
    private $location;

    /**
     * @var string
     *
     * @ORM\Column(name="zipcode", type="string", length=255, nullable=true)
     */
    private $zipcode;


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
     * Set credit
     *
     * @param integer $credit
     *
     * @return logCredit
     */
    public function setCredit($credit)
    {
        $this->credit = $credit;

        return $this;
    }

    /**
     * Get credit
     *
     * @return int
     */
    public function getCredit()
    {
        return $this->credit;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return logCredit
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
     * Set employerId
     *
     * @param integer $employer
     *
     * @return logCredit
     */
    public function setPro($employer)
    {
        $this->employer = $employer;

        return $this;
    }

    /**
     * Get employer
     *
     * @return int
     */
    public function getPro()
    {
        return $this->employer;
    }

    /**
     * Set price
     *
     * @param float $price
     *
     * @return LogCredit
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return LogCredit
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     * @return LogCredit
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param string $location
     * @return LogCredit
     */
    public function setLocation($location)
    {
        $this->location = $location;
        return $this;
    }

    /**
     * @return string
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }

    /**
     * @param string $zipcode
     * @return LogCredit
     */
    public function setZipcode($zipcode)
    {
        $this->zipcode = $zipcode;
        return $this;
    }


}
