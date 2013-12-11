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
class Shopware_Plugins_Frontend_Facebook_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    public function install()
    {
        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Frontend_Detail',
            'onPostDispatchDetail'
        );
        $form = $this->Form();

        $form->setElement('checkbox', 'show', array('label' => 'Facebook zeigen', 'value' => 1, 'scope' => Shopware_Components_Form::SCOPE_SHOP));
        $form->setElement('text', 'app_id', array('label' => 'Facebook App-ID', 'value' => '', 'scope' => Shopware_Components_Form::SCOPE_SHOP));

        return true;
    }

    public function onPostDispatchDetail(Enlight_Event_EventArgs $args)
    {
        $view = $args->getSubject()->View();
        $config = $this->Config();

        if (empty($config->show) && $config->show !== null) {
            return;
        }

        $view->app_id = $config->app_id;

        if (!empty($_SERVER["HTTP_USER_AGENT"]) && preg_match("/MSIE 6/", $_SERVER['HTTP_USER_AGENT'])) {
            $view->hideFacebook = true;
        } else {
            $view->hideFacebook = false;
        }

        $article = $view->sArticle;
        $view->unique_id = Shopware()->Shop()->getId() . '_' . $article['articleID'];
        $view->extendsTemplate('frontend/plugins/facebook/blocks_detail.tpl');
    }
}
