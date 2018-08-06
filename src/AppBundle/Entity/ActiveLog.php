<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ActiveLog
 *
 * @ORM\Table(name="active_log")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ActiveLogRepository")
 */
class ActiveLog
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
     * @ORM\Column(name="offerId", type="integer")
     */
    private $offerId;

    /**
     * @var int
     *
     * @ORM\Column(name="slotId", type="integer", nullable=true)
     */
    private $slotId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="startDate", type="date")
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="endDate", type="date", nullable=true)
     */
    private $endDate;


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
     * Set offerId
     *
     * @param integer $offerId
     *
     * @return ActiveLog
     */
    public function setOfferId($offerId)
    {
        $this->offerId = $offerId;

        return $this;
    }

    /**
     * Get offerId
     *
     * @return int
     */
    public function getOfferId()
    {
        return $this->offerId;
    }

    /**
     * Set slotId
     *
     * @param integer $slotId
     *
     * @return ActiveLog
     */
    public function setSlotId($slotId)
    {
        $this->slotId = $slotId;

        return $this;
    }

    /**
     * Get slotId
     *
     * @return int
     */
    public function getSlotId()
    {
        return $this->slotId;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param \DateTime $startDate
     * @return ActiveLog
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param \DateTime $endDate
     * @return ActiveLog
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
        return $this;
    }


}

