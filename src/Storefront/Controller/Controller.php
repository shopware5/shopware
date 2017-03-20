<?php

namespace Shopware\Storefront\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as SymfonyController;
use Symfony\Component\HttpFoundation\Response;

abstract class Controller extends SymfonyController
{
    protected function render($view, array $parameters = array(), Response $response = null)
    {
        //remove static template inheritance prefix
        if (strpos($view, '@') === 0) {
            $view = explode('/', $view);
            array_shift($view);
            $view = implode('/', $view);
        }

        $template = $this->get('shopware.storefront.twig.template_finder')->find($view, true);

        return parent::render($template, $parameters, $response);
    }
}