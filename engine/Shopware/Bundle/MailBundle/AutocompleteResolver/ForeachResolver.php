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

class ForeachResolver extends AbstractResolver
{
    private const FOREACH_REGEX = '/{foreach\s+\w+=[a-zA-Z0-9\$](.*)}/m';
    private const ATTR_REGEX = '/(\s*(?<key>\w+)=(?<value>[a-zA-Z0-9\$]+)\s*)/';

    public function completer(array $context, string $smartyCode): array
    {
        foreach ($this->getMatches($smartyCode) as $match) {
            if (!isset($match['from'], $match['item'])) {
                continue;
            }

            $value = $this->getValueFromPath($context, $match['from']);

            if ($value === null) {
                continue;
            }

            $count = count($value);

            if ($count) {
                $key = array_keys($value)[0];
                $context[$match['item']] = $value[$key];
                if (isset($match['key'])) {
                    $context[$match['key']] = $key;
                }
            } else {
                $context[$match['item']] = [];
                if (isset($match['key'])) {
                    $context[$match['key']] = 0;
                }
            }
        }

        return $context;
    }

    private function getMatches(string $smartyCode): array
    {
        $result = [];
        preg_match_all(self::FOREACH_REGEX, $smartyCode, $matches, PREG_SET_ORDER, 0);

        if (!empty($matches)) {
            foreach ($matches as $forEachEntry) {
                preg_match_all(self::ATTR_REGEX, $forEachEntry[0], $attributeMatches, PREG_SET_ORDER, 0);

                if (!empty($attributeMatches)) {
                    $attributeMatches = $this->extractAttributes($attributeMatches);

                    if (!empty($attributeMatches)) {
                        $result[] = $attributeMatches;
                    }
                }
            }
        }

        return $result;
    }

    private function extractAttributes(array $attributeMatches): array
    {
        $result = [];

        foreach ($attributeMatches as $attributeMatch) {
            $result[$attributeMatch['key']] = ltrim($attributeMatch['value'], '$');
        }

        return $result;
    }
}
