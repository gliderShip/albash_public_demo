<?php

namespace AppBundle\Admin;

use AppBundle\Entity\Order;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\Form\Type\DateTimeRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ItemAdmin extends AbstractAdmin
{
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
    );

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('create')
            ->remove('delete');

    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('order')
            ->add('quantity')
            ->add('unitPrice')
            ->add('totalPrice')
            ->add('status')
            ->add('expireAt',
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
            )
            ->add('lastCheckedStockPrice', null, ['label' => 'Updated Stock Unit Price'])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add('order', null, ['route' => ['name' => 'show']])
            ->add('quantity')
            ->add('unitPrice')
            ->add('totalPrice')
            ->add('expired', 'boolean')
            ->add('status')
            ->add('expireAt', 'datetime', array('format' => 'Y/m/d H:i:s'))
            ->add('createdAt', 'datetime', array('format' => 'Y/m/d H:i:s'))
            ->add('updatedAt', 'datetime', array('format' => 'Y/m/d H:i:s'))
            ->add('lastCheckedStockPrice', null, ['label' => 'Updated Stock Unit Price'])
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
        $formMapper
            ->add('status', ChoiceType::class,
                ['choices' => Order::STATUS]
            );
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('order', null, ['route' => ['name' => 'show']])
            ->add('quantity')
            ->add('unitPrice')
            ->add('totalPrice')
            ->add('expired')
            ->add('status')
            ->add('expireAt', 'datetime', array('format' => 'Y/m/d H:i:s'))
            ->add('createdAt', 'datetime', array('format' => 'Y/m/d H:i:s'))
            ->add('updatedAt', 'datetime', array('format' => 'Y/m/d H:i:s'))
            ->add('lastCheckedStockPrice', null, ['label' => 'Updated Stock Unit Price'])
        ;
    }
}
