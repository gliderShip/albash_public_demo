<?php

namespace AppBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait Priceable
{
    /**
     * @var int
     *
     * @ORM\Column(name="quantity", type="integer", options={"unsigned"=true, "default": 1})
     * @Assert\GreaterThanOrEqual(0)
     */
    private $quantity = 1;

    /**
     * @var float
     *
     * @ORM\Column(name="unitPrice", type="decimal", precision=10, scale=2)
     * @Assert\GreaterThan(value="0")
     */
    private $unitPrice;

    /**
     * @var float
     *
     * @ORM\Column(name="totalPrice", type="decimal", precision=10, scale=2)
     * @Assert\GreaterThanOrEqual(value="0")
     */
    private $totalPrice;

    /**
     * Set quantity
     *
     * @param integer $quantity
     *
     * @return $this
     */
    public function setQuantity(int $quantity = 1)
    {
        $this->quantity = $quantity;
        $this->updateTotalPrice();

        return $this;
    }

    /**
     * Add quantity
     *
     * @param integer $quantity
     *
     * @return $this
     */
    public function addQuantity(int $quantity = 1)
    {
        $this->quantity += $quantity;
        $this->updateTotalPrice();

        return $this;
    }

    /**
     * Set quantity
     *
     * @param integer $quantity
     *
     * @return $this
     */
    public function subtractQuantity(int $quantity = 1)
    {
        $this->quantity -= $quantity;
        $this->updateTotalPrice();

        return $this;
    }

    /**
     * Get quantity
     *
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set unitPrice
     *
     * @param float $unitPrice
     *
     * @return $this
     */
    public function setUnitPrice(float $unitPrice)
    {
        $this->unitPrice = $unitPrice;
        $this->updateTotalPrice();

        return $this;
    }

    /**
     * Get unitPrice
     *
     * @return float
     */
    public function getUnitPrice()
    {
        return (float)$this->unitPrice;
    }

    /**
     * Get Price
     *
     * @return float
     */
    public function getTotalPrice()
    {
        return $this->totalPrice;
    }

    public function isAvailable()
    {
        return $this->areAvailable(1);
    }

    public function areAvailable($quantity = 1)
    {
        return $this->quantity >= $quantity;
    }

    private function updateTotalPrice()
    {
        $this->totalPrice = $this->unitPrice * $this->quantity;
    }

}