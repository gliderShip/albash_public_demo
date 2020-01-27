<?php


namespace AppBundle\EventListener;

use AppBundle\Entity\Car;
use AppBundle\Entity\EngineSize;
use AppBundle\Entity\Inventory;
use AppBundle\Entity\Make;
use AppBundle\Entity\Model;
use AppBundle\Entity\Registration;
use AppBundle\Entity\Tag;
use AppBundle\Manager\InventoryManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use FOS\ElasticaBundle\Persister\ObjectPersister;
use FOS\ElasticaBundle\Provider\IndexableInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use FOS\ElasticaBundle\Doctrine\Listener;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class SearchIndexer extends Listener implements EventSubscriber
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var InventoryManager
     */
    private $inventoryManager;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param ObjectPersisterInterface $objectPersister
     * @param IndexableInterface $indexable
     * @param array $config
     * @param LoggerInterface $logger
     */
    public function __construct(ObjectPersisterInterface $objectPersister, IndexableInterface $indexable, array $config = [], LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        parent::__construct($objectPersister, $indexable, $config, $logger);
    }


    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
            Events::preRemove,
            Events::postUpdate,
        ];
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        $stocks = $this->getRelatedStocksFromEntity($entity);
        $this->updateSearchIndex($stocks);
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        $stocks = $this->getRelatedStocksFromEntity($entity);
        $this->updateSearchIndex($stocks);
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        $stocks = $this->getRelatedStocksFromEntity($entity);
        $this->updateSearchIndex($stocks);
    }

    private function getRelatedStocksFromEntity($entity)
    {
        $this->inventoryManager = $this->container->get('AppBundle\Manager\InventoryManager');

        $inventory = [];

        if ($entity instanceof Car) {
            /** @var Car $entity */
            if (!is_null($entity->getId())) {
                $inventory[] = $this->inventoryManager->getStock($entity->getId());
            }
        } elseif ($entity instanceof EngineSize) {
            /** @var EngineSize $entity */
            $cars = $entity->getCars();
            foreach ($cars as $car) {
                if (!is_null($car->getId())) {
                    $stock = $this->inventoryManager->getStock($car->getId());
                    if($stock && !is_null($stock->getId())) {
                        $inventory[] = $stock;
                    }
                }
            }
        } elseif ($entity instanceof Inventory) {
            if (!is_null($entity->getId())) {
                $inventory[] = $entity;
            }
        } elseif ($entity instanceof Make) {
            /** @var Make $entity */
            $models = $entity->getModels();
            foreach ($models as $model) {
                $cars = $model->getCars();
                foreach ($cars as $car) {
                    if (!is_null($car->getId())) {
                        $stock = $this->inventoryManager->getStock($car->getId());
                        if($stock && !is_null($stock->getId())) {
                            $inventory[] = $stock;
                        }
                    }
                }
            }
        } elseif ($entity instanceof Model) {
            /** @var Model $entity */
            $cars = $entity->getCars();
            foreach ($cars as $car) {
                if (!is_null($car->getId())) {
                    $stock = $this->inventoryManager->getStock($car->getId());
                    if($stock && !is_null($stock->getId())) {
                        $inventory[] = $stock;
                    }
                }
            }
        } elseif ($entity instanceof Tag) {
            /** @var Tag $entity */
            $cars = $entity->getCars();
            foreach ($cars as $car) {
                if (!is_null($car->getId())) {
                    $stock = $this->inventoryManager->getStock($car->getId());
                    if($stock && !is_null($stock->getId())) {
                        $inventory[] = $stock;
                    }
                }
            }
        } elseif ($entity instanceof Registration) {
            /** @var Registration $entity */
            $car = $entity->getCar();
            if ($car && !is_null($car->getId())) {
                $stock = $this->inventoryManager->getStock($car->getId());
                if($stock && !is_null($stock->getId())) {
                    $inventory[] = $stock;
                }
            }
        }

        return $inventory;

    }

    private function updateSearchIndex($stocks)
    {
        if (!empty($stocks)) {
//            $this->objectPersister->replaceMany($stocks);
        }

    }

}