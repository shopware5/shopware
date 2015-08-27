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

use Shopware\Bundle\SearchBundleDBAL\SearchTerm\SearchIndexerInterface;

/**
 * Shopware Plugin Frontend CronRefresh
 *
 * Plugin to cleanup shopware statistic tables in intervals
 */
class Shopware_Plugins_Frontend_CronRefresh_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Defining Cronjob-Events
     *
     * @return bool
     */
    public function install()
    {
        $this->subscribeEvent('Shopware_CronJob_Clearing', 'onCronJobClearing');
        $this->subscribeEvent('Shopware_CronJob_Search', 'onCronJobSearch');

        return true;
    }

    /**
     * Clear s_emarketing_lastarticles / s_statistics_search / s_core_log in 30 days interval
     * Delete all entries older then 30 days.
     * To change this time - modify sql-queries
     *
     * @static
     * @param Shopware_Components_Cron_CronJob $job
     * @return Array
     */
    public function onCronJobClearing(Shopware_Components_Cron_CronJob $job)
    {
        // Delete all entries from lastarticles older than 30 days
        $sql = '
            DELETE FROM s_emarketing_lastarticles WHERE `time` < date_add(current_date, INTERVAL -30 DAY)
        ';
        $result = Shopware()->Db()->query($sql);
        $data['lastarticles']['rows'] = $result->rowCount();

        // Delete all entries from search statistic older than 30 days
        $sql = '
            DELETE FROM s_statistics_search WHERE datum < date_add(current_date, INTERVAL -30 DAY)
        ';
        $result = Shopware()->Db()->query($sql);
        $data['search']['rows'] = $result->rowCount();

        // Delete all entries from s_core_log older than 30 days
        $sql = '
            DELETE FROM s_core_log WHERE `date` < date_add(current_date, INTERVAL -30 DAY)
        ';
        $result = Shopware()->Db()->query($sql);
        $data['log']['rows'] = $result->rowCount();

        $data['referrer']['rows'] = $this->deleteOldReferrerData(Shopware()->Config()->maximumReferrerAge);
        $data['article_impression']['rows'] = $this->deleteOldArticleImpressionData(Shopware()->Config()->maximumImpressionAge);

        return $data;
    }

    /**
     * Recreate shopware search index
     *
     * @param Shopware_Components_Cron_CronJob $job
     * @return void
     */
    public function onCronJobSearch(Shopware_Components_Cron_CronJob $job)
    {
        /* @var $indexer SearchIndexerInterface */
        $indexer = $this->get('shopware_searchdbal.search_indexer');
        $indexer->build();
    }

    /**
     * Delete old entries from s_statistics_referrer
     * by default older than 90 days
     *
     * @param $maximumReferrerAge
     * @return int
     */
    private function deleteOldReferrerData($maximumReferrerAge)
    {
        $maximumReferrerAge = intval($maximumReferrerAge);
        if ($maximumReferrerAge === 0) {
            $maximumReferrerAge = 90;
        }
        //negate the value and quote it for the sql statement
        $maximumReferrerAge = Shopware()->Db()->quote($maximumReferrerAge * -1);
        $sql = '
            DELETE FROM s_statistics_referer WHERE `datum` < date_add(current_date, INTERVAL ' . $maximumReferrerAge . ' DAY)
        ';
        $result = Shopware()->Db()->query($sql);

        return $result->rowCount();
    }

    /**
     * Delete old entries from s_statistics_article_impression
     * by default older than 90 days
     *
     * @param $maximumAge
     * @return int
     */
    private function deleteOldArticleImpressionData($maximumAge)
    {
        $maximumAge = intval($maximumAge);
        if ($maximumAge === 0) {
            $maximumAge = 90;
        }
        //negate the value and quote it for the sql statement
        $maximumAge = Shopware()->Db()->quote($maximumAge * -1);
        $sql = '
            DELETE FROM  s_statistics_article_impression WHERE `date` < date_add(current_date, INTERVAL ' . $maximumAge . ' DAY)
        ';
        $result = Shopware()->Db()->query($sql);

        return $result->rowCount();
    }
}
