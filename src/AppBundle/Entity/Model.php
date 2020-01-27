<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Model
 *
 * @ORM\Table(name="model", uniqueConstraints={
 *     @ORM\UniqueConstraint(
 *     name="unique_model", columns={"name", "make_id"})
 * }
 *     )
 * @UniqueEntity(
 *     fields={"name", "make"},
 *     errorPath="name",
 *     message="This model already exists!"
 * )
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ModelRepository")
 */
class Model
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
     * @ORM\Column(name="name", type="string", length=50)
     * @Assert\NotBlank()
     * @Assert\Length(
     *      min = 2,
     *      max = 50,
     *      minMessage = "Model name must be at least {{ limit }} characters long",
     *      maxMessage = "Model name cannot be longer than {{ limit }} characters"
     * )
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Make", inversedBy="models", cascade={"persist"})
     * @ORM\JoinColumn(name="make_id", referencedColumnName="id", onDelete="CASCADE")
     * @Assert\NotNull()
     */
    private $make;

    /**
     * @var Car[] | Collection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Car", mappedBy="model")
     * @ORM\OrderBy({"name": "ASC"})
     */
    private $cars;

    public function __construct()
    {
        $this->cars = new ArrayCollection();
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
     * Set make
     *
     * @param Make $make
     *
     * @return Model
     */
    public function setMake(Make $make = null)
    {
        $this->make = $make;

        return $this;
    }

    /**
     * Get make
     *
     * @return Make
     */
    public function getMake()
    {
        return $this->make;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Model
     */
    public function setName($name = null)
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
     * @return Car[]|Collection
     */
    public function getCars()
    {
        return $this->cars;
    }

    public function __toString()
    {
        if($this->getName()) {
            return $this->getName() .' - ' . $this->getMake();
        }

        return '';
    }
}

