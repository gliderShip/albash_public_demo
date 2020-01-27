<?php


namespace AppBundle\Twig;

use AppBundle\Entity\Car;
use AppBundle\Entity\Inventory;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class InventoryExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('getStock', [$this, 'getStock']),
        ];
    }

    /**
     * @param Car $car
     * @param Inventory ...$inventory
     * @return Inventory|null
     */
    public function getStock($car, array $inventory)
    {
        foreach ($inventory as $stock){
            if($stock->getCar()->getId() == $car->getId()){
                return $stock;
            }
        }

        return null;
    }
}