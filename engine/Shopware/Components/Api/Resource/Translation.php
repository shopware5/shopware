<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

namespace Shopware\Components\Api\Resource;

use Shopware\Components\Api\Exception as ApiException;

/**
 * Translation API Resource
 *
 * @category  Shopware
 * @package   Shopware\Components\Api\Resource
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Translation extends Resource
{

    /** @var \Shopware_Components_Translation $translationWriter */
    protected $translationWriter = null;

    /**
     * @return \Shopware_Components_Translation
     */
    public function getTranslationComponent()
    {
        if ($this->translationWriter === null) {
            $this->translationWriter = new \Shopware_Components_Translation();
        }
        return $this->translationWriter;
    }

    /**
     * @param \Shopware_Components_Translation $translationWriter
     */
    public function setTranslationComponent($translationWriter)
    {
        $this->translationWriter = $translationWriter;
    }

    /**
     * Helper function that will for example unserialize the serialized objectdata and resolve language
     * @param array $translation
     * @return array
     */
    private function prepareTranslationForOutput($translation)
    {
        $translation['objectdata'] = unserialize($translation['objectdata']);
        return $translation;
    }

    /**
     * @param int $id
     * @return array
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     */
    public function getOne($id)
    {

        $this->checkPrivilege('read');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }

        /** @var $translation \Shopware_Components_Translation */
        $translation = Shopware()->Db()->fetchRow(
            "SELECT t.*, l.locale, l.language, l.territory FROM s_core_translations t
                LEFT JOIN s_core_locales l ON l.id = t.objectlanguage
                WHERE t.id = ?",
            array($id)
        );

        if (!$translation) {
            throw new ApiException\NotFoundException("Translation by id $id not found");
        }

        return $this->prepareTranslationForOutput($translation);
    }

    /**
     * @param int $offset
     * @param int $limit
     * @param array $criteria
     * @param array $orderBy
     * @return array
     */
    public function getList($offset = 0, $limit = 25, array $criteria = array(), array $orderBy = array())
    {
        $this->checkPrivilege('read');

        $offset = (int) $offset;
        $limit = (int) $limit;

        $translation = Shopware()->Db()->fetchAll(
            "SELECT t.*, l.locale, l.language, l.territory FROM s_core_translations t
                LEFT JOIN s_core_locales l ON l.id = t.objectlanguage
                LIMIT {$offset},{$limit}"
        );

        return array('data' => $translation, 'total' => count($translation));
    }

    /**
     * @param array $params
     * @return array
     * @throws \Shopware\Components\Api\Exception\ValidationException
     * @throws \Exception
     */
    public function create(array $params)
    {
        $this->checkPrivilege('create');

        $params = $this->prepareTranslationData($params);

        $translationWriter = $this->getTranslationComponent();
        $translationWriter->write(
            $params['objectlanguage'],
            $params['objecttype'],
            $params['objectkey'],
            $params['objectdata']
        );

        $sql  = '
            SELECT `id`
            FROM `s_core_translations`
            WHERE `objecttype` = ?
            AND `objectkey` = ?
            AND `objectlanguage` = ?
        ';
        $id = Shopware()->Db()->fetchOne($sql, array(
            $params['objecttype'],
            $params['objectkey'],
            $params['objectlanguage']
        ));

        if ($id) {
            $translation = $this->getOne($id);
        } else {
            throw new \Exception("Translation wasn't inserted properly");
        }


        return $translation;
    }

    /**
     * @param int $id
     * @param array $params
     * @return array
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Exception
     */
    public function update($id, array $params)
    {
        $this->checkPrivilege('update');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }
        // will throw a not found exception, if id is not found
        $translation = $this->getOne($id);

        $params = $this->prepareTranslationData($params, $translation);

        $this->delete($id);

        $translationWriter = $this->getTranslationComponent();
        $translationWriter->write(
            $params['objectlanguage'],
            $params['objecttype'],
            $params['objectkey'],
            $params['objectdata']
        );

        $sql  = '
            SELECT `id`
            FROM `s_core_translations`
            WHERE `objecttype` = ?
            AND `objectkey` = ?
            AND `objectlanguage` = ?
        ';
        $id = Shopware()->Db()->fetchOne($sql, array(
            $params['objecttype'],
            $params['objectkey'],
            $params['objectlanguage']
        ));

        if ($id) {
            $translation = $this->getOne($id);
        } else {
            throw new \Exception("Translation wasn't inserted properly");
        }

        return $translation;
    }

    /**
     * @param int $id
     * @return array
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     * @throws \Exception
     */
    public function delete($id)
    {
        $this->checkPrivilege('delete');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }

        // will throw a not found exception, if id is not found
        $this->getOne($id);

        Shopware()->Db()->delete('s_core_translations',
        array(
            'id = ?' => $id
        ));

        return true;
    }

    /**
     * @param array $params
     * @param null $translation
     * @return array
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Exception
     */
    private function prepareTranslationData($params, $translation = null)
    {
        $requiredParams = array('objecttype', 'objectdata', 'objectkey', 'objectlanguage');
        foreach ($requiredParams as $param) {
            if (!$translation) {
                if (!isset($params[$param]) || empty($params[$param])) {
                    throw new ApiException\ParameterMissingException($param);
                }
            } else {
                if (isset($params[$param]) && empty($params[$param])) {
                    throw new \Exception('param $param may not be empty');
                }
            }
        }

        if (!is_array($params['objectdata'])) {
            throw new \Exception("objectdata needs to be an array");
        }

        if ($translation) {
            $params = array_merge($translation, $params);
        }

        return $params;
    }
}
