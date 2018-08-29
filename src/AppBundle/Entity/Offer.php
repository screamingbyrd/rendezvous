<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Offer
 *
 * @ORM\Table(name="offer")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\OfferRepository")
 */
class Offer
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
     * @var \DateTime
     *
     * @ORM\Column(name="startDate", type="datetime")
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="endDate", type="datetime")
     */
    private $endDate;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Employer", cascade={"persist"})
     *
     */
    private $employer;

    /**
     * @var string
     *
     * @ORM\Column(name="location", type="string", length=255)
     * @Assert\NotNull()
     */
    private $location;

    /**
     *
     * @ORM\Column(name="description", type="text")
     * @Assert\NotNull()
     */
    private $description;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Image", cascade={"persist"})
     */
    private $image;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var int
     *
     * @ORM\Column(name="countView", type="integer")
     */
    private $countView;

    /**
     * @var int
     *
     * @ORM\Column(name="countContact", type="integer")
     */
    private $countContact;

    /**
     * @var string
     *
     * @ORM\Column(name="wage", type="string", nullable=true)
     */
    private $wage;

    /**
     * @var string
     *
     * @ORM\Column(name="experience", type="string", length=255, nullable=true)
     */
    private $experience;

    /**
     * @var array
     *
     * @ORM\Column(name="benefits", type="array", nullable=true)
     */
    private $benefits;

    /**
     * @var string
     *
     * @ORM\Column(name="diploma", type="string", length=255, nullable=true)
     */
    private $diploma;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ContractType")
     */
    private $contractType;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Tag", cascade={"persist"})
     */
    private $tag;

    /**
     * @var \datetime
     *
     * @ORM\Column(name="creationDate", type="datetime")
     */
    private $creationDate;

    /**
     * @var \datetime
     *
     * @ORM\Column(name="updateDate", type="datetime", nullable=true)
     */
    private $updateDate;

    /**
     * @var boolean
     *
     * @ORM\Column(name="archived", type="boolean")

     */
    protected $archived = 0;

    /**
     * @var \datetime
     *
     * @ORM\Column(name="availableDate", type="datetime", nullable=true)
     */
    private $availableDate;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Slot")
     * @ORM\JoinColumn(name="slot_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     *
     */
    private $slot;

    /**
     * @var array
     *
     * @ORM\Column(name="language", type="array", nullable=true)
     */
    private $language;

    private $offerUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="link", type="string", nullable=true)
     */
    private $link;

    /**
     * @var boolean
     *
     * @ORM\Column(name="validated", type="boolean", nullable=true)

     */
    protected $validated;

    public function __toString()
    {
        return (string)$this->getTag();
    }

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
     * Set startDate
     *
     * @param \DateTime $startDate
     *
     * @return Offer
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
     * @return Offer
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
     * Set description
     *
     * @param string $description
     *
     * @return Offer
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
     * Set image
     *
     * @param integer $image
     *
     * @return Offer
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return int
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Offer
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
     * Set countView
     *
     * @param integer $countView
     *
     * @return Offer
     */
    public function setCountView($countView)
    {
        $this->countView = $countView;

        return $this;
    }

    /**
     * Get countView
     *
     * @return int
     */
    public function getCountView()
    {
        return $this->countView;
    }

    /**
     * @return int
     */
    public function getCountContact()
    {
        return $this->countContact;
    }

    /**
     * @param int $countContact
     * @return Offer
     */
    public function setCountContact($countContact)
    {
        $this->countContact = $countContact;
        return $this;
    }

    /**
     * @return int
     */
    public function getWage()
    {
        return $this->wage;
    }

    /**
     * @param int $wage
     * @return Offer
     */
    public function setWage($wage)
    {
        $this->wage = $wage;
        return $this;
    }

    /**
     * @return string
     */
    public function getExperience()
    {
        return $this->experience;
    }

    /**
     * @param string $experience
     * @return Offer
     */
    public function setExperience($experience)
    {
        $this->experience = $experience;
        return $this;
    }

    /**
     * @return string
     */
    public function getBenefits()
    {
        return $this->benefits;
    }

    /**
     * @param string $benefits
     * @return Offer
     */
    public function setBenefits($benefits)
    {
        $this->benefits = $benefits;
        return $this;
    }

    /**
     * @return string
     */
    public function getDiploma()
    {
        return $this->diploma;
    }

    /**
     * @param string $diploma
     * @return Offer
     */
    public function setDiploma($diploma)
    {
        $this->diploma = $diploma;
        return $this;
    }

    /**
     * @return int
     */
    public function getContractType()
    {
        return $this->contractType;
    }

    /**
     * @param int $contractType
     * @return Offer
     */
    public function setContractType($contractType)
    {
        $this->contractType = $contractType;
        return $this;
    }


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tag = new \Doctrine\Common\Collections\ArrayCollection();
        $this->creationDate =  new \Datetime();
        $this->updateDate = new \DateTime();


    }

    /**
     * Add tag
     *
     * @param \AppBundle\Entity\Tag $tag
     *
     * @return Offer
     */
    public function addTag(\AppBundle\Entity\Tag $tag)
    {
        $this->tag[] = $tag;

        return $this;
    }

    /**
     * Remove tag
     *
     * @param \AppBundle\Entity\Tag $tag
     */
    public function removeTag(\AppBundle\Entity\Tag $tag)
    {
        $this->tag->removeElement($tag);
    }

    /**
     * Get tag
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @return \datetime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param \datetime $creationDate
     * @return Offer
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isArchived()
    {
        return $this->archived;
    }

    /**
     * @param boolean $archived
     * @return Offer
     */
    public function setArchived($archived)
    {
        $this->archived = $archived;
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
     * @return Offer
     */
    public function setLocation($location)
    {
        $this->location = $location;
        return $this;
    }

    /**
     * Set employer
     *
     * @param \AppBundle\Entity\Employer $employer
     *
     * @return Offer
     */
    public function setEmployer(\AppBundle\Entity\Employer $employer = null)
    {
        $this->employer = $employer;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\Employer
     */
    public function getEmployer()
    {
        return $this->employer;
    }

    /**
     * @return \datetime
     */
    public function getUpdateDate()
    {
        return $this->updateDate;
    }

    /**
     * @param \datetime $updateDate
     * @return Offer
     */
    public function setUpdateDate($updateDate)
    {
        $this->updateDate = $updateDate;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        $now = new \datetime();
        return (($now >= $this->startDate) && ($now <= $this->endDate) || (isset($this->slot) && $now >= $this->creationDate));
    }


    /**
     * Get archived
     *
     * @return boolean
     */
    public function getArchived()
    {
        return $this->archived;
    }

    /**
     * @return \datetime
     */
    public function getAvailableDate()
    {
        return $this->availableDate;
    }

    /**
     * @param \datetime $availableDate
     * @return Offer
     */
    public function setAvailableDate($availableDate)
    {
        $this->availableDate = $availableDate;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSlot()
    {
        return $this->slot;
    }

    /**
     * @param mixed $slot
     * @return Offer
     */
    public function setSlot($slot)
    {
        $this->slot = $slot;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOfferUrl()
    {
        return $this->offerUrl;
    }

    /**
     * @param mixed $slot
     * @return string
     */
    public function setOfferUrl($offerUrl)
    {
        $this->offerUrl = $offerUrl;
        return $this;
    }

    /**
     * @return array
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param array $language
     * @return Offer
     */
    public function setLanguage($language)
    {
        $this->language = $language;
        return $this;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string $link
     * @return Offer
     */
    public function setLink($link)
    {
        $this->link = $link;
        return $this;
    }

    /**
     * @return bool
     */
    public function isValidated()
    {
        return $this->validated;
    }

    /**
     * @param bool $validated
     * @return Offer
     */
    public function setValidated($validated)
    {
        $this->validated = $validated;
        return $this;
    }



    /**
     * Get validated
     *
     * @return boolean
     */
    public function getValidated()
    {
        return $this->validated;
    }
}
