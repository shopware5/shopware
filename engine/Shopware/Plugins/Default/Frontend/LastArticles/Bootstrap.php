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

use Shopware\Models\Config\Element;

/**
 * Shopware LastArticles Plugin
 */
class Shopware_Plugins_Frontend_LastArticles_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Install plugin method
     *
     * @return bool
     */
    public function install()
    {
        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatchSecure_Frontend',
            'onPostDispatch'
        );

        $this->createConfigForm();

        return true;
    }

    /**
     * create the configuration for the last viewed products
     */
    private function createConfigForm()
    {
        $form = $this->Form();
        /** @var \Shopware\Models\Config\Form $parent */
        $parent = $this->Forms()->findOneBy(['name' => 'Frontend']);
        $form->setParent($parent);

        $form->setElement(
            'checkbox',
            'show',
            [
                'label' => 'Artikelverlauf anzeigen',
                'value' => true,
                'scope' => Element::SCOPE_SHOP
            ]
        );

        $form->setElement(
            'text',
            'controller',
            [
                'label' => 'Controller-Auswahl',
                'value' => 'index, listing, detail, custom, newsletter, sitemap, campaign',
                'scope' => Element::SCOPE_SHOP
            ]
        );

        $form->setElement(
            'number',
            'lastarticlestoshow',
            [
                'label' => 'Anzahl Artikel in Verlauf (zuletzt angeschaut)',
                'value' => 5,
                'scope' => Element::SCOPE_SHOP
            ]
        );

        $form->setElement(
            'number',
            'time',
            [
                'label' => 'Speicherfrist in Tagen',
                'value' => 15,
                'scope' => Element::SCOPE_SHOP
            ]
        );

        $this->addFormTranslations([
           'en_GB' => [
               'plugin_form' => [
                   'label' => 'Recently viewed items'
               ],
               'show' => [
                   'label' => 'Display recently viewed items'
               ],
               'controller' => [
                   'label' => 'Controller selection'
               ],
               'lastarticlestoshow' => [
                   'label' => 'Maximum number of items to display'
               ],
               'time' => [
                   'label' => 'Storage period in days'
               ]
           ]
       ]);
    }

    /**
     * @return array
     */
    public function getInfo()
    {
        return [
            'label' => 'Artikelverlauf'
        ];
    }

    /**
     * Event listener method
     *
     * Read the last article in defined controllers
     * Saves the last article in detail controller
     *
     * @param Enlight_Controller_ActionEventArgs $args
     */
    public function onPostDispatch(Enlight_Controller_ActionEventArgs $args)
    {
        $request = $args->getSubject()->Request();
        $view = $args->getSubject()->View();
        $config = $this->Config();

        if (rand(0, 100) === 0) {
            $time = $config->get('time') > 0 ? (int) $config->get('time') : 15;
            $sql = 'DELETE FROM s_emarketing_lastarticles
                    WHERE time < DATE_SUB(CONCAT(CURDATE(), ?), INTERVAL ? DAY);';
            $this->get('db')->query($sql, [' 00:00:00', $time]);

            $this->get('events')->notify('Shopware_Plugins_LastArticles_ResetLastArticles', [
                'subject' => $this
            ]);
        }

        if (empty($config->show)) {
            return;
        }

        if (!empty($config->controller) && strpos($config->controller, $request->getControllerName()) === false) {
            return;
        }

        $view->assign('sLastArticlesShow', true);
    }

    /**
     * Creates a new s_emarketing_lastarticles entry for the passed article id.
     *
     * @param int $articleId
     */
    public function setLastArticleById($articleId)
    {
        /** @var \Enlight_Components_Session_Namespace $session */
        $session = $this->get('session');
        $session->offsetSet('sLastArticle', $articleId);
        $sessionId = $session->get('sessionId');
        $articleName = (string) Shopware()->Modules()->Articles()->sGetArticleNameByArticleId($articleId);

        if (!$sessionId || !$articleName || !$articleId) {
            return;
        }

        $this->get('events')->notify('Shopware_Modules_Articles_Before_SetLastArticle', [
            'subject' => Shopware()->Modules()->Articles(),
            'article' => $articleId
        ]);

        $sql = 'INSERT INTO s_emarketing_lastarticles
                    (name, articleID, sessionID, time, userID, shopID)
                VALUES
                    (?, ?, ?, NOW(), ?, ?)
                ON DUPLICATE KEY UPDATE time=NOW(), userID=VALUES(userID);';
        $this->get('db')->query(
            $sql,
            [
                $articleName,
                $articleId,
                $sessionId,
                (int) $session->get('sUserId'),
                (int) $this->get('shop')->getId()
            ]
        );
    }
}
