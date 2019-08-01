# CHANGELOG for Shopware 5.7.x

This changelog references changes done in Shopware 5.6 patch versions.

[View all changes from v5.6.0...v5.7.0](https://github.com/shopware/shopware/compare/v5.6.0...v5.7.0)

### Changes

* Changed `Shopware\Models\Order\Order` and `Shopware\Models\Order\Detail`models by extracting business logic into:
    * `Shopware\Bundle\OrderBundle\Service\StockService`
    * `Shopware\Bundle\OrderBundle\Service\CalculationService`
    * `Shopware\Bundle\OrderBundle\Subscriber\ProductStockSubscriber`
    * `Shopware\Bundle\OrderBundle\Subscriber\OrderRecalculationSubscriber`
