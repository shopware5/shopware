<?php
/**
 * Enlight
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://enlight.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Enlight
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */

use Shopware\Bundle\ContentTypeBundle\Structs\Criteria;

function smarty_function_fetchContent(array $parameters, Enlight_Template_Default $template)
{
    if (!isset($parameters['type'])) {
        throw new RuntimeException('Smarty method \'fetchContent\' needs an array key \'type\' in parameters');
    }

    if (!isset($parameters['assign'])) {
        throw new RuntimeException('Smarty method \'fetchContent\' needs an array key \'assign\' in parameters');
    }

    if (!isset($parameters['offset'])) {
        $parameters['offset'] = 0;
    }

    if (!isset($parameters['limit'])) {
        $parameters['limit'] = 10;
    }

    if (!isset($parameters['sort'])) {
        $parameters['sort'] = [];
    }

    if (!isset($parameters['filter'])) {
        $parameters['filter'] = [];
    }

    /** @var \Shopware\Bundle\ContentTypeBundle\Services\RepositoryInterface $service */
    $service = Shopware()->Container()->get('shopware.bundle.content_type.' . $parameters['type']);

    $criteria = new Criteria();
    $criteria->offset = $parameters['offset'];
    $criteria->limit = $parameters['limit'];
    $criteria->sort = $parameters['sort'];
    $criteria->filter = $parameters['filter'];

    $template->assign($parameters['assign'], $service->findAll($criteria)->items);
}
