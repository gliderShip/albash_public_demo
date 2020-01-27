<?php


namespace AppBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Entity\Order;

/**
 * Trait Expirable
 */
trait Expirable
{
    /**
     * @var \DateTime
     * @ORM\Column(name="expire_at", type="datetime")
     * @Assert\DateTime()
     */
    private $expireAt;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="string", length=32)
     * @Assert\Choice(choices=Order::STATUS, message="Choose a valid status.")
     */
    private $status;

    /**
     * @return bool
     */
    public function isExpired(): bool
    {
        $now = new \DateTime('now');

        return ( ($this->expireAt <= $now) or ($this->status == Order::STATUS['expired']));
    }

    public function setExpired(bool $expired)
    {
        if ($expired) {
//            $this->expireAt = new \DateTime('now');
            $this->status = Order::STATUS['expired'];
        }
    }

    /**
     * @return \DateTime
     */
    public function getExpireAt()
    {
        return $this->expireAt;
    }

    /**
     * @param \DateTime $expireAt
     */
    public function setExpireAt(\DateTime $expireAt = null)
    {
        $this->expireAt = $expireAt;
    }


    /**
     * Set status
     *
     * @param string $status
     */
    public function setStatus($status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }
}