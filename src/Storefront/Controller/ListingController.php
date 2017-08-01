<?php

namespace Shopware\Storefront\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ListingController extends Controller
{
    /**
     * @Route("/listing/{id}", name="listing_page")
     */
    public function indexAction($id, Request $request)
    {
        echo '<pre>';
        print_r($id);
        exit();
    }
}
