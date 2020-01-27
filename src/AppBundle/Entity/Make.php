<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Make
 *
 * @ORM\Table(name="make")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MakeRepository")
 * @UniqueEntity("name", message="Duplicate name. A Make with the same name exist: {{ value }} !")
 */
class Make
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
     * @ORM\Column(name="name", type="string", length=50, unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(
     *      min = 2,
     *      max = 50,
     *      minMessage = "Make name must be at least {{ limit }} characters long",
     *      maxMessage = "Make name cannot be longer than {{ limit }} characters"
     * )
     */
    private $name;

    /**
     * @var Model[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Model", mappedBy="make")
     */
    private $models;

    public function __construct()
    {
        $this->models = new ArrayCollection();
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
     * @return Make
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
     * @return Model[] | ArrayCollection
     */
    public function getModels(): Collection
    {
        return $this->models;
    }

    /**
     * @param Model[]|Collection $models
     */
    public function setModels(Model ...$models)
    {
        $this->models = new ArrayCollection();

        foreach ($models as $model) {
            $this->addModel($model);
        }
    }

    /**
     * @param Model $model
     */
    public function addModel(Model $model)
    {
        if (!$this->models->contains($model)) {
            $model->setMake($this);
            $this->models[] = $model;
        }
    }

    /**
     * @param Model $model
     */
    public function removeModel(Model $model)
    {
        if ($this->models->contains($model)) {
            $this->models->removeElement($model);
            $model->setMake(null);
        }
    }

    public function __toString()
    {
        return $this->getName() ?? '';
    }

}

