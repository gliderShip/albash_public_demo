<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * EngineSize
 *
 * @ORM\Table(name="engine_size")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EngineSizeRepository")
 * @UniqueEntity("size", message="This engine size already exist")
 */
class EngineSize
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
     * @var float
     *
     * @ORM\Column(name="size", type="decimal", precision=5, scale=2, unique=true)
     * @Assert\NotBlank()
     * @Assert\Range(
     *      min = 0.5,
     *      max = 999.99,
     *      minMessage = "Min engine size {{ limit }}",
     *      maxMessage = "Max engine size {{ limit }}"
     * )
     */
    private $size;

    /**
     * @var Car[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Car", mappedBy="engineSize")
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
     * Set size
     *
     * @param float $size
     *
     * @return EngineSize
     */
    public function setSize($size = null)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return float
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return Car[]|ArrayCollection
     */
    public function getCars()
    {
        return $this->cars;
    }

    public function __toString()
    {
        return (string)($this->getSize() ?? 'New Engine');
    }

}

