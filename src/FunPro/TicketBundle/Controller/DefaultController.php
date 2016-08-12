<?php

namespace FunPro\TicketBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('FunProTicketBundle:Default:index.html.twig');
    }
}
