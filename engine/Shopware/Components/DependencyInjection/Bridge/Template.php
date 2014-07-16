<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

/**
 * @category  Shopware
 * @package   Shopware\Components\DependencyInjection\Bridge
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Template
{
    /**
     * @param \Enlight_Event_EventManager           $eventManager
     * @param \Shopware_Components_Snippet_Manager  $snippetManager
     * @param array                                 $options
     * @return \Enlight_Template_Manager
     */
    public function factory(
        \Enlight_Event_EventManager $eventManager,
        \Shopware_Components_Snippet_Manager $snippetManager,
        array $options
    ) {

        /** @var $template \Enlight_Template_Manager */
        $template = \Enlight_Class::Instance('Enlight_Template_Manager');

        $template->setCompileDir(Shopware()->AppPath('Cache_Compiles'));
        $template->setCacheDir(Shopware()->AppPath('Cache_Templates'));
        $template->setTemplateDir(Shopware()->AppPath('Views'));

        $template->setOptions($options);
        $template->setEventManager($eventManager);

        $template->setTemplateDir(array(
            'custom'      => '_local',
            'local'       => '_local',
            'emotion'     => '_default',
            'default'     => '_default',
            'base'        => 'templates',
            'include_dir' => '.',
        ));

        $resource = new \Enlight_Components_Snippet_Resource($snippetManager);
        $template->registerResource('snippet', $resource);
        $template->setDefaultResourceType('snippet');

        return $template;
    }
}
