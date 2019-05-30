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

namespace Shopware\Components\Privacy;

use Shopware_Components_Config;

/**
 * This class is intended to improve usability of the ip anonymization feature across
 * the shop by abstracting away if the setting for anonymization is active or not.
 */
class ConfigDependentIpAnonymizer implements IpAnonymizerInterface
{
    /**
     * @var IpAnonymizerInterface
     */
    private $ipAnonymizer;

    /**
     * @var bool
     */
    private $doAnonymize;

    public function __construct(IpAnonymizerInterface $ipAnonymizer, Shopware_Components_Config $config)
    {
        $this->ipAnonymizer = $ipAnonymizer;
        $this->doAnonymize = $config->get('anonymizeIp');
    }

    /**
     * {@inheritdoc}
     */
    public function anonymize($ip)
    {
        if ($this->doAnonymize) {
            return $this->ipAnonymizer->anonymize($ip);
        }

        return $ip;
    }
}
