<?php

namespace AppBundle\Entity;

use AppBundle\Model\Priceable;
use AppBundle\Model\Timestampable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Inventory
 *
 * @ORM\Table(name="inventory", uniqueConstraints={
 *     @ORM\UniqueConstraint(
 *     name="unique_product", columns={"car_id"})
 * }
 *     )
 * @ORM\Entity(repositoryClass="AppBundle\Repository\InventoryRepository")
 * @UniqueEntity("car", message="This car is already in the inventory: {{ value }} !")
 * @ORM\HasLifecycleCallbacks
 */
class Inventory
{
    use Timestampable, Priceable;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Car
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Car")
     * @ORM\JoinColumn(name="car_id", referencedColumnName="id", onDelete="CASCADE", unique=true)
     * @Assert\NotNull()
     */
    private $car;

    /**
     * @var bool
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    private $enabled = true;

    public function __construct()
    {
        $now = new \DateTime();
        $this->createdAt = $now;
        $this->updatedAt = $now;
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
     * @return Car
     */
    public function getCar()
    {
        return $this->car;
    }

    /**
     * @param Car $car
     */
    public function setCar(Car $car)
    {
        $this->car = $car;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     *
     * @return Car
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get enabled
     *
     * @return bool
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    public function __toString()
    {
        return (string)$this->getCar() ?? '';
    }

    public function __toArray()
    {
        return [
            'id' => $this->getId(),
            'car' => $this->getCar()->getId(),
            'quantity' => $this->getQuantity(),
            'unitPrice' => $this->getUnitPrice(),
            'totalPrice' => $this->getTotalPrice(),
            'updatedAt' => $this->getUpdatedAt()->format("Y-m-d H:i:s"),
            'createdAt' => $this->getCreatedAt()->format("Y-m-d H:i:s"),
        ];
    }
}

