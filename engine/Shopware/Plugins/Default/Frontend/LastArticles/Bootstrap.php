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
            'Enlight_Controller_Action_PostDispatch',
            'onPostDispatch'
        );
        $form = $this->Form();
        $parent = $this->Forms()->findOneBy(['name' => 'Frontend']);
        $form->setParent($parent);
        $form->setElement('checkbox', 'show', [
            'label' => 'Artikelverlauf anzeigen',
            'value' => true,
            'scope' => Shopware\Models\Config\Element::SCOPE_SHOP,
        ]);
        $form->setElement('text', 'controller', [
            'label' => 'Controller-Auswahl',
            'value' => 'index, listing, detail, custom, newsletter, sitemap, campaign',
            'scope' => Shopware\Models\Config\Element::SCOPE_SHOP,
        ]);
        $form->setElement('number', 'thumb', [
            'label' => 'Vorschaubild-Größe',
            'value' => 2,
            'scope' => Shopware\Models\Config\Element::SCOPE_SHOP,
        ]);
        $form->setElement('number', 'lastarticlestoshow', [
            'label' => 'Anzahl Artikel in Verlauf (zuletzt angeschaut)',
            'value' => 5,
        ]);
        $form->setElement('number', 'time', [
            'label' => 'Speicherfrist in Tagen',
            'value' => 15,
        ]);

        $this->addFormTranslations(
            [
                'en_GB' => [
                    'plugin_form' => [
                        'label' => 'Recently viewed items',
                    ],
                    'show' => [
                        'label' => 'Display recently viewed items',
                    ],
                    'controller' => [
                        'label' => 'Controller selection',
                    ],
                    'thumb' => [
                        'label' => 'Thumbnail size',
                        'description' => 'Index of the thumbnail size of the associated album to use. Starts at 0',
                    ],
                    'lastarticlestoshow' => [
                        'label' => 'Maximum number of items to display',
                    ],
                    'time' => [
                        'label' => 'Storage period in days',
                    ],
                ],
            ]
        );

        return true;
    }

    /**
     * @return array
     */
    public function getInfo()
    {
        return [
            'label' => 'Artikelverlauf',
        ];
    }

    /**
     * Event listener method
     *
     * Read the last article in defined controllers
     * Saves the last article in detail controller
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onPostDispatch(Enlight_Event_EventArgs $args)
    {
        $request = $args->getSubject()->Request();
        $response = $args->getSubject()->Response();
        $view = $args->getSubject()->View();

        if (!$request->isDispatched()
            || $response->isException()
            || $request->getModuleName() != 'frontend'
            //|| !empty(Shopware()->Session()->Bot)
            || !$view->hasTemplate()
        ) {
            return;
        }

        $config = $this->Config();

        if (rand(0, 100) === 0) {
            $time = $config->time > 0 ? (int) $config->time : 15;
            $sql = '
                DELETE FROM s_emarketing_lastarticles
                WHERE time < DATE_SUB(CONCAT(CURDATE(), ?), INTERVAL ? DAY)
            ';
            Shopware()->Db()->query($sql, [' 00:00:00', $time]);

            Shopware()->Events()->notify('Shopware_Plugins_LastArticles_ResetLastArticles', [
                'subject' => $this,
            ]);
        }

        if (empty($config->show)) {
            return;
        }
        if (!empty($config->controller)
            && strpos($config->controller, $request->getControllerName()) === false
        ) {
            return;
        }

        $view->assign('sLastArticlesShow', true);
    }

    /**
     * Creates a new s_emarketing_lastarticles entry for the passed article id.
     *
     * @param int $articleId
     *
     * @return \Zend_Db_Statement_Pdo
     */
    public function setLastArticleById($articleId)
    {
        $article = $this->getArticleData((int) $articleId);

        Shopware()->Session()->sLastArticle = $articleId;
        $sessionId = Shopware()->Session()->get('sessionId');

        if (empty($sessionId) || empty($article['articleName']) || empty($articleId)) {
            return;
        }

        Shopware()->Events()->notify('Shopware_Modules_Articles_Before_SetLastArticle', [
            'subject' => Shopware()->Modules()->Articles(),
            'article' => $articleId,
        ]);

        return Shopware()->Db()->query('
            INSERT INTO s_emarketing_lastarticles
                (img, name, articleID, sessionID, time, userID, shopID)
            VALUES
                (?, ?, ?, ?, NOW(), ?, ?)
            ON DUPLICATE KEY UPDATE time=NOW(), userID=VALUES(userID)
        ', [
            (string) $article['image'],
            (string) $article['articleName'],
            $articleId,
            $sessionId,
            (int) Shopware()->Session()->sUserId,
            (int) Shopware()->Shop()->getId(),
        ]);
    }

    /**
     * Helper function which returns a data array with the article id,
     * name and preview image.
     *
     * @param $id
     *
     * @return array
     */
    protected function getArticleData($id)
    {
        $images = Shopware()->Modules()->Articles()->sGetArticlePictures($id, true);

        $size = $this->Config()->thumb;
        if (!$size) {
            $size = Shopware()->Config()->lastArticlesThumb;
        }
        $image = null;

        if (isset($images['src'][$size])) {
            $image = $images['src'][$size];
        }

        return [
            'articleID' => $id,
            'image' => $image,
            'articleName' => Shopware()->Modules()->Articles()->sGetArticleNameByArticleId($id),
        ];
    }
}
