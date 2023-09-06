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

namespace Shopware\Tests\Functional\Plugins\Core\MarketingAggregate\Components;

use Enlight_Event_EventArgs;
use Shopware\Tests\Functional\Plugins\Core\MarketingAggregate\AbstractMarketing;
use Symfony\Component\HttpFoundation\Response;

class SimilarShownTest extends AbstractMarketing
{
    public function testResetSimilarShown(): void
    {
        $this->SimilarShown()->resetSimilarShown();
        static::assertCount(0, $this->getAllSimilarShown());
    }

    public function testInitSimilarShown(): void
    {
        $this->insertDemoData();

        $this->SimilarShown()->initSimilarShown();

        $data = $this->getAllSimilarShown();

        static::assertCount(144, $data);
    }

    public function testUpdateElapsedSimilarShownArticles(): void
    {
        $this->insertDemoData();

        $this->setSimilarShownInvalid();

        $this->SimilarShown()->updateElapsedSimilarShownArticles(10);

        $articles = $this->getAllSimilarShown(" WHERE init_date > '2010-01-01' ");

        static::assertCount(10, $articles);

        $this->SimilarShown()->updateElapsedSimilarShownArticles();

        $articles = $this->getAllSimilarShown(" WHERE init_date > '2010-01-01' ");

        static::assertCount(
            \count($this->getAllSimilarShown()),
            $articles
        );
    }

    public function testRefreshSimilarShown(): void
    {
        $this->insertDemoData();
        $this->SimilarShown()->initSimilarShown();

        $similarShown = $this->getAllSimilarShown();

        foreach ($similarShown as $combination) {
            $this->SimilarShown()->refreshSimilarShown((int) $combination['article_id'], (int) $combination['related_article_id']);
            $updated = $this->getAllSimilarShown(
                ' WHERE article_id = ' . $combination['article_id'] .
                ' AND related_article_id = ' . $combination['related_article_id']
            );
            $updated = $updated[0];
            static::assertSame((int) $combination['viewed'] + 1, (int) $updated['viewed']);
        }
    }

    /**
     * @group skipElasticSearch
     */
    public function testSimilarShownLiveRefresh(): void
    {
        $this->insertDemoData();
        $this->SimilarShown()->initSimilarShown();

        $countBefore = \count($this->getAllSimilarShown());
        $this->saveConfig('similarRefreshStrategy', 3);
        Shopware()->Container()->get('cache')->clean();

        $this->setSimilarShownInvalid(' LIMIT 20');

        Shopware()->Events()->notify('Shopware_Plugins_LastArticles_ResetLastArticles', []);

        $articles = $this->getAllSimilarShown();

        static::assertCount($countBefore - 20, $articles);
    }

    public function testSimilarCronJobRefresh(): void
    {
        $this->insertDemoData();
        $this->SimilarShown()->initSimilarShown();

        $this->saveConfig('similarRefreshStrategy', 2);
        Shopware()->Container()->get('cache')->clean();

        $this->setSimilarShownInvalid();

        $result = $this->dispatch('/sommerwelten/accessoires/170/sonnenbrille-red');
        static::assertSame(Response::HTTP_OK, $result->getHttpResponseCode());

        $articles = $this->getAllSimilarShown(" WHERE init_date > '2010-01-01' ");
        static::assertCount(0, $articles);

        $cron = $this->Db()->fetchRow("SELECT * FROM s_crontab WHERE action = 'RefreshSimilarShown'");
        static::assertNotEmpty($cron);

        // the cron plugin isn't installed, so we can't use a dispatch on /backend/cron
        $this->Plugin()->refreshSimilarShown(new Enlight_Event_EventArgs(['subject' => $this]));

        $articles = $this->getAllSimilarShown(" WHERE init_date > '2010-01-01' ");
        static::assertCount(
            \count($this->getAllSimilarShown()),
            $articles
        );
    }

    /**
     * @return list<array{articleID: string, sessionID: string, time: string, userID: string, shopID: string}>
     */
    private function getDemoData(): array
    {
        return require __DIR__ . '/fixtures/similarShown.php';
    }

    /**
     * The demo data contains 144 combinations of the similar shown articles for three users.
     */
    private function insertDemoData(): void
    {
        $this->Db()->query('DELETE FROM s_emarketing_lastarticles');
        $statement = $this->Db()->prepare(
            'INSERT INTO s_emarketing_lastarticles (articleID, sessionID, time, userID, shopID)
             VALUES(:articleID, :sessionID, :time, :userID, :shopID)'
        );
        foreach ($this->getDemoData() as $data) {
            $statement->execute($data);
        }
    }

    /**
     * @return array<array<string, string>>
     */
    private function getAllSimilarShown(string $condition = ''): array
    {
        return $this->Db()->fetchAll('SELECT * FROM s_articles_similar_shown_ro ' . $condition);
    }

    private function setSimilarShownInvalid(string $condition = ''): void
    {
        $this->Db()->query(' UPDATE s_articles_similar_shown_ro SET init_date = :date ' . $condition, [
            'date' => '2010-01-01',
        ]);
    }
}
