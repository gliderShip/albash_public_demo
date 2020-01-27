<?php

namespace AppBundle\Entity;

use AppBundle\Model\Expirable;
use AppBundle\Model\Priceable;
use AppBundle\Model\Timestampable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Item
 *
 * @ORM\Table(name="item",
 *  indexes={
 *     @ORM\Index( name="expireAt_idx", columns={"expire_at"} ),
 *     @ORM\Index(name="status_idx", columns={"status"})
 * },
 *  uniqueConstraints={
 *     @ORM\UniqueConstraint( name="unique_product", columns={"order_id", "car_id"} )
 * }
 *     )
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ItemRepository")
 * @UniqueEntity(
 *     fields={"order", "car"},
 *     errorPath="car",
 *     message="This tem is already in your cart. Please update the quantity if you want more! {{value}}"
 * )
 * @ORM\HasLifecycleCallbacks
 */
class Item
{
    use Timestampable, Priceable, Expirable;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Order
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Order", inversedBy="items")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", onDelete="CASCADE")
     * @Assert\NotNull()
     */
    private $order;

    /**
     * @var Car
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Car")
     * @ORM\JoinColumn(name="car_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull()
     */
    private $car;

    /**
     * @var float
     *
     * @ORM\Column(name="lastCheckedStockPrice", type="decimal", precision=10, scale=2)
     * @Assert\GreaterThan(value="0")
     */
    private $lastCheckedStockPrice;

    public function __construct(Car $car = null, float $unitPrice = 0, $quantity = 1, \DateTime $expireAt)
    {
        $now = new \DateTime();
        $this->createdAt = $now;
        $this->updatedAt = $now;

        $this->car = $car;
        $this->unitPrice = $unitPrice;
        $this->lastCheckedStockPrice = $unitPrice;
        $this->quantity = $quantity;
        $this->updateTotalPrice();
        $this->status = Order::STATUS['open'];
        $this->expireAt = $expireAt;
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
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param mixed $order
     */
    public function setOrder(Order $order = null)
    {
        $this->order = $order;
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
    public function setCar(Car $car = null)
    {
        $this->car = $car;
    }

    /**
     * @param string $carId
     * @return bool|int
     */
    public function hasProductId($carId)
    {
        if ($this->car->getId() == $carId) {
            return $this->getQuantity();
        }

        return false;
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
        $this->lastCheckedStockPrice = $unitPrice;
        $this->updateTotalPrice();

        return $this;
    }

    /**
     * @return float
     */
    public function getLastCheckedStockPrice(): float
    {
        return $this->lastCheckedStockPrice;
    }

    /**
     * @param float $lastCheckedStockPrice
     */
    public function setLastCheckedStockPrice(float $lastCheckedStockPrice = 0): void
    {
        $this->lastCheckedStockPrice = $lastCheckedStockPrice;
    }


    public function __toString()
    {
        return $this->getCar() .' : '. $this->getQuantity().' x '.$this->getUnitPrice().'$ = '.$this->totalPrice.'$' ;
    }

    public function __toArray()
    {
        return [
            'id' => $this->getId(),
            'order' => $this->getOrder() ? $this->getOrder()->getId() : null,
            'car' => $this->getCar()->getId(),
            'quantity' => $this->getQuantity(),
            'unitPrice' => $this->getUnitPrice(),
            'lastCheckedStockPrice' => $this->getLastCheckedStockPrice(),
            'totalPrice' => $this->getTotalPrice(),
            'expired' => $this->isExpired(),
            'status' => $this->getStatus(),
            'expireAt' => $this->getExpireAt()->format("Y-m-d H:i:s"),
            'updatedAt' => $this->getUpdatedAt()->format("Y-m-d H:i:s"),
            'createdAt' => $this->getCreatedAt()->format("Y-m-d H:i:s"),
        ];
    }

}

