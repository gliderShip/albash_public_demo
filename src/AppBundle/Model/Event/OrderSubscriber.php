<?php

namespace AppBundle\Model\Event;

use AppBundle\Manager\InventoryManager;
use Doctrine\ORM\EntityManagerInterface;
use http\Exception\BadMethodCallException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderSubscriber implements EventSubscriberInterface
{
    /** @var EntityManagerInterface $em */
    private $em;

    /** @var InventoryManager $inventoryManager */
    private $inventoryManager;

    public function __construct(EntityManagerInterface $em, InventoryManager $inventoryManager)
    {
        $this->em = $em;
        $this->inventoryManager = $inventoryManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            OrderEvents::ORDER_ITEM_DELETED_EVENT => [['onOrderItemDeleted', 0]],
            OrderEvents::CART_EXPIRED_EVENT => [['onCartExpiration', 0]],
//            OrderPlacedEvent::NAME => 'onStoreOrder',
        ];
    }

    public function onOrderItemDeleted(OrderItemEvent $event)
    {
        /** TODO  */
    }

    public function onCartExpiration()
    {
        /** TODO  */
    }
}