<?php
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

namespace Shopware\Tests\Functional\Components\Snippet;

use Enlight_Components_Test_TestCase;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Snippet\DatabaseHandler;
use Shopware\Models\Shop\Locale;
use Shopware\Models\Snippet\Snippet;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class DatabaseHandlerTest extends Enlight_Components_Test_TestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    /**
     * Tests the import of snippets with missing locale.
     * Asserts that all remaining snippets of a plugin are imported correctly even if some
     * of the snippets are skipped because the respective locale is missing.
     */
    public function testLoadToDatabaseWithMissingLocale(): void
    {
        $em = $this->getContainer()->get(ModelManager::class);

        // Delete necessary locale
        $germanLocale = $em->getRepository(Locale::class)->findOneBy([
            'locale' => 'de_DE',
        ]);
        static::assertInstanceOf(Locale::class, $germanLocale);
        $em->remove($germanLocale);
        // Delete snippets of the AdvancedMenu plugin
        $advancedMenuSnippets = $em->getRepository(Snippet::class)->findBy([
            'namespace' => 'frontend/plugins/advanced_menu/advanced_menu',
        ]);
        foreach ($advancedMenuSnippets as $advancedMenuSnippet) {
            $em->remove($advancedMenuSnippet);
        }
        $em->flush();

        // No snippets are remaining
        $advancedMenuSnippets = $em->getRepository(Snippet::class)->findBy([
            'namespace' => 'frontend/plugins/advanced_menu/advanced_menu',
        ]);
        static::assertCount(0, $advancedMenuSnippets);

        // (partial) import snippets of the AdvancedMenu plugin
        $pluginBootstrap = Shopware()->Plugins()->Frontend()->AdvancedMenu();
        $this->getContainer()->get(DatabaseHandler::class)->loadToDatabase($pluginBootstrap->Path() . 'Snippets/');

        // Check that all snippets of the remaining locale are installed
        $advancedMenuSnippets = $em->getRepository(Snippet::class)->findBy([
            'namespace' => 'frontend/plugins/advanced_menu/advanced_menu',
        ]);
        static::assertCount(3, $advancedMenuSnippets);

        // Restore locale
        $sql = "INSERT INTO s_core_locales values (1, 'de_DE', 'Deutsch', 'Deutschland');";
        Shopware()->Db()->exec($sql);

        // Import all remaining snippets
        $this->getContainer()->get(DatabaseHandler::class)
            ->loadToDatabase($pluginBootstrap->Path() . 'Snippets/');
    }
}
