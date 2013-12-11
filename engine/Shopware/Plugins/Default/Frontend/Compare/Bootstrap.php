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

/**
 */
class Shopware_Plugins_Frontend_Compare_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    public function install()
    {
        $this->subscribeEvent('Enlight_Controller_Action_PostDispatch', 'onPostDispatch');

        $form = $this->Form();
        $parent = $this->Forms()->findOneBy(array('name' => 'Frontend'));
        $form->setParent($parent);
        $form->setElement('checkbox', 'show', array(
            'label' => 'Vergleich anzeigen', 'value' => 1,
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        $form->setElement('number', 'maxComparisons', array(
            'label' => 'Maximale Anzahl von zu vergleichenden Artikeln', 'value' => 5,
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));

        return true;
    }

    /**
     * @return array
     */
    public function getInfo()
    {
        return array(
            'label' => 'Artikelvergleich'
        );
    }

    public function onPostDispatch(Enlight_Event_EventArgs $args)
    {
        $request = $args->getSubject()->Request();
        $response = $args->getSubject()->Response();
        $view = $args->getSubject()->View();

        if (!$request->isDispatched() || $response->isException()
            || $request->getModuleName() != 'frontend'
            || !$view->hasTemplate()
        ) {
            return;
        }

        $config = $this->Config();
        if (empty($config->show) && $config->show !== null) {
            return;
        }

        $view->extendsTemplate('frontend/plugins/compare/index.tpl');
        $view->assign('sCompareShow', true);
    }
}
