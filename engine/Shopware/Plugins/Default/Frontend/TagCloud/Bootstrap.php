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

/**
 */
class Shopware_Plugins_Frontend_TagCloud_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    public function install()
    {
        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch',
            'onPostDispatch'
        );

        $this->addForm();
        $this->translateForm();

        return true;
    }

    /**
     * Translates the plugin form
     */
    private function addForm()
    {
        $form = $this->Form();
        $parent = $this->Forms()->findOneBy(array('name' => 'Frontend'));
        $form->setParent($parent);
        $form->setElement('checkbox', 'show', array(
            'label' => 'Tag-Cloud anzeigen',
            'value' => true,
            'scope' => Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        $form->setElement('text', 'controller', array(
            'label' => 'Controller-Auswahl',
            'value' => 'index, listing',
            'scope' => Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        $form->setElement('text', 'tagCloudClass', array(
            'label' => 'Name der Tag-Klasse',
            'value' => 'tag',
            'scope' => Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        $form->setElement('number', 'tagCloudMax', array(
            'label' => 'Maximale Anzahl Begriffe',
            'value' => 46,
            'scope' => Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        $form->setElement('number', 'tagCloudSplit', array(
            'label' => 'Anzahl der Stufen',
            'value' => 3,
            'scope' => Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        $form->setElement('number', 'tagTime', array(
            'label' => 'Die berücksichtigte Zeit in Tagen',
            'value' => 30,
            'scope' => Shopware\Models\Config\Element::SCOPE_SHOP
        ));
    }

    /**
     * Translates the plugin form
     */
    private function translateForm()
    {
        $formTranslation = array(
            'en_GB' => array(
                'plugin_form' => array(
                    'label' => 'Tag cloud'
                ),
                'show' => array(
                    'label' => 'Display tag cloud',
                ),
                'controller' => array(
                    'label' => 'Controller selection'
                ),
                'tagCloudClass' => array(
                    'label' => 'Name of the tag class'
                ),
                'tagCloudMax' => array(
                    'label' => 'Maximum number of terms'
                ),
                'tagCloudSplit' => array(
                    'label' => 'Number of ranks'
                ),
                'tagTime' => array(
                    'label' => 'Time period (in days) considered'
                )
            )
        );

        $this->addFormTranslations($formTranslation);
    }
    /**
     * @return array
     */
    public function getInfo()
    {
        return array(
            'label' => 'Schlagwortwolke'
        );
    }

    /**
     * @param Enlight_Event_EventArgs $args
     * @return mixed
     */
    public function onPostDispatch(Enlight_Event_EventArgs $args)
    {
        $view = $args->getSubject()->View();
        $request = $args->getSubject()->Request();
        $response = $args->getSubject()->Response();

        if (!$request->isDispatched()
            || $response->isException()
            || $request->getModuleName() != 'frontend'
        ) {
            return;
        }

        $config = $this->Config();
        if (empty($config->show)) {
            return;
        }

        if (strpos($config->controller, $request->getControllerName()) !== false) {
            $view->sCloudShow = true;
        }
    }
}
