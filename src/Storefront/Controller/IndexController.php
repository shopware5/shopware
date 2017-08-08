<?php

namespace Shopware\Storefront\Controller;

use Shopware\Context\Struct\ShopContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(ShopContext $context, Request $request)
    {
        echo '<pre>';
        print_r($context);
        exit();
        return $this->render('frontend/home/index.html.twig', []);
    }
}
