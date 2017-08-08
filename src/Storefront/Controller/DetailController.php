<?php

namespace Shopware\Storefront\Controller;

use Shopware\Context\Struct\ShopContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DetailController extends Controller
{
    /**
     * @Route("/detail/{number}", name="detail_page")
     */
    public function indexAction(string $number, ShopContext $context, Request $request)
    {
        return $this->render('frontend/home/index.html.twig', []);
    }
}



