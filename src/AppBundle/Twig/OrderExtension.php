<?php


namespace AppBundle\Twig;

use AppBundle\Entity\Order;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class OrderExtension extends AbstractExtension
{
    protected $cartExpiration;

    public function __construct(int $cartExpiration)
    {
        $this->cartExpiration = $cartExpiration;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('getTimeToLive', [$this, 'getTimeToLive']),
        ];
    }

    /**
     * @param Car $car
     * @param Inventory ...$inventory
     * @return Inventory|null
     */
    public function getTimeToLive(Order $cart = null)
    {
        if ($cart) {
            $ttl = $cart->getExpireAt()->getTimestamp() - time();
            if ($ttl > 0) {
                return $ttl;
            } else {
                return 0;
            }
        }

        return $this->cartExpiration * 60;
    }
}