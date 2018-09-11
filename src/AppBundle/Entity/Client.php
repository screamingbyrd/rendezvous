<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Client
 *
 * @ORM\Table(name="candidate")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ClientRepository")
 */
class Client
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
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(name="age", type="integer", nullable=true)
     * @Assert\Range(
     *      min = 16,
     *      max = 99,
     *      minMessage = "candidate.age",
     *      maxMessage = "candidate.age"
     * )
     */
    private $age;

    /**
     * @var string
     *
     * @ORM\Column(name="experience", type="string", length=255, nullable=true)
     */
    private $experience;

    /**
     * @var array
     *
     * @ORM\Column(name="license", type="array", nullable=true)
     */
    private $license;

    /**
     * @var string
     *
     * @ORM\Column(name="diploma", type="string", length=255, nullable=true)
     */
    private $diploma;

    /**
     *
     *
     * @ORM\Column(name="socialMedia", type="text", nullable=true)
     */
    private $socialMedia;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
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

    /**
     * @var array
     *
     * @ORM\Column(name="language", type="array", nullable=true)
     */
    private $language;

    /**
     * @var string
     *
     * @Assert\Length(
     *      min = 3,
     *      max = 255,
     *      minMessage = "candidate.minTitle",
     *      maxMessage = "candidate.maxTitle"
     * )
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     */
    private $cv;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     */
    private $coverLetter;

    private $firstName;

    private $lastName;

    private $email;

    private $password;

    /**
     * @var \datetime
     *
     * @ORM\Column(name="modifiedDate", type="datetime")
     */
    private $modifiedDate;

    /**
     * @var \datetime
     *
     * @ORM\Column(name="creationDate", type="datetime")
     */
    private $creationDate;

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
     * @return Client
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
     * @return Client
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
     * @return Client
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
     * @return Client
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
     * @return Client
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
     * @return Client
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
     * @return Client
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
     * @return Client
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
     * @return Client
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
     * @return Client
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
        $this->modifiedDate =  new \datetime();
        $this->creationDate =  new \datetime();
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return Client
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
     * @return Client
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
     * @return Client
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
     * @return Client
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
     * @return Client
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
     * @return Client
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
     * @return Client
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
     * @return Client
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
     * @return Client
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
     * @return Client
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
     * @return Client
     */
    public function setModifiedDate($modifiedDate)
    {
        $this->modifiedDate = $modifiedDate;
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
     * @return Client
     */
    public function setLanguage($language)
    {
        $this->language = $language;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Client
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCv()
    {
        return $this->cv;
    }

    /**
     * @param mixed $cv
     * @return Client
     */
    public function setCv($cv)
    {
        if(isset($cv)) {
            $this->cv = $cv;
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCoverLetter()
    {
        return $this->coverLetter;
    }

    /**
     * @param mixed $coverLetter
     * @return Client
     */
    public function setCoverLetter($coverLetter)
    {
        if(isset($coverLetter)){
            $this->coverLetter = $coverLetter;
        }

        return $this;
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
     * @return Client
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
        return $this;
    }


}
