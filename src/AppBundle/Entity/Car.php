<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Car
 *
 * @ORM\Table(name="car")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CarRepository")
 * @UniqueEntity("name", message="Duplicate name. A car with the same name exist: {{ value }} !")
 * @UniqueEntity("registration", message="This plate is already assgined to another car: {{ value }} !")
 */
class Car
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
     * @ORM\Column(name="name", type="string", length=127, unique=true)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Model", inversedBy="cars")
     * @ORM\JoinColumn(name="model_id", referencedColumnName="id", onDelete="CASCADE")
     * @Assert\NotNull()
     * @Assert\Valid()
     */
    private $model;

    /**
     * @var Registration
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Registration", inversedBy="car")
     * @ORM\JoinColumn(name="registration_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     * @Assert\Valid()
     */
    private $registration;

    /**
     * @var EngineSize
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\EngineSize", inversedBy="cars")
     * @ORM\JoinColumn(name="engine_id", referencedColumnName="id")
     * @Assert\NotNull()
     */
    private $engineSize;

    /**
     * @var Tag[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Tag", inversedBy="cars", cascade={"persist"})
     * @ORM\JoinTable(name="cars_tags")
     * @ORM\OrderBy({"name": "ASC"})
     */
    private $tags;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
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
     * Set model
     *
     * @param Model $model
     *
     * @return Car
     */
    public function setModel(Model $model = null)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Get model
     *
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set registration
     *
     * @param Registration $registration
     *
     * @return Car
     */
    public function setRegistration(Registration $registration = null)
    {
        $this->registration = $registration;

        return $this;
    }

    /**
     * Get registration
     *
     * @return Registration|null
     */
    public function getRegistration()
    {
        return $this->registration;
    }

    /**
     * @return EngineSize
     */
    public function getEngineSize()
    {
        return $this->engineSize;
    }

    /**
     * @param EngineSize $engineSize
     */
    public function setEngineSize(EngineSize $engineSize = null)
    {
        $this->engineSize = $engineSize;
    }

    public function addTag(Tag ...$tags)
    {
        foreach ($tags as $tag) {
            if (!$this->tags->contains($tag)) {
                $tag->addCar($this);
                $this->tags->add($tag);
            }
        }
    }

    public function removeTag(Tag $tag)
    {
        $this->tags->removeElement($tag);
    }

    public function getTags(): Collection
    {
        return $this->tags;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Car
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

    public function __toString()
    {
        return $this->getName() ?? '';
    }
}

