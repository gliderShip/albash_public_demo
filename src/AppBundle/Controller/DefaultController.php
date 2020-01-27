<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Order;
use AppBundle\Manager\InventoryManager;
use AppBundle\Manager\ItemManager;
use AppBundle\Manager\OrderManager;
use AppBundle\Repository\CarRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class DefaultController extends Controller
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
     * @Route("/", name="product_list")
     */
    public function listAction(Request $request)
    {
        $user = $this->getUser();
        $session = $request->getSession();
        if (!$session->isStarted()) {
            $session->set('n', 'v');
        }

        $cart = $this->orderManager->getCartFromUserOrSession($user, $session);

        if($this->orderManager->handleStaleCartProducts($session, $cart)){
            $this->em->flush();
        };

        $inventory = $this->inventoryManager->getListedStock();

        return $this->render('ecommerce/homepage.html.twig',
            [
                'cart' => $cart,
                'inventory' => $inventory,
            ]
        );

    }

    /**
     * @Route("/checkout/", name="checkout")
     */
    public function checkoutAction(Request $request)
    {
        $user = $this->getUser();
        $session = $request->getSession();
        if (!$session->isStarted()) {
            $session->start();
        }

        $cart = $this->orderManager->getCartFromUserOrSession($user, $session);

        if($this->orderManager->handleStaleCartProducts($session, $cart)){
            $this->em->flush();
        };

        $cartProductsInventory = null;

        $products = $this->orderManager->getProducts($cart);
        $cartProductsInventory = $this->inventoryManager->getProductsStock($products);
        $previousOrders = $this->orderManager->getSuccesfulOrders($user);

        if (!$user and ($cart == null or $cart->isEmpty())) {
            $this->addFlash("danger", "No Items in cart");
            return $this->redirectToRoute("product_list");
        }

        return $this->render('ecommerce/checkout.html.twig',
            [
                'cart' => $cart,
                'inventory' => $cartProductsInventory,
                'orders' => $previousOrders,
            ]
        );
    }

    /**
     * @Route("/pay/", name="pay_cart")
     * @Security("is_granted('ROLE_USER')")
     */
    public function payAction(Request $request)
    {
        $user = $this->getUser();

        if (!$this->isGranted('ROLE_CLIENT', $user)) {
            $this->addFlash("danger", "Not Authorized! Please login with *client* credentials!");
            return $this->redirectToRoute("fos_user_security_login");
        }
        if (!$this->isGranted('ROLE_CLIENT', $user)) {
            $this->addFlash("danger", "Not Authorized!");
            return $this->redirectToRoute("fos_user_security_login");
        }

        $cart = $this->orderManager->getUserCart($user);
        if (!$cart) {
            $this->addFlash("danger", "No Items in cart");
            return $this->redirectToRoute("product_list");
        } else{
            if($this->orderManager->handleStaleCartProducts($request->getSession(), $cart)){
                $this->addFlash("success", "Please note the following updates before checking out.");
                return $this->redirectToRoute("checkout");
                $this->em->flush();
            };
        }

        $products = $this->orderManager->getProducts($cart);

        if (empty($products)) {
            $this->addFlash("danger", "Please add some products first!");
            return $this->redirectToRoute("product_list");
        } else {
            $this->orderManager->purchase($cart);
        }

        $this->em->flush();

        $this->addFlash("success", "Great choice! Thank you for your order!");
        return $this->redirectToRoute("checkout");
    }


}
