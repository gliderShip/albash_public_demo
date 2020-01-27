<?php


namespace AppBundle\EventListener;


use AppBundle\Entity\User;
use AppBundle\Exception\RedirectException;
use AppBundle\Manager\OrderManager;
use Doctrine\ORM\EntityManagerInterface;
use http\Exception\UnexpectedValueException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginListener implements EventSubscriberInterface
{
    /**
     * @var Security $security
     */
    private $security;

    /**
     * @var OrderManager $orderManager
     */
    private $orderManager;

    /**
     * @var EntityManagerInterface $em
     */
    private $em;

    /**
     * @var RouterInterface $router
     */
    private $router;

    public function __construct(Security $security, EntityManagerInterface $em, OrderManager $orderManager, RouterInterface $router)
    {
        $this->security = $security;
        $this->em = $em;
        $this->orderManager = $orderManager;
        $this->router = $router;
    }

    public static function getSubscribedEvents()
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => "onInteractiveLoginSuccess",
        ];
    }

    public function onInteractiveLoginSuccess(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();
//        if(!$this->security->isGranted('ROLE_CLIENT', $user)){
//            return;
//        }

        $request = $event->getRequest();
        $session = $request->getSession();
        $user = $event->getAuthenticationToken()->getUser();
        $userCart = $this->orderManager->getUserCart($user);
        $sessionCart = $this->orderManager->getSessionCart($session);

        if($userCart and !$userCart->isEmpty()){
            if($sessionCart and ($sessionCart->getId() != $userCart->getId()) ) {
                $sessionCart->setOwner($user);
                $this->orderManager->expire($sessionCart);
                $session->getFlashBag()->add('danger', 'You have a pending order. Please checkout or remove the following items if no longer interested!');
                $this->em->flush();
                $event->stopPropagation();
                $response =  new RedirectResponse($this->router->generate('checkout'));
                throw new RedirectException($response);
            }
        } else{
            if($userCart){
                $userCart->setSessionId($session->getId());
                $this->orderManager->expire($userCart);
            }
            if ($sessionCart) {
                if (is_null($sessionCart->getOwner())) {
                    $sessionCart->setOwner($user);
//                    $session->setId($sessionCart->getSessionId());
                } else{
                    throw new \UnexpectedValueException("Session Hijacked");
                }
            }
        }

        $this->em->flush();
    }

}