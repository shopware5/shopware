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

namespace Shopware\Bundle\SearchBundleDBAL\ConditionHandler;

use Shopware\Bundle\SearchBundle\Condition\HasPriceCondition;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundleDBAL\ConditionHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\PriceHelper;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

/**
 * @deprecated since 5.0.1 will be removed in 5.1
 * @category  Shopware
 * @package   Shopware\Bundle\SearchBundleDBAL\ConditionHandler
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class HasPriceConditionHandler implements ConditionHandlerInterface
{
    /**
     * @var PriceHelper
     */
    private $priceHelper;

    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @param PriceHelper $priceHelper
     * @param \Shopware_Components_Config $config
     */
    public function __construct(
        PriceHelper $priceHelper,
        \Shopware_Components_Config $config
    ) {
        $this->priceHelper = $priceHelper;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsCondition(ConditionInterface $condition)
    {
        return ($condition instanceof HasPriceCondition);
    }

    /**
     * {@inheritdoc}
     */
    public function generateCondition(
        ConditionInterface $condition,
        QueryBuilder $query,
        ShopContextInterface $context
    ) {
        Shopware()->Container()->get('corelogger')->notice(
            __CLASS__ . ' is deprecated. Please remove usage'
        );
    }
}
