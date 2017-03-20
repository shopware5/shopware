<?php

namespace Shopware\Storefront\Controller\Widgets;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Shopware\Storefront\Controller\Controller;

class CheckoutController extends Controller
{
    /**
     * @Route("/widgets/checkout/info", name="widgets/checkout/info")
     * @Method({"GET"})
     */
    public function shopMenuAction()
    {
        return $this->render('@Shopware/widgets/checkout/info.html.twig', [
            'sBasketQuantity' => 0,
            'sBasketAmount' => 0,
            'sNotesQuantity' => 0,
            'sUserLoggedIn' => false,
        ]);
    }
}