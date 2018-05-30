<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Candidate
 *
 * @ORM\Table(name="candidate")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CandidateRepository")
 */
class Candidate
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
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\User", cascade={"persist"})
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=false)
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(name="age", type="integer", nullable=false)
     */
    private $age;

    /**
     * @var string
     *
     * @ORM\Column(name="experience", type="string", length=255, nullable=false)
     */
    private $experience;

    /**
     * @var string
     *
     * @ORM\Column(name="license", type="string", length=255, nullable=false)
     */
    private $license;

    /**
     * @var string
     *
     * @ORM\Column(name="diploma", type="string", length=255, nullable=false)
     */
    private $diploma;

    /**
     * @var string
     *
     * @ORM\Column(name="socialMedia", type="string", length=255, nullable=false)
     */
    private $socialMedia;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255)
     */
    private $phone;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Tag", cascade={"persist"})
     * @ORM\JoinTable(name="searched_tag")
     */
    private $searchedTag;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\ContractType", cascade={"persist"})
     */
    private $typeContract;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Tag", cascade={"persist"})
     * @ORM\JoinTable(name="candidate_tag")
     */
    private $tag;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Tag", cascade={"persist"})
     * @ORM\JoinTable(name="candidate_notification")
     */
    private $notification;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Offer", cascade={"persist"})
     */


    private $favorite;

    private $firstName;

    private $lastName;

    private $email;

    private $password;

    /**
     * @var datetime
     *
     * @ORM\Column(name="modifiedDate", type="datetime")
     */
    private $modifiedDate;

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
     * Set name
     *
     * @param string $name
     *
     * @return Candidate
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Candidate
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
     * Set age
     *
     * @param integer $age
     *
     * @return Candidate
     */
    public function setAge($age)
    {
        $this->age = $age;

        return $this;
    }

    /**
     * Get age
     *
     * @return int
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * Set experience
     *
     * @param string $experience
     *
     * @return Candidate
     */
    public function setExperience($experience)
    {
        $this->experience = $experience;

        return $this;
    }

    /**
     * Get experience
     *
     * @return string
     */
    public function getExperience()
    {
        return $this->experience;
    }

    /**
     * Set license
     *
     * @param string $license
     *
     * @return Candidate
     */
    public function setLicense($license)
    {
        $this->license = $license;

        return $this;
    }

    /**
     * Get license
     *
     * @return string
     */
    public function getLicense()
    {
        return $this->license;
    }

    /**
     * Set diploma
     *
     * @param string $diploma
     *
     * @return Candidate
     */
    public function setDiploma($diploma)
    {
        $this->diploma = $diploma;

        return $this;
    }

    /**
     * Get diploma
     *
     * @return string
     */
    public function getDiploma()
    {
        return $this->diploma;
    }

    /**
     * Set socialMedia
     *
     * @param string $socialMedia
     *
     * @return Candidate
     */
    public function setSocialMedia($socialMedia)
    {
        $this->socialMedia = $socialMedia;

        return $this;
    }

    /**
     * Get socialMedia
     *
     * @return string
     */
    public function getSocialMedia()
    {
        return $this->socialMedia;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return Candidate
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set searchedTag
     *
     * @param integer $searchedTag
     *
     * @return Candidate
     */
    public function setSearchedTag($searchedTag)
    {
        $this->searchedTag = $searchedTag;

        return $this;
    }

    /**
     * Get searchedTag
     *
     * @return int
     */
    public function getSearchedTag()
    {
        return $this->searchedTag;
    }

    /**
     * Set typeContract
     *
     * @param integer $typeContract
     *
     * @return Candidate
     */
    public function setTypeContract($typeContract)
    {
        $this->typeContract = $typeContract;

        return $this;
    }

    /**
     * Get typeContract
     *
     * @return int
     */
    public function getTypeContract()
    {
        return $this->typeContract;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->typeContract = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tag = new \Doctrine\Common\Collections\ArrayCollection();
        $this->notification = new \Doctrine\Common\Collections\ArrayCollection();
        $this->favorite = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return Candidate
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add typeContract
     *
     * @param \AppBundle\Entity\ContractType $typeContract
     *
     * @return Candidate
     */
    public function addTypeContract(\AppBundle\Entity\ContractType $typeContract)
    {
        $this->typeContract[] = $typeContract;

        return $this;
    }

    /**
     * Remove typeContract
     *
     * @param \AppBundle\Entity\ContractType $typeContract
     */
    public function removeTypeContract(\AppBundle\Entity\ContractType $typeContract)
    {
        $this->typeContract->removeElement($typeContract);
    }

    /**
     * Add tag
     *
     * @param \AppBundle\Entity\Tag $tag
     *
     * @return Candidate
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
     * Add notification
     *
     * @param \AppBundle\Entity\Tag $notification
     *
     * @return Candidate
     */
    public function addNotification(\AppBundle\Entity\Tag $notification)
    {
        $this->notification[] = $notification;

        return $this;
    }

    /**
     * Remove notification
     *
     * @param \AppBundle\Entity\Tag $notification
     */
    public function removeNotification(\AppBundle\Entity\Tag $notification)
    {
        $this->notification->removeElement($notification);
    }

    /**
     * Get notification
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * Add favorite
     *
     * @param \AppBundle\Entity\Offer $favorite
     *
     * @return Candidate
     */
    public function addFavorite(\AppBundle\Entity\Offer $favorite)
    {
        $this->favorite[] = $favorite;

        return $this;
    }

    /**
     * Remove favorite
     *
     * @param \AppBundle\Entity\Offer $favorite
     */
    public function removeFavorite(\AppBundle\Entity\Offer $favorite)
    {
        $this->favorite->removeElement($favorite);
    }

    /**
     * Get favorite
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFavorite()
    {
        return $this->favorite;
    }

    /**
     * Add searchedTag
     *
     * @param \AppBundle\Entity\Tag $searchedTag
     *
     * @return Candidate
     */
    public function addSearchedTag(\AppBundle\Entity\Tag $searchedTag)
    {
        $this->searchedTag[] = $searchedTag;

        return $this;
    }

    /**
     * Remove searchedTag
     *
     * @param \AppBundle\Entity\Tag $searchedTag
     */
    public function removeSearchedTag(\AppBundle\Entity\Tag $searchedTag)
    {
        $this->searchedTag->removeElement($searchedTag);
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     * @return Candidate
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     * @return Candidate
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     * @return Candidate
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     * @return Candidate
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return datetime
     */
    public function getModifiedDate()
    {
        return $this->modifiedDate;
    }

    /**
     * @param datetime $modifiedDate
     * @return Candidate
     */
    public function setModifiedDate($modifiedDate)
    {
        $this->modifiedDate = $modifiedDate;
        return $this;
    }


}
