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
 *
 * @category   Shopware
 * @package    Shopware_Controllers
 * @subpackage Api
 * @version    $Id$
 * @author     Benjamin Cremer
 */

class Shopware_Controllers_Api_Caches extends Shopware_Controllers_Api_Rest
{
    /**
     * @var Shopware\Components\Api\Resource\Cache
     */
    protected $resource = null;

    public function init()
    {
        $this->resource = \Shopware\Components\Api\Manager::getResource('cache');
    }

    /**
     * Get list of caches
     *
     * GET /api/caches/
     */
    public function indexAction()
    {
        $result = $this->resource->getList();

        $this->View()->assign($result);
        $this->View()->assign('success', true);
    }

    /**
     * Get one cache
     *
     * GET /api/caches/{id}
     */
    public function getAction()
    {
        $id = $this->Request()->getParam('id');

        $cache = $this->resource->getOne($id);

        $this->View()->assign('data', $cache);
        $this->View()->assign('success', true);
    }

    /**
     * Creating caches is not possible
     *
     * @throws RuntimeException
     */
    public function postAction()
    {
        throw new \RuntimeException('Building caches is not possible, yet. You can build the cache by calling the category/product manually.');
    }

    /**
     * Updating caches is not possible
     *
     * @throws RuntimeException
     */
    public function putAction()
    {
        throw new \RuntimeException('Updating caches is not possible, yet. After updating a product or category the cache will be invalidated automatically, if configured in the HTTP-Cache settings..');
    }

    /**
     * Delete cache
     *
     * DELETE /api/caches/{id}
     */
    public function deleteAction()
    {
        $id = $this->Request()->getParam('id');

        $this->resource->delete($id);

        $this->View()->assign(array('success' => true));
    }
}
