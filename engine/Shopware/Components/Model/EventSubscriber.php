<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Components\Model;

use Doctrine\Common\EventSubscriber as BaseEventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Proxy\Proxy;
use Enlight_Event_EventArgs;
use Enlight_Event_EventManager;

/**
 * The Shopware EventSubscriber is an extension of the standard Doctrine EventSubscriber.
 *
 * This subscriber has different event listener functions to trace the LiveCycleEvents of doctrine models and forward them to Enlight Events.
 * Thus it is possible to listen on certain events of specified shopware models.
 *
 * Enlight event listener function can be registered over $this->subscribeEvent();
 * Example:
 *  - Before a new product created (Model: Shopware\Models\Article\Article) we want to call an own function named "beforeAnArticleCreated".
 *
 * - Plugin Solution:
 * $this->subscribeEvent(
 *     $this->createEvent('Shopware\Models\Article\Article::prePersist', 'beforeAnArticleCreated')
 * );
 * </code>
 *
 * - Shopware Core Solution:
 * Shopware()->Subscriber()->subscribeEvent(
 *     new Enlight_Event_EventHandler('Shopware\Models\Article\Article::prePersist', 'beforeAnArticleCreated')
 * );
 */
class EventSubscriber implements BaseEventSubscriber
{
    /**
     * @var Enlight_Event_EventManager
     */
    protected $eventManager;

    /**
     * @param Enlight_Event_EventManager $eventManager
     */
    public function __construct($eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * Specifies the list of events to listen.
     *
     * @return string[]
     */
    public function getSubscribedEvents()
    {
        return [
            'prePersist',
            'preRemove',
            'preUpdate',
            'postPersist',
            'postUpdate',
            'postRemove',
        ];
    }

    /**
     * Event listener function of the preUpdate live cycle event. Fired before an existing model saved.
     *
     * @return Enlight_Event_EventArgs|null
     */
    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        /** @var ModelEntity $entity */
        $entity = $eventArgs->getObject();
        $entityName = $this->getEntityName($entity);

        return $this->notifyEvent($entityName . '::preUpdate', $eventArgs);
    }

    /**
     * Event listener function of the preRemove live cycle event. Fired before an model removed.
     *
     * @return Enlight_Event_EventArgs|null
     */
    public function preRemove(PreRemoveEventArgs $eventArgs)
    {
        /** @var ModelEntity $entity */
        $entity = $eventArgs->getObject();
        $entityName = $this->getEntityName($entity);

        return $this->notifyEvent($entityName . '::preRemove', $eventArgs);
    }

    /**
     * Event listener function of the prePersist live cycle event. Fired before a new model saved.
     *
     * @return Enlight_Event_EventArgs|null
     */
    public function prePersist(PrePersistEventArgs $eventArgs)
    {
        /** @var ModelEntity $entity */
        $entity = $eventArgs->getObject();
        $entityName = $this->getEntityName($entity);

        return $this->notifyEvent($entityName . '::prePersist', $eventArgs);
    }

    /**
     * Event listener function of the postUpdateRemove live cycle event. Fired after an existing model saved.
     *
     * @return Enlight_Event_EventArgs|null
     */
    public function postUpdate(PostUpdateEventArgs $eventArgs)
    {
        /** @var ModelEntity $entity */
        $entity = $eventArgs->getObject();
        $entityName = $this->getEntityName($entity);

        return $this->notifyEvent($entityName . '::postUpdate', $eventArgs);
    }

    /**
     * Event listener function of the postRemove live cycle event. Fired after a model removed.
     *
     * @return Enlight_Event_EventArgs|null
     */
    public function postRemove(PostRemoveEventArgs $eventArgs)
    {
        /** @var ModelEntity $entity */
        $entity = $eventArgs->getObject();
        $entityName = $this->getEntityName($entity);

        return $this->notifyEvent($entityName . '::postRemove', $eventArgs);
    }

    /**
     * Event listener function of the postPersist live cycle event. Fired after a new model saved.
     *
     * @return Enlight_Event_EventArgs|null
     */
    public function postPersist(PostPersistEventArgs $eventArgs)
    {
        /** @var ModelEntity $entity */
        $entity = $eventArgs->getObject();
        $entityName = $this->getEntityName($entity);

        return $this->notifyEvent($entityName . '::postPersist', $eventArgs);
    }

    /**
     * Returns the class name of the passed entity.
     *
     * @param ModelEntity $entity
     *
     * @return class-string
     */
    protected function getEntityName($entity)
    {
        if ($entity instanceof Proxy) {
            $entityName = get_parent_class($entity);
        } else {
            $entityName = \get_class($entity);
        }
        \assert(\is_string($entityName));

        return $entityName;
    }

    /**
     * Notify a lifecycleCallback event of doctrine over the enlight event manager.
     *
     * @param string             $eventName
     * @param LifecycleEventArgs $eventArgs
     *
     * @return Enlight_Event_EventArgs|null
     */
    protected function notifyEvent($eventName, $eventArgs)
    {
        return $this->eventManager->notify($eventName, [
            'entityManager' => $eventArgs->getObjectManager(),
            'entity' => $eventArgs->getObject(),
        ]);
    }
}
