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
namespace Shopware\Components\Plugin;

use Shopware\Components\Plugin\XmlReader\XmlConfigReader;

@trigger_error('The '.__NAMESPACE__.'\XmlConfigDefinitionReader class is deprecated since version 5.2 and will be removed in 5.3. Use Shopware\Components\Plugin\XmlReader\XmlConfigReader instead.', E_USER_DEPRECATED);

/**
 * Class XmlConfigDefinitionReader
 *
 * @package Shopware\Components\Plugin
 */
class XmlConfigDefinitionReader extends XmlConfigReader {
}