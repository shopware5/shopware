<?php

class Shopware_Tests_Plugins_Core_MarketingAggregate_Components_SimilarShownTest extends Shopware_Tests_Plugins_Core_MarketingAggregate_AbstractMarketing
{

    protected function getDemoData()
    {
        return require __DIR__ . '/fixtures/similarShown.php';
    }

    /**
     * The demo data contains 144 combinations of the similar shown articles for three users.
     */
    protected function insertDemoData()
    {
        $this->Db()->query("DELETE FROM s_emarketing_lastarticles");
        $statement = $this->Db()->prepare("
            INSERT INTO s_emarketing_lastarticles (img, name, articleID, sessionID, time, userID, shopID)
            VALUES(:img, :name, :articleID, :sessionID, :time, :userID, :shopID)"
        );
        foreach($this->getDemoData() as $data) {
            $statement->execute($data);
        }
    }

    protected function getAllSimilarShown($condition = '')
    {
        return $this->Db()->fetchAll('SELECT * FROM s_articles_similar_shown_ro ' . $condition);
    }

    protected function resetSimilarShown($condition = '')
    {
        $this->Db()->query("DELETE FROM s_articles_similar_shown_ro " . $condition);
    }

    protected function setSimilarShownInvalid($date = '2010-01-01', $condition = '') {
        $this->Db()->query(" UPDATE s_articles_similar_shown_ro SET init_date = :date " . $condition, array(
            'date' => $date
        ));
    }


    public function testResetSimilarShown()
    {
        $this->SimilarShown()->resetSimilarShown();
        $this->assertCount(0, $this->getAllSimilarShown());
    }

    public function testInitSimilarShown()
    {
        $this->insertDemoData();

        $this->SimilarShown()->initSimilarShown();

        $data = $this->getAllSimilarShown();

        $this->assertCount(144, $data);
    }

    public function testUpdateElapsedSimilarShownArticles()
    {
        $this->insertDemoData();

        $this->setSimilarShownInvalid();

        $this->SimilarShown()->updateElapsedSimilarShownArticles(10);

        $articles = $this->getAllSimilarShown(" WHERE init_date > '2010-01-01' ");

        $this->assertCount(10, $articles);

        $this->SimilarShown()->updateElapsedSimilarShownArticles();

        $articles = $this->getAllSimilarShown(" WHERE init_date > '2010-01-01' ");

        $this->assertCount(
            count($this->getAllSimilarShown()),
            $articles
        );
    }

    public function testRefreshSimilarShown()
    {
        $this->insertDemoData();
        $this->SimilarShown()->initSimilarShown();

        $similarShown = $this->getAllSimilarShown();

        foreach($similarShown as $combination) {
            $this->SimilarShown()->refreshSimilarShown($combination['article_id'], $combination['related_article_id']);
            $updated = $this->getAllSimilarShown(
                " WHERE article_id = " . $combination['article_id'] .
                " AND related_article_id = " . $combination['related_article_id']
            );
            $updated = $updated[0];
            $this->assertEquals($combination['viewed'] + 1, $updated['viewed']);
        }
    }

    public function testSimilarShownLiveRefresh()
    {
        $this->insertDemoData();
        $this->SimilarShown()->initSimilarShown();

        $this->saveConfig('similarRefreshStrategy', 3);
        Shopware()->Cache()->clean();

        $this->setSimilarShownInvalid();

        $result = $this->dispatch('/sommerwelten/accessoires/170/sonnenbrille-red');
        $this->assertEquals(200, $result->getHttpResponseCode());

        $articles = $this->getAllSimilarShown(" WHERE init_date > '2010-01-01' ");
        $this->assertCount(50, $articles);
    }



}