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
class Shopware_Plugins_Backend_SwagHtmlCodeWidget_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * @return array
     */
    public function enable()
    {
        return [
            'success' => true,
            'invalidateCache' => ['backend', 'template', 'theme']
        ];
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return '1.0.1';
    }

    /**
     * Returns the human readable name of the plugin
     *
     * @return string
     */
    public function getLabel()
    {
        return 'HTML Code Widget';
    }

    /**
     * Install plugin method
     *
     * @return bool
     */
    public function install()
    {
        $component = $this->createEmotionComponent(array(
            'name' => 'HTML Code Widget',
            'xtype' => 'emotion-html-code',
            'template' => 'component_html_code',
            'cls' => 'emotion-html-code-widget'
        ));

        $component->createCodeMirrorField(array(
            'name' => 'javascript',
            'fieldLabel' => 'JavaScript Code',
            'allowBlank' => true
        ));

        $component->createCodeMirrorField(array(
            'name' => 'smarty',
            'fieldLabel' => 'HTML Code',
            'allowBlank' => true,
            'mode' => 'smarty'
        ));

        return true;
    }
}
