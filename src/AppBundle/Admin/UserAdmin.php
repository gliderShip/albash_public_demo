<?php
/**
 * Created by PhpStorm.
 * User: glidership
 * Date: 04/01/16
 * Time: 20.02
 */

namespace AppBundle\Admin;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Admin\AbstractAdmin as Admin;
use FOS\UserBundle\Model\UserManagerInterface;
use AppBundle\Entity\User;

class UserAdmin extends Admin
{
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
    );

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('General')
                ->add('username')
                ->add('email')
                ->add('plainPassword', 'text')
            ->end()

            ->with('Management')
                ->add('roles')
               // ->add('roles', 'sonata_security_roles', array('multiple' => true))
                ->add('enabled', null, array('required' => false))
            ->end();
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('username')
            ->add('email')
            ->add('lastLogin')
            ->add('enabled')
            ->add('password')
            ->add('roles')
            ->add('orders')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->addIdentifier('username', null, array('route' => array('name' => 'show')))
            ->add('email')
            ->add('enabled')
            ->add('roles')
            ->add('orders')
            ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array(),
                    'edit' => array(),
                    'delete' => array(),
                )
            ))
        ;
    }

    public function preUpdate($user)
    {
        $this->getUserManager()->updateCanonicalFields($user);
        $this->getUserManager()->updatePassword($user);
    }

    public function setUserManager(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * @return UserManagerInterface
     */
    public function getUserManager()
    {
        return $this->userManager;
    }
}
