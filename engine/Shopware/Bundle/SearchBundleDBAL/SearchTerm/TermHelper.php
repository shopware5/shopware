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

namespace Shopware\Bundle\SearchBundleDBAL\SearchTerm;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class TermHelper implements TermHelperInterface
{
    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @param $config
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Parse a string / search term into a keyword array
     *
     * @param string $string
     *
     * @return array
     */
    public function splitTerm($string)
    {
        $string = str_replace(
            ['Ü', 'ü', 'ä', 'Ä', 'ö', 'Ö', 'ß'],
            ['Ue', 'ue', 'ae', 'Ae', 'oe', 'Oe', 'ss'],
            $string
        );

        $string = mb_strtolower(html_entity_decode($string), 'UTF-8');

        // Remove not required chars from string
        $string = trim(preg_replace("/[^\pL_0-9]/u", ' ', $string));

        // Parse string into array
        $wordsTmp = preg_split('/ /', $string, -1, PREG_SPLIT_NO_EMPTY);

        if (count($wordsTmp)) {
            $words = array_unique($wordsTmp);
        } elseif (!empty($string)) {
            $words = [$string];
        } else {
            return [];
        }

        // Check if any keyword is on blacklist
        $words = $this->filterBadWordsFromString($words);

        return $words;
    }

    /**
     * Filter out bad keywords before starting search
     *
     * @param array $words
     *
     * @return array|bool
     */
    private function filterBadWordsFromString(array $words)
    {
        if (!count($words) || !is_array($words)) {
            return false;
        }

        $result = [];

        foreach ($words as $word) {
            if ($this->filterBadWordFromString($word)) {
                $result[] = $word;
            }
        }

        return $result;
    }

    /**
     * Check if a keyword is on blacklist or not
     *
     * @param string $word
     *
     * @return bool
     */
    private function filterBadWordFromString($word)
    {
        static $badWords;

        if (!isset($badWords)) {
            $badWords = preg_split(
                "#[\s,;]+#msi",
                $this->config->get('badwords'),
                -1,
                PREG_SPLIT_NO_EMPTY
            );
        }

        if (in_array((string) $word, $badWords)) {
            return false;
        }

        return true;
    }
}
