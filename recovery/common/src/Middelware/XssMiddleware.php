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

namespace Shopware\Recovery\Common\Middelware;

use Slim\Middleware;
use voku\helper\AntiXSS;

class XssMiddleware extends Middleware
{
    public function call()
    {
        $env = $this->app->environment();
        $env['slim.input_original'] = $env['slim.input'];
        $env['slim.input'] = $this->parse($env['slim.input']);

        $this->next->call();
    }

    private function parse(string $input)
    {
        $output = [];
        if (\function_exists('mb_parse_str') && !isset($this->env['slim.tests.ignore_multibyte'])) {
            mb_parse_str($input, $output);
        } else {
            parse_str($input, $output);
        }

        /** @var AntiXSS $xss */
        $xss = new AntiXSS();

        return \is_array($input) ? array_map(function ($data) use ($xss) {
            return $xss->xss_clean($data);
        }, $input) : $xss->xss_clean($input);
    }
}
