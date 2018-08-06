<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Employer
 *
 * @ORM\Table(name="employer")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EmployerRepository")
 */
class Employer
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
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\User", cascade={"persist","remove"}, mappedBy="employer")
     *
     */
    private $user;

    /**
     * @var string
     *
     * @Assert\Length(
     *      min = 3,
     *      max = 50,
     *      minMessage = "employer.minName",
     *      maxMessage = "employer.maxName"
     * )
     *
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     *
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(name="credit", type="integer")
     */
    private $credit;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Image", cascade={"persist"})
     */
    private $logo;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Image", cascade={"persist"})
     */
    private $coverImage;

    /**
     * @var string
     *-
     * @ORM\Column(name="location", type="string", length=255, nullable=true)
     */
    private $location;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Tag", cascade={"persist"})
     * @ORM\JoinTable(name="employer_tag")
     */
    private $tag;


    /**
     * @var string
     *
     *
     * @Assert\Length(
     *      min = 3,
     *      max = 50,
     *      minMessage = "employer.minName",
     *      maxMessage = "employer.maxName"
     * )
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     */
    private $phone;

    private $email;

    private $password;

    private $firstName;

    private $lastName;

    /**
     * @var \datetime
     *
     * @ORM\Column(name="creationDate", type="datetime")
     */
    private $creationDate;

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     * @return Employer
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
     * @return Employer
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
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
     * @return Employer
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
     * @return Employer
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
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
     * @return Employer
     */
    public function setLocation($location)
    {
        $this->location = $location;
        return $this;
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
     * Set name
     *
     * @param string $name
     *
     * @return Employer
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
     * @return Employer
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
     * Set credit
     *
     * @param integer $credit
     *
     * @return Employer
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
     * Set logo
     *
     * @param integer $logo
     *
     * @return Employer
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * Get logo
     *
     * @return int
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Set coverImage
     *
     * @param string $coverImage
     *
     * @return Employer
     */
    public function setCoverImage($coverImage)
    {
        $this->coverImage = $coverImage;

        return $this;
    }

    /**
     * Get coverImage
     *
     * @return string
     */
    public function getCoverImage()
    {
        return $this->coverImage;
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
     * @return Employer
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->user = new \Doctrine\Common\Collections\ArrayCollection();
        $this->creationDate =  new \datetime();
    }

    /**
     * Add user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return Employer
     */
    public function addUser(\AppBundle\Entity\User $user)
    {
        $this->user[] = $user;

        $user->setEmployer($this);

        return $this;
    }

    /**
     * Remove user
     *
     * @param \AppBundle\Entity\User $user
     */
    public function removeUser(\AppBundle\Entity\User $user)
    {
        $this->user->removeElement($user);
    }

    /**
     * Get user
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add tag
     *
     * @param \AppBundle\Entity\Tag $tag
     *
     * @return Employer
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
}
