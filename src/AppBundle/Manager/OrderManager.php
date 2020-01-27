<?php

namespace AppBundle\Manager;

use AppBundle\Entity\Item;
use AppBundle\Entity\User;
use AppBundle\Model\Event\OrderEvents;
use AppBundle\Model\Event\OrderItemEvent;
use AppBundle\Repository\OrderRepository;
use http\Exception\BadMethodCallException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use AppBundle\Entity\Order;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;

class OrderManager
{
    /** @var OrderRepository $orderRepository */
    private $orderRepository;

    /** @var ItemManager $itemManager */
    private $itemManager;

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

    public function __construct(OrderRepository $orderRepository, ItemManager $itemManager, InventoryManager $inventoryManager, EventDispatcherInterface $eventDispatcher, int $cartExpiration)
    {
        $this->orderRepository = $orderRepository;
        $this->itemManager = $itemManager;
        $this->inventoryManager = $inventoryManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->cartExpiration = $cartExpiration;
    }

    /**
     * @param User|null $user
     * @param Session|null $session
     * @return Order|null
     */
    public function getCartFromUserOrSession(User $user = null, Session $session = null)
    {
        if ($user) {
            return $this->getUserCart($user);
        } elseif ($session) {
            return $this->getSessionCart($session);
        }

        return null;
    }

    /**
     * @param User $user
     * @return Order|null
     */
    public function getUserCart(User $user)
    {
        $cart = $this->orderRepository->getUserCart($user);

        return $cart;
    }

    /**
     * @param Session $session
     * @return Order|null
     */
    public function getSessionCart(Session $session)
    {
        $sessionId = $session->getId();
        $cart = $this->orderRepository->getSessionCart($sessionId);

        return $cart;
    }

    /**
     * @param User|null $user
     * @param Session|null $session
     * @return Order
     */
    public function createCart(User $user = null, Session $session = null)
    {
        if ($user) {
            return $this->createUserCart($user, $session);
        } else {
            return $this->createSessionCart($session);
        }
    }

    /**
     * @param User $user
     * @return Order
     */
    public function createUserCart(User $user, Session $session)
    {

        $cart = $this->getUserCart($user);
        if ($cart) {
            throw new PreconditionFailedHttpException('User Cart already exists');
        }

        $expireAt = new \DateTime("+ $this->cartExpiration minutes");
        $cart = new Order($expireAt);
        $cart->setSessionId($session->getId());
        $cart->setOwner($user);

        return $cart;
    }

    /**
     * @param Session $session
     * @return Order
     */
    public function createSessionCart(Session $session)
    {

        $sessionId = $session->getId();
        $cart = $this->getSessionCart($session);
        if ($cart) {
            throw new PreconditionFailedHttpException('Session Cart already exists');
        }

        $expireAt = new \DateTime("+ $this->cartExpiration minutes");
        $cart = new Order($expireAt);
        $cart->setSessionId($sessionId);

        return $cart;
    }

    public function addProduct(Order $order, string $carId, int $quantity = 1)
    {
        $carInventory = $this->inventoryManager->getStock($carId);
        $car = $carInventory->getCar();
        $stockUnitPrice = $carInventory->getUnitPrice();

        $orderItem = $this->getProductIdItem($order, $carId);

        if (!$orderItem) {
            $orderItem = $this->itemManager->createOrderItem($car, $stockUnitPrice, $quantity);
            $order->addItem($orderItem);
        } else {
            $this->itemManager->addOrderItemQuantity($orderItem, $quantity);
            /* Update unit price if meanwhile it was discounted */
            if ($stockUnitPrice < $orderItem->getUnitPrice()) {
                $orderItem->setUnitPrice($stockUnitPrice);
            }
            $order->refreshTotalPrice();
        }

        return $orderItem;
    }

    public function deleteOrderItem(Order $order, Item $item)
    {
        $removed = $order->removeItem($item);
        if (!$removed) {
            throw new BadMethodCallException("Product Not Found!");
        }

        $newStockQuantity = $this->itemManager->deleteOrderItem($item);
        $order->refreshTotalPrice();

        return $newStockQuantity;
    }

    public function updateOrderItemQuantity(Order $order, Item $item, int $newQuantity)
    {
        if ($newQuantity == 0) {
            $newStockQuantity = $this->deleteOrderItem($order, $item);
        } else {
            $newStockQuantity = $this->itemManager->updateOrderItemQuantity($item, $newQuantity);
        }

        return $newStockQuantity;
    }

    public function getProducts(Order $order = null)
    {
        $products = [];
        if ($order) {
            $orderItems = $order->getItems();
            foreach ($orderItems as $item) {
                $products[] = $item->getCar();
            }
        }

        return $products;
    }

    /**
     * @param string $carId
     * @return Item|null
     */
    public function getProductIdItem(Order $order, string $carId)
    {
        foreach ($order->getItems() as $orderItem) {
            if ($orderItem->getCar()->getId() == $carId) {
                return $orderItem;
            }
        }

        return null;
    }

    public function purchase(Order $cart)
    {
        $products = $cart->getItems();
        foreach ($products as $item) {
            $this->itemManager->purchase($item);
        }

        $cart->setStatus(Order::STATUS['paid']);
    }

    public function getSuccesfulOrders(User $user = null)
    {
        if (!$user) {
            return null;
        }

        return $this->orderRepository->getUserSuccessfulOrders($user);
    }

    public function getPendingExpiredCarts()
    {
        $now = new \DateTime();
        $openStatus = Order::STATUS['open'];

        return $this->orderRepository->getExpiredOrdersByStatus($now, $openStatus);
    }

    public function getAllCarts()
    {
        $openStatus = Order::STATUS['open'];

        return $this->orderRepository->getOrdersByStatus($openStatus);
    }

    public function expire(Order $cart)
    {
        if (!$cart or ($cart->getStatus() != Order::STATUS['open'])) {
            return flase;
        }

        $items = $cart->getItems();
        foreach ($items as $item) {
            $this->itemManager->expire($item);
        }

        $cart->setStatus(Order::STATUS['expired']);

        return true;
    }

    public function handleStaleCartProducts(Session $session, Order $cart = null)
    {
        if ($cart and $session) {
            $staleItems = $this->getStaleCartItems($cart);
            if (!empty($staleItems)) {
                $this->handleSessionStaleItems($session, $staleItems);
                $cart->refreshTotalPrice();
                return true;
            }
        }

        return false;
    }

    private function getStaleCartItems(Order $cart = null)
    {
        $staleItems = [];

        if ($cart) {
            $orderItems = $cart->getItems();
            foreach ($orderItems as $item) {
                $itemLastCheckedStockPrice = $item->getLastCheckedStockPrice();
                $currentProductPrice = $this->itemManager->getCurrentPrice($item);
                if ($itemLastCheckedStockPrice != $currentProductPrice) {
                    $staleItems[$item->getId()] = ['item' => $item, 'currentProductPrice' => $currentProductPrice];
                }
            }
        }

        return $staleItems;
    }

    private function handleSessionStaleItems(Session $session, array $staleItems)
    {
        foreach ($staleItems as $itemId => $staleItemStruct) {
            /** @var Item $orderItem */
            $orderItem = $staleItemStruct['item'];
            $itemProductPrice = $orderItem->getUnitPrice();
            $ippStr = number_format($itemProductPrice, 2);
            $itemLastCheckedStockPrice = $orderItem->getLastCheckedStockPrice();
            $currentProductPrice = $staleItemStruct['currentProductPrice'];
            $cppStr = number_format($currentProductPrice, 2);
            $productName = $orderItem->getCar()->getName();
            if ($currentProductPrice > $itemLastCheckedStockPrice) {
                $message['type'] = "danger";
                $message['text'] = "Hurry Up! Your cart product ->:$productName price was increased to \$$cppStr but you can still purchase it at \$$ippStr before your cart expires and its in your cart!";
            } elseif ($currentProductPrice < $itemLastCheckedStockPrice) {
                $orderItem->setUnitPrice($currentProductPrice);
                $message['type'] = "success";
                $message['text'] = "Great News! Your cart product ->:$productName price was discounted to \$$cppStr. We applied the discount automatically. You're all set!";
            } else {
                throw new \BadMethodCallException('Item not stale');
            }

            $session->getFlashBag()->add($message['type'], $message['text']);
            $orderItem->setLastCheckedStockPrice($currentProductPrice);
        }

    }


}