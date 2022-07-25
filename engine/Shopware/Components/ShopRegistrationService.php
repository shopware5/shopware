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

namespace Shopware\Components;

use Enlight_Plugin_PluginManager;
use Enlight_Template_Manager;
use RuntimeException;
use Shopware\Components\Theme\Inheritance;
use Shopware\Models\Shop\Shop;
use Shopware\Models\Shop\Template;
use Shopware_Components_Config;
use Shopware_Components_Plugin_Namespace;
use Shopware_Components_Snippet_Manager;
use Shopware_Components_TemplateMail;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Zend_Currency;
use Zend_Locale;

class ShopRegistrationService implements ShopRegistrationServiceInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function registerShop(Shop $shop): void
    {
        $this->registerResources($shop);
        $this->resetTemplate($shop);
    }

    public function registerResources(Shop $shop): void
    {
        $this->container->set('shop', $shop);

        $locale = $this->container->get(Zend_Locale::class);
        $locale->setLocale($shop->getLocale()->toString());

        $currency = $this->container->get(Zend_Currency::class);
        $currency->setLocale($locale);
        $currency->setFormat($shop->getCurrency()->toArray());

        $config = $this->container->get(Shopware_Components_Config::class);
        $config->setShop($shop);

        $snippets = $this->container->get(Shopware_Components_Snippet_Manager::class);
        $snippets->setShop($shop);

        $plugins = $this->container->get(Enlight_Plugin_PluginManager::class);

        foreach ($plugins as $pluginNamespace) {
            if ($pluginNamespace instanceof Shopware_Components_Plugin_Namespace) {
                $pluginNamespace->setShop($shop);
            }
        }

        // Initializes the frontend session to prevent output before session started.
        $this->container->get('session');

        $templateMail = $this->container->get(Shopware_Components_TemplateMail::class);
        $templateMail->setShop($shop);

        // Reset mail transport to have right configuration
        if ($this->container->initialized('mailtransport')) {
            $this->container->reset('mailtransport');
            $this->container->load('mailtransport');
        }
    }

    public function resetTemplate(Shop $shop): void
    {
        $template = $shop->getTemplate();
        if (!$template instanceof Template) {
            return;
        }

        $localeName = $shop->getLocale()->toString();

        if ($template->getVersion() === 3) {
            $this->registerTheme($template);
        } else {
            throw new RuntimeException(sprintf('Tried to load unsupported template version %s for template: %s', $template->getVersion(), $template->getName()));
        }

        $this->container->get(Enlight_Template_Manager::class)->setCompileId(
            sprintf('frontend_%s_%s_%s', $template->toString(), $localeName, $shop->getId())
        );
    }

    private function registerTheme(Template $template): void
    {
        $path = $this->container->get(Inheritance::class)->getTemplateDirectories($template);
        $this->container->get(Enlight_Template_Manager::class)->setTemplateDir($path);
    }
}
