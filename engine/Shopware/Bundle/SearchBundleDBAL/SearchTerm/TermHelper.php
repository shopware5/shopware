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

class TermHelper implements TermHelperInterface
{
    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @var bool
     */
    private $useBadWords;

    /**
     * @var bool
     */
    private $replaceUmlauts;

    /**
     * @var bool
     */
    private $replaceNonLetters;

    /**
     * @param \Shopware_Components_Config $config
     * @param bool                        $useBadWords
     * @param bool                        $replaceUmlauts
     * @param bool                        $replaceNonLetters
     */
    public function __construct($config, $useBadWords = true, $replaceUmlauts = true, $replaceNonLetters = true)
    {
        $this->config = $config;
        $this->useBadWords = $useBadWords;
        $this->replaceUmlauts = $replaceUmlauts;
        $this->replaceNonLetters = $replaceNonLetters;
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
        if ($this->replaceUmlauts) {
            $string = str_replace(
                ['Ü', 'ü', 'ä', 'Ä', 'ö', 'Ö', 'ß'],
                ['Ue', 'ue', 'ae', 'Ae', 'oe', 'Oe', 'ss'],
                $string
            );
        }

        $string = mb_strtolower(html_entity_decode($string), 'UTF-8');

        if ($this->replaceNonLetters) {
            // Remove not required chars from string
            $string = trim(preg_replace("/[^\pL_0-9]/u", ' ', $string));
        }

        // Parse string into array
        $wordsTmp = preg_split('/ /', $string, -1, PREG_SPLIT_NO_EMPTY);

        if (count($wordsTmp)) {
            $words = array_unique($wordsTmp);
        } elseif (!empty($string)) {
            $words = [$string];
        } else {
            return [];
        }

        if ($this->useBadWords) {
            // Check if any keyword is on blacklist
            $words = $this->filterBadWordsFromString($words);
        }

        return $words;
    }

    /**
     * Filter out bad keywords before starting search
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
            if (mb_strlen($word) >= $this->config->get('minSearchIndexLength') && $this->filterBadWordFromString($word)) {
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
