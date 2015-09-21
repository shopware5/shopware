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

namespace Shopware\Behat\ShopwareExtension\NameResolver;

use SensioLabs\Behat\PageObjectExtension\PageObject\Factory\CamelcasedClassNameResolver;
use SensioLabs\Behat\PageObjectExtension\PageObject\Factory\ClassNameResolver;

class ShopwareClassNameResolver extends CamelcasedClassNameResolver implements ClassNameResolver
{
    /**
     * @var string
     */
    private $templateName;

    /**
     * @param array $pageNamespaces
     * @param array $elementNamespaces
     */
    public function __construct(array $pageNamespaces = array('\\'), array $elementNamespaces = array('\\'))
    {
        if (count($pageNamespaces) > 1) {
            $pageNamespaces = array($pageNamespaces[1]);
        }

        if (count($elementNamespaces) > 1) {
            $elementNamespaces = array($elementNamespaces[1]);
        }

        $namespace = explode('\\', $pageNamespaces[0]);
        $this->templateName = end($namespace);

        parent::__construct($pageNamespaces, $elementNamespaces);
    }

    public function getTemplateName()
    {
        return $this->templateName;
    }
}
