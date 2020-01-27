<?php

namespace AppBundle\Manager;

use AppBundle\Entity\Car;
use AppBundle\Entity\Order;
use AppBundle\Exception\InsufficientStockException;
use AppBundle\Model\Event\OrderEvents;
use AppBundle\Model\Event\OrderItemEvent;
use AppBundle\Repository\ItemRepository;
use AppBundle\Entity\Item;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ItemManager
{
    /** @var ItemRepository $ItemRepository */
    private $itemRepository;

    /** @var InventoryManager $inventoryManager */
    private $inventoryManager;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var int $cartExpiration
     */
    private $cartExpiration;

    public function __construct(ItemRepository $itemRepository, InventoryManager $inventoryManager, EventDispatcherInterface $eventDispatcher, int $cartExpiration)
    {
        $this->itemRepository = $itemRepository;
        $this->inventoryManager = $inventoryManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->cartExpiration = $cartExpiration;
    }

    /**
     * @param Car $car
     * @param float $unitPrice
     * @param int $quantity
     * @return Item
     */
    public function createOrderItem(Car $car, float $unitPrice, $quantity = 1)
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException("Order item quantity must be positive!");
        }

        $expireAt = new \DateTime("+ $this->cartExpiration minutes");
        $orderItem = new Item($car, $unitPrice, 0, $expireAt);
        $this->updateOrderItemQuantity($orderItem, $quantity);

        return $orderItem;
    }

    public function deleteOrderItem(Item $item)
    {
        $itemQuantity = $item->getQuantity();
        $productId = $item->getCar()->getId();
        $itemInventory = $this->inventoryManager->getStock($productId);

        $item->setOrder(null);
        $itemInventory->addQuantity($itemQuantity);

        $orderItemEvent = new OrderItemEvent($item);
        $this->eventDispatcher->dispatch(OrderEvents::ORDER_ITEM_DELETED_EVENT, $orderItemEvent);

        return $itemInventory->getQuantity();
    }

    public function updateOrderItemQuantity(Item $item, int $newQuantity)
    {
        if ($newQuantity <= 0) {
            throw new \InvalidArgumentException("Quantity must be positive!");
        }

        $inventoryQuantity = 0;
        $previousQuantity = $item->getQuantity();
        if ($newQuantity > $previousQuantity) {
            $deltaQuantity = $newQuantity - $previousQuantity;
            $inventoryQuantity = $this->addOrderItemQuantity($item, $deltaQuantity);
        } elseif ($newQuantity < $previousQuantity) {
            $deltaQuantity = $previousQuantity - $newQuantity;
            $inventoryQuantity = $this->subtractOrderItemQuantity($item, $deltaQuantity);
        }

        return $inventoryQuantity;
    }

    public function addOrderItemQuantity(Item $item, int $deltaQuantity)
    {
        if ($deltaQuantity <= 0) {
            throw new \InvalidArgumentException("Order item quantity to add must be positive!");
        }

        $productId = $item->getCar()->getId();
        $itemInventory = $this->inventoryManager->getAvailableStock($productId);
        $inventoryQuantity = $itemInventory->getQuantity();
        if ($inventoryQuantity < $deltaQuantity) {
            throw new InsufficientStockException("Only $inventoryQuantity products left in stock");
        }

        $item->addQuantity($deltaQuantity);
        $itemInventory->subtractQuantity($deltaQuantity);

        $orderItemEvent = new OrderItemEvent($item);
        $this->eventDispatcher->dispatch(OrderEvents::ORDER_ITEM_UPDATED_EVENT, $orderItemEvent);

        return $itemInventory->getQuantity();
    }

    public function subtractOrderItemQuantity(Item $item, int $deltaQuantity)
    {
        if ($deltaQuantity <= 0) {
            throw new \InvalidArgumentException("Order item quantity to subtract must be positive!");
        }

        $productId = $item->getCar()->getId();
        $itemInventory = $this->inventoryManager->getAvailableStock($productId);
        $inventoryQuantity = $itemInventory->getQuantity();

        $item->subtractQuantity($deltaQuantity);
        $itemInventory->addQuantity($deltaQuantity);

        $orderItemEvent = new OrderItemEvent($item);
        $this->eventDispatcher->dispatch(OrderEvents::ORDER_ITEM_UPDATED_EVENT, $orderItemEvent);

        return $itemInventory->getQuantity();
    }

    public function purchase(Item $item)
    {
        $item->setStatus(Order::STATUS['paid']);
    }

    public function expire(Item $item)
    {
        if ($item and ($item->getStatus() == Order::STATUS['open'])) {

            $quantity = $item->getQuantity();
            $productId = $item->getCar()->getId();
            $stock = $this->inventoryManager->getStock($productId);
            if ($stock) {
                $this->inventoryManager->addQuantity($stock, $quantity);
            }

            $item->setStatus(Order::STATUS['expired']);
            return true;
        }

        return false;
    }

    public function isStale(Item $item)
    {
        $price = $item->getUnitPrice();
        $product = $item->getCar();

        return $this->inventoryManager->isProductPriceStale($product, $price);
    }

    public function getUnitPrice(Item $item)
    {
        return $item->getUnitPrice();
    }

    public function getCurrentPrice(Item $item)
    {
        $currentPrice = $this->getUnitPrice($item);
        $product = $item->getCar();
        $stock = $this->inventoryManager->getStock($product->getId());
        if ($stock) {
            $currentPrice = $stock->getUnitPrice();
        }

        return $currentPrice;
    }


}