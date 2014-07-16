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

class Shopware_StoreApi_Models_Query_Criterion_ParentId extends Shopware_StoreApi_Models_Query_Criterion_Criterion
{
    /**
     * @param int|array $ids
     */
    public function __construct($ids)
    {
        if (is_array($ids)) {
            foreach ($ids as $id) {
                $this->addId($id);
            }
        } else {
            $this->addId($ids);
        }
    }

    /**
     * @param int $id
     * @return bool
     */
    public function addId($id)
    {
        $id = intval($id);
        if (!empty($id)) {
            $this->collection[] = $id;
            return true;
        } else {
            return false;
        }
    }

    public function getCriterionStatement()
    {
        if (empty($this->collection)) {
            return false;
        } else {
            return array(
                'parent_id' => $this->collection
            );
        }
    }
}
