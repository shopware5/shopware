<?php
/**
 * Returns a custom page's content either as HTML or plain text
 *
 * @param $params
 * @param $smarty
 * @return string
 */
function smarty_function_getPageContent($params, $smarty)
{
    $shopId = Shopware()->Container()->get('shopware_storefront.context_service')->getShopContext()->getShop()->getId();
    $staticPage = Shopware()->Modules()->Cms()->sGetStaticPage($params["id"], $shopId);
    if (!empty($staticPage['html']))
    {
        if (empty($params["plain"]))
        {
            return $staticPage['html'];
        }
        else
        {
            $html = $staticPage['html'];
            $search =  [
                          "/\r/", 
                          "/[\n\t]+/", 
                          "/<head\b[^>]*>.*?<\/head>/i", 
                          "/<script\b[^>]*>.*?<\/script>/i", 
                          "/<style\b[^>]*>.*?<\/style>/i", 
                          "/(<ul\b[^>]*>|<\/ul>)/i", 
                          "/(<ol\b[^>]*>|<\/ol>)/i", 
                          "/(<dl\b[^>]*>|<\/dl>)/i", 
                          "/<li\b[^>]*>(.*?)<\/li>/i", 
                          "/<dd\b[^>]*>(.*?)<\/dd>/i", 
                          "/<dt\b[^>]*>(.*?)<\/dt>/i", 
                          "/<li\b[^>]*>/i", 
                          "/<hr\b[^>]*>/i", 
                          "/<div\b[^>]*>/i", 
                          "/<p\b[^>]*>/i", 
                          "/<br\b[^>]*>/i", 
                          "/<h[123456]+\b[^>]*>/i", 
                          "/(<table\b[^>]*>|<\/table>)/i", 
                          "/(<tr\b[^>]*>|<\/tr>)/i", 
                          "/<td\b[^>]*>(.*?)<\/td>/i"
                       ];
            $replace = [
                          "", 
                          " ", 
                          "", 
                          "", 
                          "", 
                          "\n\n", 
                          "\n\n", 
                          "\n\n", 
                          "\t* \\1\n", 
                          " \\1\n", 
                          "\t* \\1", 
                          "\n\t* ", 
                          "\n-------------------------\n", 
                          "\n\n", 
                          "\n\n", 
                          "\n\n", 
                          "\n\n", 
                          "\n\n", 
                          "\n", 
                          "\t\t\\1\n"
                       ];
            $text = strip_tags(preg_replace($search, $replace, html_entity_decode($html)));
            return $text;
        }
    }
}
