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

namespace Shopware\Bundle\CustomerSearchBundle;

class CustomerNumberSearchResult
{
    /**
     * @var CustomerNumberRow[]
     */
    private $rows;

    /**
     * @var int
     */
    private $total;

    /**
     * @var string[]
     */
    private $numbers = [];

    /**
     * @var string[]
     */
    private $emails = [];

    /**
     * @var int[]
     */
    private $ids = [];

    /**
     * @param CustomerNumberRow[] $rows
     * @param int                 $total
     */
    public function __construct(array $rows, $total)
    {
        $this->rows = $rows;
        $this->total = $total;
        $this->collectData($rows);
    }

    /**
     * @return CustomerNumberRow[]
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @return string[]
     */
    public function getNumbers()
    {
        return $this->numbers;
    }

    /**
     * @return string[]
     */
    public function getEmails()
    {
        return $this->emails;
    }

    /**
     * @return int[]
     */
    public function getIds()
    {
        return $this->ids;
    }

    /**
     * @param CustomerNumberRow[] $rows
     */
    private function collectData(array $rows)
    {
        foreach ($rows as $row) {
            $this->ids[] = $row->getId();
            $this->emails[] = $row->getEmail();
            $this->numbers[] = $row->getNumber();
        }
    }
}
