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

namespace Shopware\Components\DependencyInjection\Bridge;

use Shopware\Components\Escaper\EscaperInterface;
use Shopware\Components\Template\Security;

class Template
{
    /**
     * @return \Enlight_Template_Manager
     */
    public function factory(
        \Enlight_Event_EventManager $eventManager,
        \Enlight_Components_Snippet_Resource $snippetResource,
        EscaperInterface $escaper,
        array $templateConfig,
        array $securityConfig,
        array $backendOptions
    ) {
        /** @var \Enlight_Template_Manager $template */
        $template = \Enlight_Class::Instance('Enlight_Template_Manager', [null, $backendOptions]);

        $template->enableSecurity(
            new Security($template, $securityConfig)
        );

        $template->setOptions($templateConfig);
        $template->setEventManager($eventManager);

        $template->registerResource('snippet', $snippetResource);
        $template->setDefaultResourceType('snippet');

        $template->registerPlugin(\Smarty::PLUGIN_MODIFIER, 'escapeHtml', [$escaper, 'escapeHtml']);
        $template->registerPlugin(\Smarty::PLUGIN_MODIFIER, 'escapeHtmlAttr', [$escaper, 'escapeHtmlAttr']);
        $template->registerPlugin(\Smarty::PLUGIN_MODIFIER, 'escapeJs', [$escaper, 'escapeJs']);
        $template->registerPlugin(\Smarty::PLUGIN_MODIFIER, 'escapeCss', [$escaper, 'escapeCss']);
        $template->registerPlugin(\Smarty::PLUGIN_MODIFIER, 'escapeUrl', [$escaper, 'escapeUrl']);

        return $template;
    }
}
