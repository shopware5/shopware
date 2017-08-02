<?php

namespace Shopware\Storefront\Controller;

use Shopware\Context\TranslationContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;

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
