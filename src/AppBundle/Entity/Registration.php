<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Registration
 *
 * @ORM\Table(name="registration")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\RegistrationRepository")
 */
class Registration
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="plate", type="string", length=6, unique=true)
     * @Assert\NotBlank()
     * @Assert\Regex(
     *     pattern="/AA\d{4}/",
     *     message="The plate shuld be in the AA1234 format"
     * )
     */
    private $plate;

    /**
     * @var Car|null
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Car", mappedBy="registration")
     */
    private $car;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set plate
     *
     * @param string $plate
     *
     * @return Registration
     */
    public function setPlate($plate)
    {
        $this->plate = $plate;

        return $this;
    }

    /**
     * Get plate
     *
     * @return string
     */
    public function getPlate()
    {
        return $this->plate;
    }

    /**
     * Set car
     * @param Car $car
     * @return Registration
     */
    public function setCar(Car $car = null)
    {
        if ($car) {
            $car->setRegistration($this);
            $this->car = $car;
        }


        return $this;
    }

    /**
     * Get car
     *
     * @return Car|null
     */
    public function getCar()
    {
        return $this->car;
    }

    public function __toString()
    {
        return $this->getPlate() ?? '';
    }
}

