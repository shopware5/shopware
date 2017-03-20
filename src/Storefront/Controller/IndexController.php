<?php

namespace Shopware\Storefront\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {


        /** @var $context ShopContextInterface */
//        $context = Shopware()->Container()->get('shopware_storefront.context_service')->getShopContext();
//        $categoryId = $context->getShop()->getCategory()->getId();

//        $emotions = $this->get('emotion_device_configuration')->get($categoryId);

//        $categoryContent = Shopware()->Modules()->Categories()->sGetCategoryContent($categoryId);

        return $this->render('frontend/home/index.html.twig', [
//            'emotions' => $emotions,
//            'hasEmotion' => !empty($emotions),
//            'sCategoryContent' => $categoryContent,
//            'sBanner' => Shopware()->Modules()->Marketing()->sBanner($categoryId),
        ]);
    }

    /**
     * @Route("/seo/{id}")
     */
    public function seoAction($id, Request $request)
    {
        $output = $request->get('_shop');
        $output['seo_id'] = $id;

        return $this->json($output);
    }
}
