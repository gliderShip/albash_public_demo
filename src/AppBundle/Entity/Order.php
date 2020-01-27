<?php

namespace AppBundle\Entity;

use AppBundle\Model\Expirable;
use AppBundle\Model\Timestampable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Order
 *
 * @ORM\Table(name="client_order", indexes={
 *     @ORM\Index(name="expireAt_idx", columns={"expire_at"}),
 *     @ORM\Index(name="status_idx", columns={"status"})
 * }
 *     )
 * @ORM\Entity(repositoryClass="AppBundle\Repository\OrderRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Order
{
    use Timestampable, Expirable;

    const STATUS = [
        'open' => 'open',
        'expired' => 'expired',
        'paid' => 'paid',
    ];

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="orders")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", nullable=true)
     */
    private $owner;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="sessionId", type="string", length=256, nullable=false)
     */
    private $sessionId;

    /**
     * @var Item[] | Collection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Item", mappedBy="order", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $items;

    /**
     * @var float
     *
     * @ORM\Column(name="totalPrice", type="decimal", precision=10, scale=2)
     * @Assert\GreaterThan(value="0")
     */
    private $totalPrice;


    public function __construct(\DateTime $expireAt)
    {
        $this->items = new ArrayCollection();
        $this->status = self::STATUS['open'];
        $now = new \DateTime();
        $this->createdAt = $now;
        $this->updatedAt = $now;
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
     * @return Item[]|Collection
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param Item[]|Collection $items
     */
    public function setItems(Item ...$items)
    {
        foreach ($items as $item) {
            $this->addItem($item);
        }
    }

    /**
     * @param Item $item
     * @return bool
     */
    public function addItem(Item $item)
    {
        if (!$this->items->contains($item)) {
            $item->setOrder($this);
            $this->items[] = $item;
            $this->addPrice($item->getTotalPrice());
            return true;
        }

        return false;
    }

    /**
     * @param Item $item
     * @return bool
     */
    public function removeItem(Item $item)
    {
        if ($this->items->contains($item)) {
            $this->items->removeElement($item);
            $this->subtractPrice($item->getTotalPrice());
            return true;
        }

        return false;
    }

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param User $owner
     */
    public function setOwner(User $owner = null)
    {
        $this->owner = $owner;
    }

    /**
     * @return string
     */
    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    /**
     * @param string $sessionId
     */
    public function setSessionId(string $sessionId = null)
    {
        $this->sessionId = $sessionId;
    }

    /**
     * Add Price
     * @param float $price
     */
    private function addPrice(float $price)
    {
        return $this->totalPrice += $price;
    }

    /**
     * Subtract Price
     * @param float $price
     */
    private function subtractPrice(float $price)
    {
        return $this->totalPrice -= $price;
    }

    /**
     * Set Price
     * @param float $totalPrice
     */
    private function setTotalPrice(float $totalPrice)
    {
        return $this->totalPrice = $totalPrice;
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

    public function containsProduct($productId)
    {
        foreach ($this->items as $item) {
            $quantity = $item->hasProductId($productId);
            if ($quantity) {
                return $quantity;
            }
        }

        return false;
    }

    public function isEmpty()
    {
        return empty($this->items);
    }

    /**
     * @return int
     */
    public function getTotalItems()
    {
        $quantity = 0;
        foreach ($this->items as $item) {
            $quantity += $item->getQuantity();
        }

        return $quantity;
    }

    /**
     * @return int
     */
    public function getUniqueItems()
    {
        return count($this->items);
    }

    public function getItemsArray()
    {
        $itemsArray = [];
        foreach ($this->getItems() as $item) {
            $itemsArray[$item->getId()] = $item->__toArray();
        }

        return $itemsArray;
    }

    public function __toArray()
    {
        return [
            'id' => $this->getId(),
            'owner' => $this->getOwner(),
            'sessionId' => $this->getSessionId(),
            'totalAmount' => $this->getTotalPrice(),
            'expired' => $this->isExpired(),
            'status' => $this->getStatus(),
            'expireAt' => $this->getExpireAt()->format("Y-m-d H:i:s"),
            'totalItems' => $this->getTotalItems(),
            'uniqueItems' => $this->getUniqueItems(),
            'updatedAt' => $this->getUpdatedAt()->format("Y-m-d H:i:s"),
            'createdAt' => $this->getCreatedAt()->format("Y-m-d H:i:s"),
            'items' => $this->getItemsArray(),
        ];
    }

    /**
     * @return float
     * @ORM\PreUpdate()
     */
    public function refreshTotalPrice()
    {
        $newPrice = 0;
        foreach ($this->items as $item) {
            $newPrice += $item->getTotalPrice();
        }

        if($this->totalPrice != $newPrice){
            $this->totalPrice = $newPrice;
            return true;
        }

        return false;
    }

    public function __toString()
    {
        return (string) $this->getId();
    }

}

