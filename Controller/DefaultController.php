<?php

namespace BiberLtd\Bundle\PaymentGatewayBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('BiberLtdPaymentGatewayBundle:Default:index.html.twig', array('name' => $name));
    }
}
