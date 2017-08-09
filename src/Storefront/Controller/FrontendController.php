<?php

namespace Shopware\Storefront\Controller;

use Shopware\Category\Struct\Category;
use Shopware\Context\Struct\ShopContext;
use Shopware\Search\Condition\ActiveCondition;
use Shopware\Search\Condition\CustomerGroupCondition;
use Shopware\Search\Condition\ParentCategoryCondition;
use Shopware\Search\Criteria;
use Symfony\Component\HttpFoundation\Response;

abstract class FrontendController extends Controller
{
    protected function render($view, array $parameters = [], Response $response = null): Response
    {
        $context = $this->get('shopware.storefront.context.storefront_context_service')
            ->getShopContext();

        $navigationId = $this->get('request_stack')->getCurrentRequest()->attributes->get('active_category_id');

        $repository = $this->get('shopware.category.gateway.category_repository');

        $category = $repository->read([$navigationId], $context->getTranslationContext(), '')
            ->get($navigationId);

        $parameters['topNavigation'] = $this->getNavigation($context, $category);
        $parameters['activeCategory'] = $category;

        return parent::render($view, $parameters, $response);
    }

    private function getNavigation(ShopContext $context, Category $category)
    {
        $systemCategory = $context->getShop()->getCategory();

        $criteria = new Criteria();
        $criteria->addCondition(new ParentCategoryCondition(array_merge($category->getPath(), [$category->getId()])));
        $criteria->addCondition(new ActiveCondition(true));
        $criteria->addCondition(new CustomerGroupCondition([$context->getCurrentCustomerGroup()->getId()]));

        $repository = $this->get('shopware.category.gateway.category_repository');
        $result = $repository->search($criteria, $context->getTranslationContext());
        $categories = $repository->read($result->getIds(), $context->getTranslationContext(), '');

        return $categories->sortByPosition()->getTree($systemCategory->getId());
    }
}