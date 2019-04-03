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

use Shopware\Components\CSRFWhitelistAware;
use Shopware\Models\Log\Log;

class Shopware_Controllers_Backend_Log extends Shopware_Controllers_Backend_ExtJs implements CSRFWhitelistAware
{
    /**
     * Sets the ACL-rights for the log-module
     */
    public function initAcl()
    {
        $this->addAclPermission('getLogs', 'read', 'You\'re not allowed to see the logs.');
        $this->addAclPermission('deleteLogs', 'delete', 'You\'re not allowed to delete the logs.');
        $this->addAclPermission('downloadLogFile', 'system', 'You\'re not allowed to see the system logs.');
        $this->addAclPermission('getLogFileList', 'system', 'You\'re not allowed to see the system logs.');
        $this->addAclPermission('getLogList', 'system', 'You\'re not allowed to see the system logs.');
    }

    /**
     * {@inheritdoc}
     */
    public function getWhitelistedCSRFActions()
    {
        return [
            'downloadLogFile',
        ];
    }

    /**
     * Disable template engine for some actions
     */
    public function preDispatch()
    {
        if ($this->Request()->getActionName() === 'downloadLogFile') {
            $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        } elseif (!in_array($this->Request()->getActionName(), ['index', 'load'])) {
            $this->Front()->Plugins()->Json()->setRenderer(true);
        }
    }

    /**
     * This function is called, when the user opens the log-module.
     * It reads the logs from s_core_log
     * Additionally it sets a filterValue
     */
    public function getLogsAction()
    {
        $start = $this->Request()->get('start');
        $limit = $this->Request()->get('limit');

        // Order data
        $order = (array) $this->Request()->getParam('sort', []);

        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(
            'log.id as id',
            'log.type as type',
            'log.key as key',
            'log.text as text',
            'log.date as date',
            'log.user as user',
            'log.ipAddress as ip_address',
            'log.userAgent as user_agent',
            'log.value4 as value4'
        )->from(Log::class, 'log');

        if ($filter = $this->Request()->get('filter')) {
            $filter = $filter[0];

            $builder->where('log.user LIKE ?1')
                ->orWhere('log.text LIKE ?1')
                ->orWhere('log.date LIKE ?1')
                ->orWhere('log.ipAddress LIKE ?1')
                ->orWhere('log.key LIKE ?1')
                ->orWhere('log.type LIKE ?1');

            $builder->setParameter(1, '%' . $filter['value'] . '%');
        }
        $builder->addOrderBy($order);

        $builder->setFirstResult($start)->setMaxResults($limit);

        $result = $builder->getQuery()->getArrayResult();
        $total = Shopware()->Models()->getQueryCount($builder->getQuery());

        $this->View()->assign(['success' => true, 'data' => $result, 'total' => $total]);
    }

    /**
     * This function is called when the user wants to delete a log.
     * It only handles the deletion.
     */
    public function deleteLogsAction()
    {
        try {
            $params = $this->Request()->getParams();
            unset($params['module'], $params['controller'], $params['action'], $params['_dc']);

            if ($params[0]) {
                foreach ($params as $values) {
                    $logModel = Shopware()->Models()->find(Log::class, $values['id']);

                    Shopware()->Models()->remove($logModel);
                    Shopware()->Models()->flush();
                }
            } else {
                $logModel = Shopware()->Models()->find(Log::class, $params['id']);

                Shopware()->Models()->remove($logModel);
                Shopware()->Models()->flush();
            }
            $this->View()->assign(['success' => true, 'data' => $params]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'errorMsg' => $e->getMessage()]);
        }
    }

    /**
     * This logging method has been moved to \Shopware\Controllers\Backend\Logger::createLogAction
     *
     * @deprecated in Shopware 5.6, to be removed in 5.7. Use \Shopware\Controllers\Backend\Logger::createLogAction instead
     */
    public function createLogAction()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be removed in 5.7. Use \Shopware\Controllers\Backend\Logger::createLogAction instead.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $this->forward('createLog', 'logger', 'backend');
    }

    public function downloadLogFileAction()
    {
        $logDir = $this->get('kernel')->getLogDir();
        $files = $this->getLogFiles($logDir);

        $logFile = $this->Request()->getParam('logFile');
        $logFile = $this->getLogFile($files, $logFile);

        if (!$logFile) {
            throw new RuntimeException('Log file not found.');
        }

        $logFilePath = $logDir . '/' . $this->getLogFile($files, $logFile);

        $response = $this->Response();
        $response->headers->set('cache-control', 'public', true);
        $response->headers->set('content-type', 'application/octet-stream');
        $response->headers->set('content-description', 'File Transfer');
        $response->headers->set('content-disposition', 'attachment; filename=' . $logFile);
        $response->headers->set('content-transfer-encoding', 'binary');
        $response->headers->set('content-length', (string) filesize($logFilePath));
        $response->sendHeaders();

        $this->Front()->Plugins()->ViewRenderer()->setNoRender();

        $out = fopen('php://output', 'wb');
        $file = fopen($logFilePath, 'rb');

        stream_copy_to_stream($file, $out);
    }

    public function getLogFileListAction()
    {
        $logDir = $this->get('kernel')->getLogDir();
        $files = $this->getLogFiles($logDir);
        $defaultFile = $this->getDefaultLogFile($files);

        $files = array_map(function ($file) use ($defaultFile) {
            return [
                'name' => $file[0],
                'channel' => $file['channel'],
                'environment' => $file['environment'],
                'date' => $file['date'],
                'default' => $file[0] == $defaultFile,
            ];
        }, $files);

        $start = $this->Request()->getParam('start', 0);
        $limit = $this->Request()->getParam('limit', 100);

        $count = count($files);
        $files = array_slice($files, $start, $limit);

        $this->View()->assign([
            'success' => true,
            'data' => $files,
            'total' => $count,
        ]);
    }

    public function getLogListAction()
    {
        $logDir = $this->get('kernel')->getLogDir();
        $files = $this->getLogFiles($logDir);

        $logFile = $this->Request()->getParam('logFile');
        $logFile = $this->getLogFile($files, $logFile);

        if (!$logFile) {
            $this->View()->assign([
                'success' => true,
                'data' => [],
                'count' => 0,
            ]);

            return;
        }

        $file = $logDir . '/' . $logFile;
        $start = $this->Request()->getParam('start', 0);
        $limit = $this->Request()->getParam('limit', 100);
        $sort = $this->Request()->getParam('sort');

        $reverse = false;
        if (!isset($sort[0]['direction']) || $sort[0]['direction'] === 'DESC') {
            $reverse = true;
        }

        /** @var \Shopware\Components\Log\Parser\LogfileParser $reader */
        $reader = $this->get('shopware.log.fileparser');

        $data = $reader->parseLogFile(
            $file,
            $start,
            $limit,
            $reverse
        );
        $count = $reader->countLogFile($file);

        $this->View()->assign([
            'success' => true,
            'data' => $data,
            'count' => $count,
        ]);
    }

    /**
     * Returns an array of all log files in the given directory.
     *
     * @param string $logDir
     *
     * @return array
     */
    private function getLogFiles($logDir)
    {
        $finder = new Symfony\Component\Finder\Finder();
        $finder->files()->name('*.log')->in($logDir);

        $matches = [];
        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($finder as $file) {
            $name = $file->getBasename();
            if (preg_match('/^(?P<channel>[^_]+)_(?P<environment>[^-]+)\-(?P<date>[0-9-]+)\.log$/', $name, $match)) {
                $matches[implode('-', [$match['date'], $match['environment'], $match['channel']])] = $match;
            }
        }

        krsort($matches);

        return array_values($matches);
    }

    /**
     * Checks whether the specified log file exists in the log directory. If so, he returns it.
     *
     * @param array  $files
     * @param string $name
     *
     * @return false|string
     */
    private function getLogFile($files, $name)
    {
        foreach ($files as $file) {
            if ($name == $file[0]) {
                return $name;
            }
        }

        return false;
    }

    /**
     * @return false|string
     */
    private function getDefaultLogFile(array $files)
    {
        return isset($files[0]) ? $files[0] : false;
    }
}
