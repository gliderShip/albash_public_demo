<?php

namespace AppBundle\Manager;

use AppBundle\Entity\Car;
use AppBundle\Entity\Inventory;
use AppBundle\Entity\Item;
use AppBundle\Entity\Order;
use AppBundle\Repository\InventoryRepository;
use Doctrine\Common\Collections\Collection;

class InventoryManager
{
    /** @var InventoryRepository $inventoryRepository */
    private $inventoryRepository;

    public function __construct(InventoryRepository $inventoryRepository)
    {
        $this->inventoryRepository = $inventoryRepository;
    }

    /**
     * @return Inventory[]
     */
    public function getListedStock()
    {
        return $this->inventoryRepository->getEnabled();
    }

    public function getProductsStock(array $cars)
    {
        return $this->inventoryRepository->getItemsStock($cars);
    }

    /**
     * @param string $carId
     * @return Inventory|null
     */
    public function getStock(string $carId)
    {
        $stock = $this->inventoryRepository->getStock($carId);

        return $stock;
    }

    /**
     * @param string $carId
     * @return Inventory|null
     */
    public function getAvailableStock(string $carId)
    {
        $stock = $this->inventoryRepository->getStock($carId);

        return $stock;
    }

    /**
     * @param Item $orderItem
     * @return Inventory|null
     */
    public function subtractItemQuantity(Inventory $inventory, Item $orderItem)
    {
        $this->removeQuantity($inventory, $orderItem->getQuantity());
    }

    /**
     * @param Item $orderItem
     * @return Inventory|null
     */
    public function addItemQuantity(Inventory $inventory, Item $orderItem)
    {
        $this->addQuantity($inventory, $orderItem->getQuantity());
    }

    /**
     * @param Item $orderItem
     * @return int $quantity
     */
    public function removeQuantity(Inventory $inventory, $quantity)
    {
        $inventory->subtractQuantity($quantity);
    }

    /**
     * @param Inventory $inventory
     * @param int $quantity
     */
    public function addQuantity(Inventory $inventory, int $quantity)
    {
        $inventory->addQuantity($quantity);
    }

    public function isProductPriceStale(Car $product, float $price)
    {
        $stock = $this->getStock($product->getId());
        if($stock->getUnitPrice() != $price){
            return true;
        }

        return false;
    }

}