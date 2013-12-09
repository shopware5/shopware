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
 * Log extension to support various writer.
 *
 * The log extension sets the log resource available.
 * It supports various writer, for example firebug, database tables and log files.
 * In additionally the Enlight_Extensions_Log_Bootstrap support to log the ip and the user agents.
 *
 */
class Shopware_Plugins_Core_Log_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * @var Zend_Wildfire_Channel_HttpHeaders Contains an instance of the Zend_Wildfire_Channel_HttpHeaders
     */
    protected $channel;

    /**
     * @var Enlight_Components_Log Contains an instance of the Enlight_Components_Log.
     */
    protected $log;

    /**
     * Installs the log extension plugin.
     * Subscribes the init resource event to initial the log resource,
     * the Enlight_Controller_Front_RouteStartup event to startup the routing process and
     * the Enlight_Controller_Front_DispatchLoopShutdown event to flush the wildfire channel.
     *
     * @return bool
     */
    public function install()
    {
        $this->subscribeEvent(
            'Enlight_Bootstrap_InitResource_Log',
            'onInitResourceLog'
        );
        $this->subscribeEvent(
            'Enlight_Controller_Front_RouteStartup',
            'onRouteStartup'
        );
        $this->subscribeEvent(
            'Enlight_Controller_Front_DispatchLoopShutdown',
            'onDispatchLoopShutdown',
            500
        );

        $form = $this->Form();
        $parent = $this->Forms()->findOneBy(array('name' => 'Core'));
        $form->setParent($parent);
        $form->setElement('checkbox', 'logDb', array('label'=>'Fehler in Datenbank schreiben', 'value'=>1));
        $form->setElement('checkbox', 'logMail', array('label'=>'Fehler an Shopbetreiber senden', 'value'=>0));

        return true;
    }

    /**
     * Sets the given Zend_Log object into the internal log property.
     * If no log given, a new instance with the internal configuration will be created.
     * @param Enlight_Components_Log|Zend_Log $log
     */
    public function setResource(Zend_Log $log = null)
    {
        if ($log === null) {
            $log = new Enlight_Components_Log();
            $log->setEventItem('date', Zend_Date::now());
            $log->addWriter(new Zend_Log_Writer_Null());
            $config = $this->Config();
            if(!empty($config->logDb)) {
                $writer = Zend_Log_Writer_Db::factory(array(
                    'db' => Shopware()->Db(),
                    'table' => 's_core_log',
                    'columnmap' => array(
                        'type' => 'priority',
                        'key' => 'priorityName',
                        'text' => 'message',
                        'date' => 'date',
                        'ip_address' => 'remote_address',
                        'user_agent' => 'user_agent',
                    )
                ));
                $writer->addFilter(Enlight_Components_Log::WARN);
                $log->addWriter($writer);
            }
            if(!empty($config->logMail)) {
                $mail = new Enlight_Components_Mail();
                $mail->addTo(Shopware()->Config()->Mail);
                $writer = new Zend_Log_Writer_Mail($mail);
                $writer->setSubjectPrependText('Fehler im Shop "'.Shopware()->Config()->Shopname.'" aufgetreten!');
                $writer->addFilter(Enlight_Components_Log::WARN);
                $log->addWriter($writer);
            }
        }
        $this->log = $log;
    }

    /**
     * Setter method for the channel property. If no channel given
     * a new instance of the Zend_Wildfire_Channel_HttpHeaders will be used.
     *
     * @param Zend_Wildfire_Channel_HttpHeaders $channel
     */
    public function setFirebugChannel($channel = null)
    {
        if ($channel === null) {
            $channel = Zend_Wildfire_Channel_HttpHeaders::getInstance();
        }
        $this->channel = $channel;
    }

    /**
     * Getter method for the log property which contains an instance of the Enlight_Components_Log.
     * @return Enlight_Components_Log
     */
    public function Resource()
    {
        if ($this->log === null) {
            $this->setResource();
        }
        return $this->log;
    }

    /**
     * Getter method of the channel property. If the channel isn't instantiated
     * a new instance of the Zend_Wildfire_Channel_HttpHeaders will be initial.
     * @return Zend_Wildfire_Channel_HttpHeaders
     */
    public function FirebugChannel()
    {
        if ($this->channel === null) {
            $this->setFirebugChannel();
        }
        return $this->channel;
    }

    /**
     * Resource handler for log plugin
     *
     * @param   Enlight_Event_EventArgs $args
     * @return  Enlight_Components_Log
     */
    public function onInitResourceLog(Enlight_Event_EventArgs $args)
    {
        return $this->Resource();
    }

    /**
     * Listener method for the Enlight_Controller_Front_RouteStartup event.
     * Adds the user-agent and the remote-address to the log component.
     * Sets the request and the response object into the Zend_Wildfire_Channel_HttpHeaders.
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onRouteStartup(Enlight_Controller_EventArgs $args)
    {
        $request = $args->getRequest();
        $response = $args->getResponse();

        /** @var $log Zend_Log */
        $log = $this->Resource();

        $log->setEventItem('remote_address', $request->getClientIp(false));
        $log->setEventItem('user_agent', $request->getHeader('USER_AGENT'));

        $channel = $this->FirebugChannel();
        $channel->setRequest($request);
        $channel->setResponse($response);
    }

    /**
     * Listener method for the Enlight_Controller_Front_DispatchLoopShutdown event.
     * On Dispatch Shutdown collect sql performance results and dump to log component.
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onDispatchLoopShutdown(Enlight_Event_EventArgs $args)
    {
        if ($this->channel !== null) {
            $this->channel->flush();
        }
    }
}
