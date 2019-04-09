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

namespace Shopware\Bundle\BenchmarkBundle\Service;

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Locale;
use Shopware_Components_Snippet_Manager as SnippetManager;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class TranslationService
{
    /**
     * @var SnippetManager
     */
    private $snippetManager;

    /**
     * @var ModelManager
     */
    private $modelManager;

    public function __construct(SnippetManager $snippetManager, ModelManager $modelManager)
    {
        $this->snippetManager = $snippetManager;
        $this->modelManager = $modelManager;
    }

    /**
     * @return array
     */
    public function getAll()
    {
        $translations = [];

        /** @var Locale $germanLocale */
        $germanLocale = $this->modelManager->getRepository(Locale::class)->find(1);
        /** @var Locale $englishLocale */
        $englishLocale = $this->modelManager->getRepository(Locale::class)->find(2);

        $this->snippetManager->setLocale($germanLocale);
        $germanNamespace = $this->snippetManager->getNamespace('backend/benchmark/statistics');
        $germanCategories = $this->loadCategories();
        $germanTimeUnits = $this->loadTimeUnits();

        $this->snippetManager->setLocale($englishLocale);
        $englishNamespace = $this->snippetManager->getNamespace('backend/benchmark/statistics');
        $englishCategories = $this->loadCategories();
        $englishTimeUnits = $this->loadTimeUnits();

        $translations['de'] = $germanNamespace->toArray() + $germanCategories + $germanTimeUnits;
        $translations['en'] = $englishNamespace->toArray() + $englishCategories + $englishTimeUnits;

        return $translations;
    }

    /**
     * @return array
     */
    private function loadCategories()
    {
        $categories = $this->snippetManager->getNamespace('backend/benchmark/categories')->toArray();
        $converter = new CamelCaseToSnakeCaseNameConverter();

        foreach ($categories as $snippetKey => $snippet) {
            $camelCaseKey = $converter->denormalize($snippetKey);
            unset($categories[$snippetKey]);
            $categories[$camelCaseKey] = $snippet;
        }

        return $categories;
    }

    /**
     * @return array
     */
    private function loadTimeUnits()
    {
        return $this->snippetManager->getNamespace('backend/benchmark/time_units')->toArray();
    }
}
