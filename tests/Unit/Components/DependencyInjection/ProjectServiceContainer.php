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

namespace Shopware\Tests\Unit\Components\DependencyInjection;

use Shopware\Components\DependencyInjection\Container;

class ProjectServiceContainer extends Container
{
    public $__bar;

    public $__parent;

    public $__child;

    public function __construct()
    {
        parent::__construct();

        $this->__bar = new \stdClass();
        $this->aliases = ['alias' => 'bar'];

        $this->__parent = new \stdClass();
        $this->__child = new \stdClass();
    }

    protected function getBarService(): \stdClass
    {
        return $this->__bar;
    }

    protected function getParentService(): \stdClass
    {
        $this->__parent->child = $this->get('child');

        return $this->__parent;
    }

    protected function getChildService(): \stdClass
    {
        return $this->__child;
    }
}
