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

namespace Shopware\Plugin\Debug\Components;

use Shopware\Components\Logger;

class ExceptionCollector implements CollectorInterface
{
    /**
     * @var \Enlight_Event_EventManager
     */
    protected $eventManager;

    /**
     * @var \Exception[]
     */
    protected $exceptions = [];

    /**
     * @var Utils
     */
    private $utils;

    public function __construct(\Enlight_Event_EventManager $eventManager, Utils $utils)
    {
        $this->eventManager = $eventManager;
        $this->utils = $utils;
    }

    public function start()
    {
        $this->eventManager->addListener(
            'Enlight_Controller_Front_PostDispatch',
            [$this, 'onPostDispatch']
        );
    }

    public function onPostDispatch(\Enlight_Controller_EventArgs $args)
    {
        $exceptions = $args->getResponse()->getException();
        if (empty($exceptions)) {
            return;
        }

        foreach ($exceptions as $exception) {
            $this->exceptions[] = $exception;
        }
    }

    public function logResults(Logger $log)
    {
        if (empty($this->exceptions)) {
            return;
        }

        $rows = [['code', 'name', 'message', 'line', 'file', 'trace']];

        foreach ($this->exceptions as $exception) {
            $rows[] = $this->utils->encode([
                $exception->getCode(),
                get_class($exception),
                $exception->getMessage(),
                $exception->getLine(),
                $exception->getFile(),
                explode("\n", $exception->getTraceAsString()),
            ]);
        }

        $table = ['Exception Log (' . count($this->exceptions) . ')', $rows];
        $log->table($table);

        foreach ($this->exceptions as $exception) {
            $log->error((string) $exception);
        }
    }
}
