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

class Shopware_Components_Subscriber extends Enlight_Event_Subscriber
{
    protected $db;

    public function init()
    {
        die("asdf");

        $this->db = Shopware()->Db();
    }
    /**
     * Returns an array of events that this subscriber listens
     *
     * @return array
     */
    public function getListeners()
    {
        $sql = '
            SELECT ce.subscribe as event, ce.listener, ce.position, ce.pluginID, cp.namespace as plugin_namespace, cp.name as plugin_name
            FROM s_core_subscribes ce
            LEFT JOIN s_core_plugins cp
            ON cp.id=ce.pluginID
            WHERE (cp.id IS NULL OR cp.active=1)
            AND ce.type=0
            ORDER BY event, position
         ';
         $rows = $this->db->fetchAll($sql);

         $events = array();

         if(!empty($rows))
         foreach ($rows as $row) {
            $events[] = new Enlight_Event_Handler_Default(
                $row['event'],
                $row['listener'],
                $row['position']
                //$row['pluginID']
            );
         }

         return $events;
    }

    /**
     * Register a listener to an event.
     *
     * @param   Enlight_Event_Handler $handler
     * @return  Enlight_Event_Subscriber
     */
    public function registerListener(Enlight_Event_Handler $handler)
    {
        return $this->saveSubscribe($handler);
    }

    /**
     * Remove an event listener.
     *
     * @param   Enlight_Event_Handler $handler
     * @return  Enlight_Event_Subscriber
     */
    public function removeListener(Enlight_Event_Handler $handler)
    {
        return $this->deleteSubscribe($handler);
    }

    /**
     * Returns an array of events that this subscriber listens
     *
     * @return array
     */
    public function getSubscribedHooks()
    {
         $sql = '
            SELECT ce.subscribe, ce.listener as hook, ce.position, ce.type, ce.pluginID, cp.namespace as plugin_namespace, cp.name as plugin_name
            FROM s_core_subscribes ce
            LEFT JOIN s_core_plugins cp
            ON cp.id=ce.pluginID
            WHERE (cp.id IS NULL OR cp.active=1)
            AND ce.type IN (1,2,3)
            ORDER BY position
         ';
         $rows = $this->db->fetchAll($sql);

         $hooks = array();

         if(!empty($rows))
         foreach ($rows as $row) {
            list($row['class'], $row['method']) = explode('::', $row['subscribe']);

            $hooks[] = new Enlight_Hook_HookHandler(
                $row['class'],
                $row['method'],
                $row['hook'],
                $row['type'],
                $row['position'],
                $row['pluginID']
            );
         }

         return $hooks;
    }

    public function subscribeEvent(Enlight_Event_EventHandler $handler)
    {
        return $this->saveSubscribe($handler);
    }

    public function subscribeHook(Enlight_Hook_HookHandler $handler)
    {
        return $this->saveSubscribe($handler);
    }

    public function unsubscribeEvent(Enlight_Event_EventHandler $handler)
    {
        return $this->deleteSubscribe($handler);
    }

    public function unsubscribeHook(Enlight_Hook_HookHandler $handler)
    {
        return $this->deleteSubscribe($handler);
    }

    public function unsubscribeEvents($where=array())
    {
        $where['type'] = 0;
         return $this->deleteSubscribes($where);
    }

    public function unsubscribeHooks($where=array())
    {
        if (!isset($where['type'])) {
            $where['type'] = array(1,2,3);
        }
        return $this->deleteSubscribes($where);
    }

    protected function deleteSubscribes($values)
    {
        $where = array();
        if (isset($values['type'])) {
            $where[] = $this->db->quoteInto('type IN (?)', $values['type']);
        }
        if (isset($values['listener'])) {
            $where[] = $this->db->quoteInto('listener=?', $values['listener']);
        }
        if (isset($values['position'])) {
            $where[] = $this->db->quoteInto('position=?', $values['position']);
        }
        if (isset($values['pluginID'])) {
            $where[] = $this->db->quoteInto('pluginID=?', $values['pluginID']);
        }
        if (!$where) {
            return false;
        }
        $where = implode(' AND ', $where);
        $sql = 'DELETE FROM `s_core_subscribes` WHERE '.$where;
        $result = $this->db->query($sql);
        return (bool) $result;
    }

    protected function deleteSubscribe($handler)
    {
        switch (get_class($handler)) {
            case 'Enlight_Event_EventHandler':
                $type = 0;
                break;
            case 'Enlight_Hook_HookHandler':
                $type = $handler->getType();
                break;
            default:
                return false;
        }
        $sql = '
            DELETE FROM `s_core_subscribes`
            WHERE `subscribe`=?,
            AND `type`=?,
            AND `listener`=?,
            AND `position`=?,
            AND `pluginID`=?
        ';
        $result = $this->db->query($sql, array(
            $handler->getName(),
            $type,
            $handler->getListener(),
            $handler->getPosition(),
            $handler->getPlugin(),
        ));
        return (bool) $result;
    }

    protected function saveSubscribe($handler)
    {
        switch (get_class($handler)) {
            case 'Enlight_Event_EventHandler':
                $type = 0;
                break;
            case 'Enlight_Hook_HookHandler':
                $type = $handler->getType();
                break;
            default:
                return false;
        }
        $sql = '
            INSERT INTO `s_core_subscribes` (
                `subscribe`,
                `type`,
                `listener`,
                `position`,
                `pluginID`
            ) VALUES (
                ?, ?, ?, ?, ?
            ) ON DUPLICATE KEY UPDATE
                position=VALUES(position),
                pluginID=VALUES(pluginID)
        ';
        $result = $this->db->query($sql, array(
            $handler->getName(),
            $type,
            $handler->getListener(),
            $handler->getPosition(),
            $handler->getPlugin(),
        ));
        return (bool) $result;
    }
}
