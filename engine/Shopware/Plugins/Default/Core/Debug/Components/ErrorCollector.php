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

/**
 * @category  Shopware
 * @package   Shopware\Plugin\Debug\Components
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ErrorCollector implements CollectorInterface
{
    /**
     * @var \Shopware_Plugins_Core_ErrorHandler_Bootstrap
     */
    protected $errorHandler;
    /**
     * @var Utils
     */
    private $utils;

    /**
     * @param \Shopware_Plugins_Core_ErrorHandler_Bootstrap $handler
     * @param Utils $utils
     */
    public function __construct(\Shopware_Plugins_Core_ErrorHandler_Bootstrap $handler, Utils $utils)
    {
        $this->errorHandler = $handler;
        $this->utils = $utils;
    }

    /**
     * @return void
     */
    public function start()
    {
        $this->errorHandler->setEnabledLog(true);
        $this->errorHandler->registerErrorHandler(E_ALL | E_STRICT);
    }

    /**
     * @param Logger $log
     * @return mixed
     */
    public function logResults(Logger $log)
    {
        $errors = $this->errorHandler->getErrorLog();
        if (empty($errors)) {
            return;
        }

        $counts = array();
        foreach ($errors as $errorKey => $error) {
            $counts[$errorKey] = $error['count'];
        }
        array_multisort($counts, SORT_NUMERIC, SORT_DESC, $errors);

        $rows = array();
        foreach ($errors as $error) {
            if (!$rows) {
                $rows[] = array_keys($error);
            }
            $rows[] = $this->utils->encode(array_values($error));
        }
        $table = array('Error Log (' . count($errors) . ')', $rows);

        $log->table($table);
    }
}
