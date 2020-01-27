<?php

namespace AppBundle\Controller;

use AppBundle\Exception\InsufficientStockException;
use AppBundle\Manager\InventoryManager;
use AppBundle\Manager\ItemManager;
use AppBundle\Manager\OrderManager;
use AppBundle\Model\Event\OrderEvents;
use AppBundle\Repository\CarRepository;
use Doctrine\ORM\EntityManagerInterface;
use Elastica\Query\MultiMatch;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class ApiController extends Controller
{
    /** @var EntityManagerInterface $em */
    private $em;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /** @var CarRepository $carRepository */
    private $carRepository;

    /** @var InventoryManager $inventoryManager */
    private $inventoryManager;

    /** @var ItemManager $itemManager */
    private $itemManager;

    /** @var OrderManager $orderManager */
    private $orderManager;

    public function __construct(EntityManagerInterface $em, EventDispatcherInterface $eventDispatcher, InventoryManager $inventoryManager, CarRepository $carRepository, ItemManager $itemManager, OrderManager $orderManager)
    {
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
        $this->inventoryManager = $inventoryManager;
        $this->carRepository = $carRepository;
        $this->itemManager = $itemManager;
        $this->orderManager = $orderManager;
    }

    /**
     * @Route("/cart/product/{carId}/create/", methods={"POST", "GET"}, name="add_cart_item", requirements={"carId"="\d+"})
     */
    public function addItemAction(Request $request, string $carId)
    {
        $user = $this->getUser();
        $session = $request->getSession();
        if (!$session->isStarted()) {
            $session->start();
        }

        $quantity = $request->query->get('quantity', 1);

        $carInventory = $this->inventoryManager->getAvailableStock($carId);
        if (!$carInventory) {
            return new JsonResponse("Car:$carId Not Available in our inventory", Response::HTTP_NOT_FOUND);
        }

        $cart = $this->orderManager->getCartFromUserOrSession($user, $session);
        if (!$cart) {
            $cart = $this->orderManager->createCart($user, $session);
            $this->em->persist($cart);
        } else {
            $this->orderManager->handleStaleCartProducts($session, $cart);
        }

        try {
            $this->orderManager->addProduct($cart, $carId, $quantity);
        } catch (InsufficientStockException $ex) {
            return new JsonResponse("You are being too greedy. We have  {$carInventory->getQuantity()} of that in stock!", Response::HTTP_PRECONDITION_FAILED);
        }

        $this->em->flush();

        return new JsonResponse([
            'cart' => $cart->__toArray(),
            'stock' => $carInventory->__toArray(),
            'popup' => $session->getFlashBag()->all(),
        ],
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/cart/product/{carId}/delete/", methods={"DELETE", "GET"}, name="remove_cart_item", requirements={"carId"="\d+"})
     */
    public function deleteItemAction(Request $request, string $carId)
    {
        $session = $request->getSession();
        $user = $this->getUser();
        if (!$user) {
            if (!$session->isStarted()) {
                return new JsonResponse(null, Response::HTTP_UNAUTHORIZED);
            }
        }

        $cart = $this->orderManager->getCartFromUserOrSession($user, $session);
        if (!$cart) {
            return new JsonResponse("Now cart found!", Response::HTTP_PRECONDITION_FAILED);
        } else {
            $this->orderManager->handleStaleCartProducts($session, $cart);
        }

        $orderItem = $this->orderManager->getProductIdItem($cart, $carId);
        if (!$orderItem) {
            return new JsonResponse("This product was not found in your cart!", Response::HTTP_NOT_FOUND);
        }

        $this->orderManager->deleteOrderItem($cart, $orderItem);
        $this->em->flush();

        $carInventory = $this->inventoryManager->getAvailableStock($carId);

        return new JsonResponse([
            'cart' => $cart->__toArray(),
            'stock' => $carInventory->__toArray(),
            'popup' => $session->getFlashBag()->all(),

        ], Response::HTTP_OK);

    }

    /**
     * @Route("/cart/product/{carId}/update/", methods={"PATCH", "GET"}, name="update_cart_item", requirements={"carId"="\d+"})
     */
    public function updateItemQuantityAction(Request $request, string $carId)
    {
        $user = $this->getUser();
        $session = $request->getSession();

        if (!$user) {
            if (!$session->isStarted()) {
                return new JsonResponse(null, Response::HTTP_UNAUTHORIZED);
            }
        }

        $quantity = $request->query->get('quantity');
        if ($quantity == 0) {
            return $this->forward("AppBundle:Api:deleteItem", [
                "request" => $request,
                "session" => $session,
                "carId" => $carId,
            ]);
        }

        $cart = $this->orderManager->getCartFromUserOrSession($user, $session);
        if (!$cart) {
            return new JsonResponse("Now cart found!", Response::HTTP_PRECONDITION_FAILED);
        } else {
            $this->orderManager->handleStaleCartProducts($session, $cart);
        }

        $orderItem = $this->orderManager->getProductIdItem($cart, $carId);
        if (!$orderItem) {
            return new JsonResponse("This product was not found in your cart!", Response::HTTP_NOT_FOUND);
        }

        $stock = $this->inventoryManager->getAvailableStock($carId);
        if (!$stock) {
            return new JsonResponse("This product is not available any more!", Response::HTTP_NOT_FOUND);
        }

        try {
            $this->orderManager->updateOrderItemQuantity($cart, $orderItem, $quantity);
        } catch (InsufficientStockException $ex) {
            return new JsonResponse($ex->getMessage(), Response::HTTP_PRECONDITION_FAILED);
        }

        $this->em->flush();

        return new JsonResponse([
            'cart' => $cart->__toArray(),
            'stock' => $stock->__toArray(),
            'orderItem' => $orderItem->__toArray(),
            'popup' => $session->getFlashBag()->all(),

        ], Response::HTTP_OK);

    }

    /**
     * @Route("/search/", methods={"PUT", "GET"}, name="search_inventory")
     */
    public function searchAction(Request $request)
    {
        $query = $request->query->get('q');
        $inventory = $this->container->get('fos_elastica.index.inventory.stock');

        $multiMatch = new MultiMatch();
        $multiMatch->setQuery($query);
        $multiMatch->setFuzziness(MultiMatch::FUZZINESS_AUTO);

        $resultSet = $inventory->search($multiMatch);
        $results = $resultSet->getResults();
        $cars = [];

        foreach ($results as $result) {
            $cars[] = $result->getHit();
        }

        return new JsonResponse($cars, Response::HTTP_OK);
    }


}
