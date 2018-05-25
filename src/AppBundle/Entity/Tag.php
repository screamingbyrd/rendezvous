<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tag
 *
 * @ORM\Table(name="tag")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TagRepository")
 */
class Tag
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
     * @ORM\ManyToOne(targetEntity="AI\AppBundle\Entity\Tag")
     * @ORM\JoinColumn(nullable=true)
     */
    private $category;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="AI\AppBundle\Entity\Candidate", cascade={"persist"})
     */
    private $candidate;

    /**
     * @ORM\ManyToMany(targetEntity="AI\AppBundle\Entity\Offer", cascade={"persist"})
     */
    private $offer;


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
     * Set category
     *
     * @param integer $category
     *
     * @return Tag
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return int
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Tag
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
}

