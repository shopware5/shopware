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

namespace Shopware\Category\Gateway;

use Shopware\Category\Struct\CategoryCollection;
use Shopware\Context\TranslationContext;
use Shopware\Search\Criteria;

class CategoryRepository
{
    const FETCH_LIST = 'list';

    /**
     * @var CategoryReader
     */
    private $reader;

    /**
     * @var CategorySearcher
     */
    private $searcher;

    public function __construct(CategoryReader $reader, CategorySearcher $searcher)
    {
        $this->reader = $reader;
        $this->searcher = $searcher;
    }

    public function search(Criteria $criteria, TranslationContext $context): CategorySearchResult
    {
        return $this->searcher->search($criteria, $context);
    }

    public function read(array $ids, TranslationContext $context, string $fetchMode): CategoryCollection
    {
        return $this->reader->read($ids, $context);
    }
}
