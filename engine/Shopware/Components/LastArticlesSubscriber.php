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

use Enlight\Event\SubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LastArticlesSubscriber implements SubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend' => 'onPostDispatch',
            'Enlight_Controller_Action_PostDispatchSecure_Widgets_Index' => 'onRefreshStatistics',
            'Shopware_Modules_Admin_Regenerate_Session_Id' => 'refreshSessionId',
        ];
    }

    public function refreshSessionId(\Enlight_Event_EventArgs $args)
    {
        $this->container->get('dbal_connection')->executeUpdate(
            'UPDATE s_emarketing_lastarticles SET sessionID = :newId WHERE sessionID = :oldId',
            ['oldId' => $args->get('oldSessionId'), 'newId' => $args->get('newSessionId')]
        );
    }

    public function onRefreshStatistics(\Enlight_Controller_ActionEventArgs $args)
    {
        $request = $args->getRequest();

        if ($request->getActionName() !== 'refreshStatistic') {
            return;
        }

        $productId = (int) $request->getParam('articleId');

        if (empty($productId)) {
            return;
        }

        $this->setLastProductById($productId);
    }

    public function onPostDispatch(\Enlight_Controller_ActionEventArgs $args)
    {
        $this->cleanupLastProducts();
        $config = $this->container->get('config');

        if (empty($config->offsetGet('lastarticles_show'))) {
            return;
        }

        $requestController = $args->getSubject()->Request()->getControllerName();
        $controllerNames = $config->offsetGet('lastarticles_controller');

        if (!empty($controllerNames) && stripos($controllerNames, $requestController) === false) {
            return;
        }

        $args->getSubject()->View()->assign('sLastArticlesShow', true);
    }

    /**
     * Removes entries from s_emarketing_lastarticles which are older than allowed by the configuration
     */
    private function cleanupLastProducts()
    {
        if (Random::getInteger(0, 100) === 0) {
            $time = (int) $this->container->get('config')->get('lastarticles_time', 15);

            $sql = 'DELETE FROM s_emarketing_lastarticles WHERE `time` < DATE_SUB(CONCAT_WS(" ", CURDATE(), ?), INTERVAL ? DAY)';
            $this->container->get('dbal_connection')->executeQuery($sql, ['00:00:00', $time]);

            $this->container->get('events')->notify('Shopware_Plugins_LastArticles_ResetLastArticles', ['subject' => $this]);
        }
    }

    /**
     * Creates a new s_emarketing_lastarticles entry for the passed article id.
     *
     * @param int $productId
     */
    private function setLastProductById($productId)
    {
        $sessionId = $this->container->get('session')->get('sessionId');

        if (empty($sessionId)) {
            return;
        }

        $this->container->get('events')->notify('Shopware_Modules_Articles_Before_SetLastArticle', [
            'subject' => $this,
            'article' => $productId,
        ]);

        $insertSql = 'INSERT INTO s_emarketing_lastarticles (`articleID`, `sessionID`, `time`, `userID`, `shopID`)
            VALUES (:articleId, :sessionId, NOW(), :userId, :shopId)
            ON DUPLICATE KEY UPDATE `time` = NOW(), `userID` = VALUES(userID)';

        $this->container->get('dbal_connection')->executeUpdate(
            $insertSql,
            [
                'articleId' => $productId,
                'sessionId' => $sessionId,
                'userId' => (int) Shopware()->Session()->get('sUserId'),
                'shopId' => $this->container->get('shop')->getId(),
            ]
        );
    }
}
