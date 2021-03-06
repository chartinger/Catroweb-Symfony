<?php

namespace Catrobat\AppBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;


class GoogleCloudMessagingAdmin extends Admin
{
    protected $baseRouteName = 'admin_gcm';
    protected $baseRoutePattern = 'gcm';

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(array('list'));
        $collection->add('send');
    }
}
