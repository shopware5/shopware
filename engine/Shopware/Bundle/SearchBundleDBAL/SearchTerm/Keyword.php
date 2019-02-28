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

class Keyword
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $word;

    /**
     * @var string
     */
    private $term;

    /**
     * @var float
     */
    private $relevance;

    /**
     * @param int    $id
     * @param float  $relevance
     * @param string $term
     * @param string $word
     */
    public function __construct($id, $relevance, $term, $word)
    {
        $this->id = $id;
        $this->relevance = $relevance;
        $this->term = $term;
        $this->word = $word;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return float
     */
    public function getRelevance()
    {
        return $this->relevance;
    }

    /**
     * @return string
     */
    public function getTerm()
    {
        return $this->term;
    }

    /**
     * @return string
     */
    public function getWord()
    {
        return $this->word;
    }
}
