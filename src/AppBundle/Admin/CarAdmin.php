<?php

namespace AppBundle\Admin;

use AppBundle\Entity\Car;
use AppBundle\Entity\Registration;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class CarAdmin extends AbstractAdmin
{
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
    );

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('name')
            ->add('model')
            ->add('registration')
            ->add('engineSize')
            ->add('tags');
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id', null, ['route' => ['name' => 'show']])
            ->add('name')
            ->add('model', null, ['route' => ['name' => 'show']])
            ->add('registration', null, ['route' => ['name' => 'show']])
            ->add('engineSize', null, ['route' => ['name' => 'show']])
            ->add('tags', null, ['route' => ['name' => 'show']])
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
        $em = $this->modelManager->getEntityManager(Registration::class);
        $registartionQuery = $em->createQueryBuilder('r')
            ->select('r')
            ->from(Registration::class, 'r')
            ->leftJoin('r.car', 'c')
            ->where('c is null')
            ->orWhere('c.id = :current')
            ->setParameter('current', $this->getSubject()->getId());


        $formMapper
            ->add('name')
            ->add('model')
            ->add('registration', null,
                [
                    'query_builder' => $registartionQuery,
//                    'expanded' => true,
//                    'multiple' => true,
//                    'required' => false,
//                    'property' => 'path',
                ]
            )
            ->add('engineSize')
            ->add('tags');
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id', null, ['route' => ['name' => 'show']])
            ->add('name')
            ->add('model', null, ['route' => ['name' => 'show']])
            ->add('registration', null, ['route' => ['name' => 'show']])
            ->add('engineSize', null, ['route' => ['name' => 'show']])
            ->add('tags', null, ['route' => ['name' => 'show']]);
    }
}
