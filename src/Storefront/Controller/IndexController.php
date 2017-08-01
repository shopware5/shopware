<?php

namespace Shopware\Storefront\Controller;

use Shopware\Context\TranslationContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        return $this->render('frontend/home/index.html.twig', []);
    }
}
