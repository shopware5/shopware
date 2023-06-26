<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Functional\Library\Zend;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Tests\Functional\Traits\ContainerTrait;

class ZendLocaleTest extends TestCase
{
    use ContainerTrait;

    public const KNOWN_FAILURE = [
        'wo_SN',
        'tt_RU',
        'syr_SY',
        'sr_YU',
        'sr_CS',
        'sid_ET',
        'sh_YU',
        'sh_CS',
        'sh_BA',
        'sa_IN',
        'oc_FR',
        'ny_MW',
        'nds_DE',
        'mn_CN',
        'ku_TR',
        'ku_SY',
        'ku_IR',
        'ku_IQ',
        'kpe_LR',
        'kpe_GN',
        'kfo_CI',
        'kcg_NG',
        'kaj_NG',
        'ha_SD',
        'ha_NE',
        'gv_GB',
        'gez_ET',
        'gez_ER',
        'gaa_GH',
        'dv_MV',
        'cch_NG',
    ];

    /**
     * @dataProvider getLocales
     */
    public function testLocalCreation(string $localName): void
    {
        static::assertFileExists(sprintf('%s/engine/Library/Zend/Locale/Data/%s.xml', Shopware()->DocPath(), $localName));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getLocales(): array
    {
        $sql = 'SELECT locale FROM s_core_locales WHERE locale NOT IN(?)';

        return $this->getContainer()->get(Connection::class)->fetchAllAssociative($sql, [self::KNOWN_FAILURE], [Connection::PARAM_STR_ARRAY]);
    }
}
