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

namespace Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator;

class Hydrator
{
    /**
     * @param string $prefix
     * @param array  $data
     *
     * @return array
     */
    public function extractFields($prefix, $data)
    {
        $result = [];
        foreach ($data as $field => $value) {
            if (strpos($field, $prefix) === 0) {
                $key = str_replace($prefix, '', $field);
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * @deprecated in 5.6, will be removed in 5.7 without an replacement.
     *
     * @param string $prefix
     * @param array  $data
     *
     * @return array
     */
    protected function getFields($prefix, $data)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be removed with 5.7. Will be removed without replacement.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $result = [];
        foreach ($data as $field => $value) {
            if (strpos($field, $prefix) === 0) {
                $result[$field] = $value;
            }
        }

        return $result;
    }

    /**
     * @param string $prefix
     *
     * @return array
     */
    protected function addArrayPrefix($prefix, array $data)
    {
        $result = [];
        foreach ($data as $key => $value) {
            $key = $prefix . '_' . $key;
            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * @param array $data
     * @param array $keys
     *
     * @return array
     */
    protected function convertArrayKeys($data, $keys)
    {
        foreach ($keys as $old => $new) {
            if (!isset($data[$old])) {
                continue;
            }

            $data[$new] = $data[$old];
            unset($data[$old]);
        }

        return $data;
    }

    /**
     * @param string|null $prefix
     * @param int|null    $id        used for `merged` translations
     * @param bool        $addPrefix
     *
     * @return array
     */
    protected function getTranslation(array $data, $prefix, array $mapping = [], $id = null, $addPrefix = true)
    {
        if ($prefix === null) {
            $key = 'translation';
        } else {
            $key = $prefix . '_translation';
        }

        $fallback = $key . '_fallback';

        $fallback = $this->extractTranslation($data, $fallback, $id);
        $translation = $this->extractTranslation($data, $key, $id);

        $translation = $translation + $fallback;

        if (!empty($mapping)) {
            $translation = $this->convertArrayKeys($translation, $mapping);
        }

        if (!$addPrefix) {
            return $translation;
        }

        return $this->addArrayPrefix($prefix, $translation);
    }

    /**
     * @param string   $key
     * @param int|null $id
     *
     * @return array
     */
    private function extractTranslation(array $data, $key, $id = null)
    {
        if (!isset($data[$key]) || empty($data[$key])) {
            return [];
        }

        $translation = unserialize($data[$key], ['allowed_classes' => false]);
        if (!$translation) {
            return [];
        }

        if ($id === null) {
            return $translation;
        }

        if (!isset($translation[$id])) {
            return [];
        }

        return $translation[$id];
    }
}
