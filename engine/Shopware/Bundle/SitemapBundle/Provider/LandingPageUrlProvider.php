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

namespace Shopware\Bundle\SitemapBundle\Provider;

use Shopware\Bundle\SitemapBundle\Struct\Url;
use Shopware\Bundle\SitemapBundle\UrlProviderInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Routing;
use Shopware\Components\Routing\Router;
use Shopware\Models\Emotion\Emotion;

class LandingPageUrlProvider implements UrlProviderInterface
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var bool
     */
    private $allExported = false;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @param ModelManager $modelManager
     * @param Router       $router
     */
    public function __construct(ModelManager $modelManager, Router $router)
    {
        $this->router = $router;
        $this->modelManager = $modelManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrls(Routing\Context $routingContext, ShopContextInterface $shopContext)
    {
        if ($this->allExported) {
            return [];
        }

        $emotionRepository = $this->modelManager->getRepository(Emotion::class);

        $shopId = $shopContext->getShop()->getId();

        $builder = $emotionRepository->getCampaignsByShopId($shopId);
        $campaigns = $builder->getQuery()->getArrayResult();

        if (count($campaigns) === 0) {
            return [];
        }

        foreach ($campaigns as $key => &$campaign) {
            if (!$this->filterCampaign($campaign['validFrom'], $campaign['validTo'])) {
                unset($campaigns[$key]);
                continue;
            }

            $campaign['changed'] = $campaign['modified'];
            $campaign['urlParams'] = [
                'sViewport' => 'campaign',
                'emotionId' => $campaign['id'],
            ];
        }
        unset($campaign);

        $campaigns = array_values($campaigns);

        $routes = $this->router->generateList(array_column($campaigns, 'urlParams'), $routingContext);
        $urls = [];

        for ($i = 0, $routeCount = count($routes); $i < $routeCount; ++$i) {
            $urls[] = new Url($routes[$i], $campaigns[$i]['changed'], 'weekly');
        }

        unset($campaign);

        $this->allExported = true;

        return $urls;
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->allExported = false;
    }

    /**
     * Helper function to filter emotion campaigns
     * Returns false, if the campaign starts later or is outdated
     *
     * @param null|\DateTime $from
     * @param null|\DateTime $to
     *
     * @return bool
     */
    private function filterCampaign($from = null, $to = null)
    {
        $now = new \DateTime();

        if (isset($from) && $now < $from) {
            return false;
        }

        if (isset($to) && $now > $to) {
            return false;
        }

        return true;
    }
}
