<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Components\DependencyInjection\Bridge;

use Enlight_Class;
use Enlight_Components_Snippet_Resource;
use Enlight_Event_EventManager;
use Enlight_Template_Manager;
use Shopware\Components\Escaper\EscaperInterface;
use Shopware\Components\Template\Security;
use Smarty;

class Template
{
    /**
     * @return Enlight_Template_Manager
     */
    public function factory(
        Enlight_Event_EventManager $eventManager,
        Enlight_Components_Snippet_Resource $snippetResource,
        EscaperInterface $escaper,
        array $templateConfig,
        array $securityConfig,
        array $backendOptions
    ) {
        $template = Enlight_Class::Instance(Enlight_Template_Manager::class, [null, $backendOptions]);
        \assert($template instanceof Enlight_Template_Manager);

        $template->enableSecurity(new Security($template, $securityConfig));

        $template->setOptions($templateConfig);
        $template->setEventManager($eventManager);

        $template->registerResource('snippet', $snippetResource);
        /* @phpstan-ignore-next-line is handled by magic method `\Smarty_Internal_TemplateBase::__call` and will set the property `\Smarty::$default_resource_type` */
        $template->setDefaultResourceType('snippet');

        $template->registerPlugin(Smarty::PLUGIN_MODIFIER, 'escapeHtml', [$escaper, 'escapeHtml']);
        $template->registerPlugin(Smarty::PLUGIN_MODIFIER, 'escapeHtmlAttr', [$escaper, 'escapeHtmlAttr']);
        $template->registerPlugin(Smarty::PLUGIN_MODIFIER, 'escapeJs', [$escaper, 'escapeJs']);
        $template->registerPlugin(Smarty::PLUGIN_MODIFIER, 'escapeCss', [$escaper, 'escapeCss']);
        $template->registerPlugin(Smarty::PLUGIN_MODIFIER, 'escapeUrl', [$escaper, 'escapeUrl']);

        return $template;
    }
}
