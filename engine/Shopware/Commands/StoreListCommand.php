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

use Shopware\Bundle\PluginInstallerBundle\Context\LicenceRequest;
use Shopware\Bundle\PluginInstallerBundle\Struct\LicenceStruct;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StoreListCommand extends StoreCommand implements CompletionAwareInterface
{
    /**
     * {@inheritdoc}
     */
    public function completeOptionValues($optionName, CompletionContext $context)
    {
        if ($optionName === 'domain') {
            return $this->completeLicensedDomain($context->getCurrentWord());
        }

        if ($optionName === 'shopware-version') {
            return $this->completeShopwareVersions($context->getCurrentWord());
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function completeArgumentValues($argumentName, CompletionContext $context)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::addConfigureShopwareVersion();
        parent::addConfigureAuth();
        parent::addConfigureHostname();

        $this
            ->setName('sw:store:list')
            ->setDescription('List licensed plugins.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $version = $this->setupShopwareVersion($input);
        $token = $this->setupAuth($input, $output);
        $domain = $this->setupDomain($input, $output);

        $context = new LicenceRequest(
            '',
            $version,
            $domain,
            $token
        );

        try {
            $licences = $this->container->get('shopware_plugininstaller.plugin_service_store_production')
                ->getLicences($context);
        } catch (\Exception $e) {
            $this->handleError([
                'message' => $e->getMessage(),
            ]);

            return null;
        }

        $result = null;

        /** @var LicenceStruct $licence */
        foreach ($licences as $licence) {
            $result[] = [
                'technicalName' => $licence->getTechnicalName(),
                'label' => $licence->getLabel(),
                'domain' => $licence->getShop(),
                'createDate' => $licence->getCreationDate()->format('Y-m-d'),
                'type' => $licence->getPriceModel()->getType(),
            ];
        }

        $table = new Table($output);
        $table->setHeaders(['Technical name', 'Description', 'domain', 'Creation date', 'Type'])
              ->setRows($result);

        $table->render();
    }
}
