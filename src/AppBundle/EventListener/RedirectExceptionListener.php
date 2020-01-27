<?php


namespace AppBundle\EventListener;

use AppBundle\Exception\RedirectException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RedirectExceptionListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        // return the subscribed events, their methods and priorities
        return [
            KernelEvents::EXCEPTION => [
                ['processRedirectException', 10],
            ],
        ];
    }


    public function processRedirectException(GetResponseForExceptionEvent $event)
    {
        if ($event->getException() instanceof RedirectException) {
            $event->setResponse($event->getException()->getRedirectResponse());
        }
    }

}