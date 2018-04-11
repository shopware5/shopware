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

namespace Shopware\Bundle\BenchmarkBundle\Hydrator;

use Shopware\Bundle\BenchmarkBundle\Exception\BenchmarkHydratingException;
use Shopware\Bundle\BenchmarkBundle\Struct\BenchmarkResponse;

class BenchmarkResponseHydrator implements HydratorInterface
{
    /**
     * @param array $data
     *
     * @throws BenchmarkHydratingException
     *
     * @return BenchmarkResponse
     */
    public function hydrate(array $data)
    {
        if (empty($data['dateUpdated'])) {
            throw new BenchmarkHydratingException('Missing field "dateUpdated" from server response');
        }

        $date = \DateTime::createFromFormat('Y-m-d H:i:s', $data['dateUpdated'], new \DateTimeZone('UTC'));
        if (!$date) {
            throw new BenchmarkHydratingException(sprintf('Field "dateUpdated" in server response contained invalid data: "%s"', $data['dateUpdated']));
        }

        if (empty($data['token'])) {
            throw new BenchmarkHydratingException('Missing field "token" from server response');
        }

        return new BenchmarkResponse($date, $data['token']);
    }
}
