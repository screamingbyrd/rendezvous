<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Notification
 *
 * @ORM\Table(name="notification")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\NotificationRepository")
 */
class Notification
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Candidate")
     */
    private $candidate;

    /**
     * @var string
     *
     * @ORM\Column(name="typeNotification", type="string", length=255)
     */
    private $typeNotification;

    /**
     * @var string
     *
     * @ORM\Column(name="elementId", type="string", length=255, nullable=true)
     */
    private $elementId;

    /**
     * @var string
     *
     * @ORM\Column(name="mail", type="string", length=255, nullable=true)
     */
    private $mail;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="uid", type="string", length=255)
     */
    private $uid;


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
     * @return Notification
     */
    public function setCandidate($candidate)
    {
        $this->candidate = $candidate;

        return $this;
    }

    /**
     * Get candidate
     *
     * @return string
     */
    public function getCandidate()
    {
        return $this->candidate;
    }

    /**
     * Set type
     *
     * @param string $typeNotification
     *
     * @return Notification
     */
    public function setTypeNotification($typeNotification)
    {
        $this->typeNotification = $typeNotification;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getTypeNotification()
    {
        return $this->typeNotification;
    }

    /**
     * Set elementId
     *
     * @param integer $elementId
     *
     * @return Notification
     */
    public function setElementId($elementId)
    {
        $this->elementId = $elementId;

        return $this;
    }

    /**
     * Get elementId
     *
     * @return int
     */
    public function getElementId()
    {
        return $this->elementId;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     * @return Notification
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return string
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * @param string $mail
     * @return Notification
     */
    public function setMail($mail)
    {
        $this->mail = $mail;
        return $this;
    }

    /**
     * @return string
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * @param string $uid
     * @return Notification
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
        return $this;
    }



}

