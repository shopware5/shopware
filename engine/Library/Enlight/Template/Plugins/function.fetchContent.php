<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
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
