<?php

declare(strict_types=1);
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

use Shopware\Components\Model\Exception\ModelNotFoundException;
use Shopware\Models\Config\Element as ConfigElement;
use Shopware\Models\Config\Value;
use Shopware\Models\Shop\Shop;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigSetCommand extends ShopwareCommand
{
    protected function configure(): void
    {
        $this
            ->setName('sw:config:set')
            ->addOption('shopId', null, InputOption::VALUE_OPTIONAL, 'If provided, the configuration will be set for the specified shop. Otherwise, it will be set for the default shop.')
            ->addOption('decode', 'd', InputOption::VALUE_NONE, 'If provided, the input value will be interpreted as JSON. Use this option to provide values as boolean, integer or float.')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the configuration element.')
            ->addArgument('value', InputArgument::REQUIRED, 'The new value for the specified configuration element.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $modelManager = $this->container->get('models');
        $shopRepository = $modelManager->getRepository(Shop::class);

        $shopId = (int) $input->getOption('shopId');
        $name = (string) $input->getArgument('name');
        $inputValue = (string) $input->getArgument('value');
        $decode = (bool) $input->getOption('decode');

        if ($decode) {
            $inputValue = json_decode($inputValue, true);
        }

        $shop = $shopRepository->find($shopId);
        if (!$shop instanceof Shop) {
            $shop = $shopRepository->getDefault();
        }

        $shopId = $shop->getId();

        $element = $modelManager->getRepository(ConfigElement::class)->findOneBy(['name' => $name]);
        if (!$element instanceof ConfigElement) {
            throw new ModelNotFoundException(ConfigElement::class, $name, 'name');
        }

        $value = $element->getValues()->filter(static fn (Value $value) => $value->getShopId() === $shopId)->first();

        if ($value instanceof Value) {
            $value->setValue($inputValue);
        } else {
            $value = new Value();
            $value->setElement($element);
            $value->setShop($shop);
            $value->setValue($inputValue);

            $modelManager->persist($value);
        }

        $modelManager->flush($value);

        return 0;
    }
}
