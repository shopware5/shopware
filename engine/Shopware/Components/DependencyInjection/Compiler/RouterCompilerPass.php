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
namespace Shopware\Components\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * @category  Shopware
 * @package   Shopware\Components\DependencyInjection\Compiler
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class RouterCompilerPass implements CompilerPassInterface
{
    Use TagReplaceTrait;

    public function process(ContainerBuilder $container)
    {
        $this->replaceArgumentWithTaggedServices($container, 'router', 'router.matcher', 1);
        $this->replaceArgumentWithTaggedServices($container, 'router', 'router.generator', 2);
        $this->replaceArgumentWithTaggedServices($container, 'router', 'router.prefilter', 3);
        $this->replaceArgumentWithTaggedServices($container, 'router', 'router.postfilter', 4);
    }
}
