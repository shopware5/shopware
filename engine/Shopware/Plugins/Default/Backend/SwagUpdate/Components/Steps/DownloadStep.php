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

namespace ShopwarePlugins\SwagUpdate\Components\Steps;

use ShopwarePlugins\SwagUpdate\Components\Download;
use ShopwarePlugins\SwagUpdate\Components\Struct\Version;

class DownloadStep
{
    /**
     * @var array
     */
    private $version;

    /**
     * @var string
     */
    private $destination;

    /**
     * @param string $destination
     */
    public function __construct(Version $version, $destination)
    {
        $this->version = $version;
        $this->destination = $destination;
    }

    /**
     * @param int $offset
     *
     * @return FinishResult|ValidResult
     */
    public function run($offset)
    {
        if (is_file($this->destination) && filesize($this->destination) > 0) {
            return new FinishResult($offset, $this->version->size);
        }

        $download = new Download();
        $startTime = microtime(true);
        $download->setHaltCallback(function () use ($startTime) {
            if (microtime(true) - $startTime > 10) {
                return true;
            }

            return false;
        });
        $offset = $download->downloadFile($this->version->uri, $this->destination, $this->version->size, $this->version->sha1);

        return new ValidResult($offset, $this->version->size);
    }
}
