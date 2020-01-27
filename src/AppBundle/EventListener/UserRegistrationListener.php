<?php


namespace AppBundle\EventListener;

use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Security;

class UserRegistrationListener implements EventSubscriberInterface
{
    /**
     * @var Security $security
     */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public static function getSubscribedEvents()
    {
        return [
            FOSUserEvents::REGISTRATION_SUCCESS => "onUserRegistrationSuccess",
        ];
    }

    public function onUserRegistrationSuccess(FormEvent $event)
    {
        $user = $event->getForm()->getData();
        if (!$this->security->isGranted('ROLE_BACKEND', $user) and !$this->security->isGranted('ROLE_CLIENT', $user)) {
            $user->addRole('ROLE_CLIENT');
        }
    }

}