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

namespace Shopware\Behat\ShopwareExtension\Factory;

use Behat\Mink\Mink;
use SensioLabs\Behat\PageObjectExtension\PageObject\Factory\DefaultFactory;
use SensioLabs\Behat\PageObjectExtension\PageObject\Factory\ClassNameResolver;
use SensioLabs\Behat\PageObjectExtension\PageObject\Factory;

class ShopwareFactory extends DefaultFactory implements Factory
{
    /** @var \Behat\Mink\Mink  */
    private $mink;

    /**
     * @var ClassNameResolver
     */
    private $classNameResolver;

    public function __construct(Mink $mink, ClassNameResolver $classNameResolver, array $pageParameters)
    {
        $this->mink = $mink;
        $this->classNameResolver = $classNameResolver;
        parent::__construct($mink, $classNameResolver, $pageParameters);
    }

    public function getSession()
    {
        return $this->mink->getSession();
    }

    public function getClassNameResolver()
    {
        return $this->classNameResolver;
    }
}
