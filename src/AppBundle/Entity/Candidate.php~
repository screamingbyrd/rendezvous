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
     * @ORM\OneToOne(targetEntity="AI\AppBundle\Entity\User", cascade={"persist"})
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(name="age", type="integer")
     */
    private $age;

    /**
     * @var string
     *
     * @ORM\Column(name="experience", type="string", length=255)
     */
    private $experience;

    /**
     * @var string
     *
     * @ORM\Column(name="license", type="string", length=255)
     */
    private $license;

    /**
     * @var string
     *
     * @ORM\Column(name="diploma", type="string", length=255)
     */
    private $diploma;

    /**
     * @var string
     *
     * @ORM\Column(name="socialMedia", type="string", length=255)
     */
    private $socialMedia;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255)
     */
    private $phone;

    /**
     * @var int
     *
     * @ORM\Column(name="searchedTag", type="integer")
     */
    private $searchedTag;

    /**
     * @ORM\ManyToMany(targetEntity="AI\AppBundle\Entity\ContractType", cascade={"persist"})
     */
    private $typeContract;

    /**
     * @ORM\ManyToMany(targetEntity="AI\AppBundle\Entity\Tag", cascade={"persist"})
     */
    private $tag;

    /**
     * @ORM\ManyToMany(targetEntity="AI\AppBundle\Entity\Tag", cascade={"persist"})
     */
    private $notification;

    /**
     * @ORM\ManyToMany(targetEntity="AI\AppBundle\Entity\Offer", cascade={"persist"})
     */
    private $favorite;




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
}

