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

use Monolog\Handler\HandlerInterface;
use Shopware\Components\Logger;
use Shopware\Plugin\Debug\Components\CollectorInterface;
use Shopware\Plugin\Debug\Components\ControllerCollector;
use Shopware\Plugin\Debug\Components\DatabaseCollector;
use Shopware\Plugin\Debug\Components\DbalCollector;
use Shopware\Plugin\Debug\Components\ErrorCollector;
use Shopware\Plugin\Debug\Components\EventCollector;
use Shopware\Plugin\Debug\Components\ExceptionCollector;
use Shopware\Plugin\Debug\Components\TemplateCollector;
use Shopware\Plugin\Debug\Components\TemplateVarCollector;
use Shopware\Plugin\Debug\Components\Utils;

class Shopware_Plugins_Core_Debug_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var CollectorInterface[]
     */
    protected $collectors = [];

    /**
     * @return bool
     */
    public function install()
    {
        $this->subscribeEvent(
            'Enlight_Controller_Front_StartDispatch',
            'onStartDispatch'
        );

        $form = $this->Form();
        $parent = $this->Forms()->findOneBy(['name' => 'Core']);
        $form->setParent($parent);
        $form->setElement('text', 'AllowIP', ['label' => 'Restrict to IP', 'value' => '']);
        $fields = [
            [
                'name' => 'logTemplateVars',
                'label' => 'Log template vars',
                'default' => true,
            ],
            [
                'name' => 'logErrors',
                'label' => 'Log errors',
                'default' => true,
            ],
            [
                'name' => 'logExceptions',
                'label' => 'Log exceptions',
                'default' => true,
            ],
            [
                'name' => 'logDb',
                'label' => 'Benchmark Zend_Db queries',
            ],
            [
                'name' => 'logModel',
                'label' => 'Benchmark DBAL queries',
            ],
            [
                'name' => 'logTemplate',
                'label' => 'Benchmark template',
            ],
            [
                'name' => 'logController',
                'label' => 'Benchmark controller events',
            ],
            [
                'name' => 'logEvents',
                'label' => 'Benchmark events',
            ],
        ];

        foreach ($fields as $field) {
            $form->setElement('boolean', $field['name'], [
                'label' => $field['label'],
                'value' => (isset($field['default'])) ?: false,
            ]);
        }

        return true;
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        if ($this->logger === null) {
            $this->logger = $this->get('debuglogger');
        }

        return $this->logger;
    }

    public function pushCollector(CollectorInterface $collector)
    {
        $this->collectors[] = $collector;
    }

    /**
     * Registers active collectors
     */
    public function registerCollectors()
    {
        $this->get('loader')->registerNamespace('Shopware\Plugin\Debug', __DIR__ . '/');

        $eventManager = $this->get('events');
        $utils = new Utils();
        $errorHandler = $this->Collection()->get('ErrorHandler');

        if ($this->Config()->get('logTemplateVars')) {
            $this->pushCollector(new TemplateVarCollector($eventManager));
        }

        if ($this->Config()->get('logErrors')) {
            $this->pushCollector(new ErrorCollector($errorHandler, $utils));
        }

        if ($this->Config()->get('logExceptions')) {
            $this->pushCollector(new ExceptionCollector($eventManager, $utils));
        }

        if ($this->Config()->get('logDb')) {
            $this->pushCollector(new DatabaseCollector($this->get('db')));
        }

        if ($this->Config()->get('logModel')) {
            $this->pushCollector(new DbalCollector($this->get('modelconfig')));
        }

        if ($this->Config()->get('logTemplate')) {
            $this->pushCollector(new TemplateCollector($this->get('template'), $utils, $this->get('kernel')->getRootDir()));
        }

        if ($this->Config()->get('logController')) {
            $this->pushCollector(new ControllerCollector($eventManager, $utils));
        }

        if ($this->Config()->get('logEvents')) {
            $this->pushCollector(new EventCollector($eventManager, $utils));
        }

        foreach ($this->collectors as $collector) {
            $collector->start();
        }
    }

    public function onStartDispatch(\Enlight_Event_EventArgs $args)
    {
        /** @var \Enlight_Controller_Request_Request $request */
        $request = $args->getSubject()->Request();

        if (!$this->isRequestAllowed($request)) {
            return;
        }

        $handlers = $this->getHandlers($request);
        if (empty($handlers)) {
            return;
        }

        foreach ($handlers as $handler) {
            $this->getLogger()->pushHandler($handler);
        }

        $this->registerCollectors();

        $this->get('events')->addListener(
            'Enlight_Controller_Front_DispatchLoopShutdown',
            [$this, 'onDispatchLoopShutdown']
        );
    }

    /**
     * @param Enlight_Controller_Request_Request $request
     *
     * @return HandlerInterface[]
     */
    public function getHandlers(\Enlight_Controller_Request_Request $request)
    {
        $handlers = [];
        if ($this->get('monolog.handler.firephp')->acceptsRequest($request)) {
            $handlers[] = $this->get('monolog.handler.firephp');
        }

        return $handlers;
    }

    /**
     * @param Enlight_Controller_Request_Request $request
     *
     * @return bool
     */
    public function isRequestAllowed(\Enlight_Controller_Request_Request $request)
    {
        $clientIp = $request->getClientIp();
        $allowedIp = $this->Config()->get('AllowIP');

        if (empty($allowedIp)) {
            return true;
        }

        if (empty($clientIp)) {
            return false;
        }

        return strpos($allowedIp, $clientIp) !== false;
    }

    /**
     * Listener method of the Enlight_Controller_Front_DispatchLoopShutdown event.
     * On Dispatch Shutdown collects results and dumps to log component.
     */
    public function onDispatchLoopShutdown(\Enlight_Event_EventArgs $args)
    {
        foreach ($this->collectors as $collector) {
            $collector->logResults($this->getLogger());
        }
    }
}
