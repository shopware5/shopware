<?php

class Shopware_Tests_Plugins_Core_MarketingAggregate_Components_AlsoBoughtTest extends Shopware_Tests_Plugins_Core_MarketingAggregate_AbstractMarketing
{

    protected function getAllAlsoBought($condition = '')
    {
        return $this->Db()->fetchAll('SELECT * FROM s_articles_also_bought_ro ' . $condition);
    }

    protected function resetAlsoBought($condition = '')
    {
        $this->Db()->query("DELETE FROM s_articles_also_bought_ro " . $condition);
    }


    public function testInitAlsoBought()
    {
        $this->resetAlsoBought();
        $this->AlsoBought()->initAlsoBought();

        $this->assertCount(18, $this->getAllAlsoBought());
    }

    public function testRefreshBoughtArticles()
    {
        $this->resetAlsoBought();
        $this->AlsoBought()->initAlsoBought();

        $combinations = $this->getAllAlsoBought();
        foreach($combinations as $combination) {
            $this->AlsoBought()->refreshBoughtArticles(
                $combination['article_id'],
                $combination['related_article_id']
            );
            $updated = $this->getAllAlsoBought(
                " WHERE article_id = " . $combination['article_id'] .
                " AND related_article_id = " . $combination['related_article_id']
            );
            $updated = $updated[0];

            $this->assertNotEmpty($updated);
            $this->assertEquals($combination['sales'] + 1, $updated['sales']);
        }
    }
}