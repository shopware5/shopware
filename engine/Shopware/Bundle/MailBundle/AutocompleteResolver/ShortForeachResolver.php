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

namespace Shopware\Bundle\MailBundle\AutocompleteResolver;

class ShortForeachResolver extends AbstractResolver
{
    private const REGEX = '/{foreach\s+\$(?<arrayName>\w+)\s+as\s+(?<key>\$(\w+)\s*=>\s*)?\$(?<value>\w+)}/m';

    public function completer(array $context, string $smartyCode): array
    {
        preg_match_all(self::REGEX, $smartyCode, $matches, PREG_SET_ORDER, 0);

        if (!empty($matches)) {
            foreach ($matches as $match) {
                if (!isset($match['arrayName'], $match['value'])) {
                    continue;
                }

                $value = $this->getValueFromPath($context, $match['arrayName']);

                if ($value === null) {
                    continue;
                }

                if (count($value)) {
                    $key = array_keys($value)[0];
                    $context[$match['value']] = $value[$key];
                    if (isset($match['key'])) {
                        $context[$match['key']] = $key;
                    }
                } else {
                    $context[$match['value']] = [];
                    if (isset($match['key'])) {
                        $context[$match['key']] = 0;
                    }
                }
            }
        }

        return $context;
    }
}
