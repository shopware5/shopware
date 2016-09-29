<?php

namespace Shopware\Components;

use Doctrine\DBAL\Connection;
use Enlight\Event\SubscriberInterface;
use Shopware\Components\DependencyInjection\Container;

class LastArticlesSubscriber implements SubscriberInterface
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend' => 'onPostDispatch',
            'Enlight_Controller_Action_PostDispatchSecure_Widgets_Index' => 'onRefreshStatistics',
            'Shopware_Modules_Admin_Regenerate_Session_Id' => 'refreshSessionId'
        ];
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function refreshSessionId(\Enlight_Event_EventArgs $args)
    {
        $this->container->get('dbal_connection')->executeUpdate(
            'UPDATE s_emarketing_lastarticles SET sessionID = :newId WHERE sessionID = :oldId',
            ['oldId' => $args->get('oldSessionId'), 'newId' => $args->get('newSessionId')]
        );
    }

    /**
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function onRefreshStatistics(\Enlight_Controller_ActionEventArgs $args)
    {
        $request = $args->getRequest();

        if ($request->getActionName() !== 'refreshStatistic') {
            return;
        }

        $articleId = (int) $request->getParam('articleId');

        if (empty($articleId)) {
            return;
        }

        $this->setLastArticleById($articleId);
    }

    /**
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function onPostDispatch(\Enlight_Controller_ActionEventArgs $args)
    {
        $this->cleanupLastArticles();
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
    private function cleanupLastArticles()
    {
        if (rand(0, 100) === 0) {
            $time = (int)$this->container->get('config')->get('lastarticles_time', 15);

            $sql = 'DELETE FROM s_emarketing_lastarticles WHERE `time` < DATE_SUB(CONCAT_WS(" ", CURDATE(), ?), INTERVAL ? DAY)';
            $this->container->get('dbal_connection')->executeQuery($sql, ['00:00:00', $time]);

            $this->container->get('events')->notify('Shopware_Plugins_LastArticles_ResetLastArticles', ['subject' => $this]);
        }
    }

    /**
     * Creates a new s_emarketing_lastarticles entry for the passed article id.
     *
     * @param int $articleId
     */
    private function setLastArticleById($articleId)
    {
        $sessionId = $this->container->get('session')->get('sessionId');

        if (empty($sessionId)) {
            return;
        }

        $this->container->get('events')->notify('Shopware_Modules_Articles_Before_SetLastArticle', [
            'subject' => $this,
            'article' => $articleId
        ]);

        $insertSql = 'INSERT INTO s_emarketing_lastarticles (`articleID`, `sessionID`, `time`, `userID`, `shopID`)
            VALUES (:articleId, :sessionId, NOW(), :userId, :shopId)
            ON DUPLICATE KEY UPDATE `time` = NOW(), `userID` = VALUES(userID)';

        $this->container->get('dbal_connection')->executeUpdate(
            $insertSql,
            [
                'articleId' => $articleId,
                'sessionId' => $sessionId,
                'userId' => (int)Shopware()->Session()->get('sUserId'),
                'shopId' => $this->container->get('shop')->getId()
            ]
        );
    }
}
