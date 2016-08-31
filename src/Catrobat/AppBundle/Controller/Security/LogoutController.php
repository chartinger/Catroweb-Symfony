<?php

namespace Catrobat\AppBundle\Controller\Security;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LogoutController extends Controller
{
    public function logoutAction()
    {
        TokenStorageInterface::setToken(null);

        return $this->redirect($this->generateUrl('index'));
    }
}
