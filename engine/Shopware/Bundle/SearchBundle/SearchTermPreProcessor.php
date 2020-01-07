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

namespace Shopware\Bundle\SearchBundle;

class SearchTermPreProcessor implements SearchTermPreProcessorInterface
{
    /**
     * @param string $term
     *
     * @return string
     */
    public function process($term)
    {
        //This fix prevents an exception if the search term includes an character that can not be
        //displayed with the database charset.
        //This fix can be removed if all tables are set to utf8mb4_unicode_ci.

        //converts encoded characters back ('%a5%27' to '?')
        $term = mb_convert_encoding($term, 'UTF-8');
        //does the replacing of 4byte chars to the Unicode replacement character
        $term = preg_replace('/[\xF0-\xF7].../s', '�', $term);

        //we have to strip the / otherwise broken urls would be created e.g. wrong pager urls
        $term = trim(strip_tags(htmlspecialchars_decode(stripslashes($term))));

        return str_replace('/', ' ', $term);
    }
}
