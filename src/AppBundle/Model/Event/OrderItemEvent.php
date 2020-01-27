<?php


namespace AppBundle\Model\Event;


use AppBundle\Entity\Inventory;
use AppBundle\Entity\Item;
use Symfony\Component\EventDispatcher\Event;

class OrderItemEvent extends Event
{
    /**
     * @var Item
     */
    private $orderItem;

    /**
     * @var Inventory
     */
    private $stock;

    public function __construct(Item $orderItem)
    {
        $this->orderItem = $orderItem;
    }

    /**
     * @return Item
     */
    public function getOrderItem(){

        return $this->orderItem;
    }

    /**
     * @return Inventory
     */
    public function getStock(): Inventory
    {
        return $this->stock;
    }

    /**
     * @param Inventory $stock
     */
    public function setStock(Inventory $stock)
    {
        $this->stock = $stock;
    }

}