<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ad
 *
 * @ORM\Table(name="ad")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AdRepository")
 */
class Ad
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
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     *
     * @ORM\Column(name="link", type="text")
     */
    private $link;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Image", cascade={"persist"})
     */
    private $logo;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Image", cascade={"persist"})
     */
    private $coverImage;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="startDate", type="date")
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="endDate", type="date")
     */
    private $endDate;

    /**
     * @var boolean
     *
     * @ORM\Column(name="archived", type="boolean")

     */
    protected $archived = 0;

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
     * Set title
     *
     * @param string $title
     *
     * @return Ad
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Ad
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return mixed
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param mixed $link
     * @return Ad
     */
    public function setLink($link)
    {
        $this->link = $link;
        return $this;
    }

    /**
     * Set logo
     *
     * @param string $logo
     *
     * @return Ad
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * Get logo
     *
     * @return string
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * @return mixed
     */
    public function getCoverImage()
    {
        return $this->coverImage;
    }

    /**
     * @param mixed $coverImage
     * @return Ad
     */
    public function setCoverImage($coverImage)
    {
        $this->coverImage = $coverImage;
        return $this;
    }


    /**
     * Set startDate
     *
     * @param \DateTime $startDate
     *
     * @return Ad
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     *
     * @return Ad
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
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
     * @return Ad
     */
    public function setArchived($archived)
    {
        $this->archived = $archived;
        return $this;
    }


}

