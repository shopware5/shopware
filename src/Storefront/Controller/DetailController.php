<?php

namespace Shopware\Storefront\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DetailController extends Controller
{
    /**
     * @Route("/detail/{number}", name="detail_page")
     */
    public function indexAction(string $number, Request $request)
    {
    }
}
