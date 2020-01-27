<?php

namespace AppBundle\Admin;

use AppBundle\Entity\Car;
use AppBundle\Entity\Inventory;
use AppBundle\Entity\Registration;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\Form\Type\DateTimeRangePickerType;
use Doctrine\ORM\Query\Expr;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class InventoryAdmin extends AbstractAdmin
{
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
    );

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('car')
            ->add('enabled')
            ->add('quantity')
            ->add('unitPrice')
            ->add('createdAt',
                'doctrine_orm_datetime_range',
                [
                    'field_type' => DateTimeRangePickerType::class,
                    'field_options' => [
                        'field_options' => [
                            'format' => 'yyyy/MM/dd H:mm:ss'
                        ]
                    ]
                ]
            )
            ->add('updatedAt',
                'doctrine_orm_datetime_range',
                [
                    'field_type' => DateTimeRangePickerType::class,
                    'field_options' => [
                        'field_options' => [
                            'format' => 'yyyy/MM/dd H:mm:ss'
                        ]
                    ]
                ]
            );
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id', null, ['route' => ['name' => 'show']])
            ->add('car', null, ['route' => ['name' => 'show']])
            ->add('enabled', null, ['editable' => true])
            ->add('quantity', null, ['editable' => true])
            ->add('unitPrice', null, ['editable' => true])
            ->add('createdAt', 'datetime', array('format' => 'Y/m/d H:i:s'))
            ->add('updatedAt', 'datetime', array('format' => 'Y/m/d H:i:s'))
            ->add('_action', null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ],
            ]);
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $em = $this->modelManager->getEntityManager(Car::class);
        $carQueryBuilder = $em->createQueryBuilder('c')
            ->select('c')
            ->from(Car::class, 'c')
            ->leftJoin(Inventory::class, 'i', Expr\Join::WITH, 'c.id = i.car')
            ->where('i.car is null')
            ->orWhere('i.id = :current')
            ->setParameter('current', $this->getSubject()->getId());

        $formMapper
            ->add('car', null,
                [
                    'query_builder' => $carQueryBuilder,
//                    'expanded' => true,
//                    'multiple' => true,
//                    'required' => false,
//                    'property' => 'path',
                ]
            )
            ->add('enabled')
            ->add('quantity')
            ->add('unitPrice')
        ;
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('car', null, ['route' => ['name' => 'show']])
            ->add('enabled')
            ->add('quantity')
            ->add('unitPrice')
            ->add('createdAt', 'datetime', array('format' => 'Y/m/d H:i:s'))
            ->add('updatedAt', 'datetime', array('format' => 'Y/m/d H:i:s'));
    }
}
