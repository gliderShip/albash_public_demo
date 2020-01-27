<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Command;

use AppBundle\Manager\OrderManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateExpiredCarts extends ContainerAwareCommand
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var OrderManager
     */
    private $orderManager;

    public function __construct($name = null, OrderManager $orderManager, EntityManagerInterface $em)
    {
        parent::__construct($name);

        $this->orderManager = $orderManager;
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:update-expired-carts')
            ->setDescription('Expire carts not purchased during the validity timeout. Return cart items back to the inventory.')
            ->addOption('all', 'a', InputOption::VALUE_NONE, "Prematurely expire all carts.");
    }

    /**
     * This method is executed after interact() and initialize().
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $allOrders = $input->getOption('all');

        if ($allOrders) {
            $expiredPendingOrders = $this->orderManager->getAllCarts();
        } else {
            $expiredPendingOrders = $this->orderManager->getPendingExpiredCarts();
        }

        $progressBar = new ProgressBar($output, count($expiredPendingOrders));
        $progressBar->start();

        foreach ($expiredPendingOrders as $order) {
            $this->orderManager->expire($order);
            $progressBar->advance();
        }

        $progressBar->finish();

        $this->em->flush();
    }


}
