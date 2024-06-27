<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Themes\TestResponsive;

use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Components\Form\Container\Tab;
use Shopware\Components\Form\Container\TabContainer;
use Shopware\Components\Theme\ConfigSet;

class Theme extends \Shopware\Components\Theme
{
    protected $extend = 'TestBare';

    protected $javascript = ['responsive_1.js', 'responsive_2.js'];

    protected $css = ['responsive_1.css', 'responsive_2.css'];

    public function createConfig(TabContainer $container): void
    {
        $container->addTab(new Tab('responsive', 'responsive'));
    }

    public function createConfigSets(ArrayCollection $collection): void
    {
        $collection->add(new ConfigSet('set1', ['value1' => 1]));
        $collection->add(new ConfigSet('set2', ['value1' => 2]));
    }
}
