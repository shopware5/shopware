<?php

namespace LowStockEmailCron;

use Doctrine\ORM\AbstractQuery;
use Shopware\Components\Plugin;
use Shopware\Models\Article\Detail;
use Shopware_Components_Cron_CronJob;

class LowStockEmailCron extends Plugin
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'Shopware_CronJob_SendLowStockEmail' => 'onSendLowStockEmailCronjob'
        ];
    }

    public function onSendLowStockEmailCronjob(Shopware_Components_Cron_CronJob $job)
    {
        $sender = $this->container->get('low_stock_email.services.sender');
        $config = $this->container
            ->get('shopware.plugin.cached_config_reader')
            ->getByPluginName('LowStockEmail');
        $config['recipient'] = Shopware()->Config()->get('mail');
        $config['product_count'] = $this->getProductsWithLowStockCount($config['low_stock_qty']);
        $sender->send($config);
    }

    /**
     * @param int $qty
     * @return mixed
     */
    public function getProductsWithLowStockCount(int $qty)
    {
        return $this->container
            ->get('shopware.api.article')
            ->getRepository()
            ->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->innerJoin(Detail::class, 'd', 'd.articleID = a.id')
            ->where('d.inStock <= :qty AND a.active = 1')
            ->setParameter('qty', $qty)
            ->getQuery()
            ->setHydrationMode(AbstractQuery::HYDRATE_SINGLE_SCALAR)
            ->execute();
    }
}
