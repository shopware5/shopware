<?php

namespace Shopware\Storefront\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class CsrfTokenController extends Controller
{
    /**
     * @Route("/csrftoken", name="csrftoken")
     * @Method({"GET"})
     */
    public function indexAction()
    {
        $token = md5(uniqid('csrf', true));
        return new Response(null, 200, ['X-CSRF-Token' => $token]);
    }
}