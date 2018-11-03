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

/**
 * <code>
 * $pointComponent = new Shopware_Components_Benchmark_Point
 * $pointComponent->Start($label);
 * </code>
 */
class Shopware_Components_Benchmark_Point
{
    public $start;
    public $end;
    public $label;
    public $start_ram;
    public $stop_ram;
    public $stopped = false;

    public function Start($label)
    {
        $this->label = $label;
        $this->start = microtime(true);
        $this->start_ram = memory_get_peak_usage(true);

        return $this;
    }

    public function Stop()
    {
        $this->stopped = true;
        $this->end = microtime(true);
        $this->stop_ram = memory_get_peak_usage(true);
    }
}
