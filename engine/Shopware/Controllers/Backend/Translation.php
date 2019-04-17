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

use Shopware\Models\Shop\Shop;

class Shopware_Controllers_Backend_Translation extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * @var \Shopware_Components_Translation
     */
    protected $translation;

    /**
     * Assign available translation languages.
     */
    public function getLanguagesAction()
    {
        $node = (int) $this->Request()->getParam('node');
        $node = $node > 0 ? $node : null;
        $sort = $this->Request()->getParam('sort', []);
        $filter = $this->Request()->getParam('filter', []);
        $filter[] = [
            'property' => 'mainId',
            'value' => $node,
        ];

        /** @var Shopware\Models\Shop\Repository $repository */
        $repository = Shopware()->Models()->getRepository(Shop::class);

        $query = $repository->getListQuery($filter, $sort);

        $data = $query->getArrayResult();
        $this->View()->assign([
            'success' => true, 'data' => $data, 'total' => count($data),
        ]);
    }

    /**
     * Assign stored translation data.
     */
    public function readTranslationAction()
    {
        $type = (string) $this->Request()->getParam('type');
        $merge = (bool) $this->Request()->getParam('merge');
        $key = (int) $this->Request()->getParam('key', 1);
        $language = (string) $this->Request()->getParam('language');

        $data = $this->getTranslation()->read($language, $type, $key, $merge);

        $this->View()->assign([
            'data' => $data, 'success' => true,
        ]);
    }

    /**
     * Saves translation data to the  storage.
     */
    public function saveTranslationAction()
    {
        $type = (string) $this->Request()->getParam('type');
        $merge = (bool) $this->Request()->getParam('merge');
        $key = (int) $this->Request()->getParam('key', 1);
        $language = (string) $this->Request()->getParam('language');
        $data = (array) $this->Request()->getParam('data', []);

        $this->View()->assign([
            'success' => $this->getTranslation()->write(
                $language,
                $type,
                $key,
                $data,
                $merge
            ),
        ]);
    }

    /**
     * @return Shopware_Components_Translation
     */
    protected function getTranslation()
    {
        if (!isset($this->translation)) {
            $this->translation = $this->container->get('translation');
        }

        return $this->translation;
    }
}
