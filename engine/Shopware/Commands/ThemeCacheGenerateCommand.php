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

namespace Shopware\Commands;

use Doctrine\ORM\AbstractQuery;
use Shopware\Components\CacheManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ThemeCacheGenerateCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:theme:cache:generate')
            ->setDescription('Generates theme caches.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repository = $this->container->get('models')->getRepository('Shopware\Models\Shop\Shop');

        $query = $repository->getShopsWithThemes();

        $shops = $query->getResult(
            AbstractQuery::HYDRATE_OBJECT
        );

        if (empty($shops)) {
            $output->writeln('No theme shops found');

            return;
        }

        /** @var $compiler \Shopware\Components\Theme\Compiler */
        $compiler = $this->container->get('theme_compiler');

        foreach ($shops as $shop) {
            $output->writeln(sprintf('Generating theme cache for shop "%s" ...', $shop->getName()));
            $compiler->compile($shop);
        }

        $output->writeln('Clearing HTTP cache ...');
        /** @var $cacheManager CacheManager */
        $cacheManager = $this->container->get('shopware.cache_manager');
        $cacheManager->clearHttpCache();
    }
}
