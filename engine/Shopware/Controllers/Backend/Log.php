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
use Shopware\Components\Log\Reader\ReaderInterface;

/**
 * Shopware Log Controller
 *
 * This controller handles all actions made by the user or the server in the log module or the backend.
 * It reads all logs, creates new ones or deletes them.
 */
class Shopware_Controllers_Backend_Log extends Shopware_Controllers_Backend_ExtJs implements CSRFWhitelistAware
{
    /**
     * Sets the ACL-rights for the log-module
     */
    public function initAcl()
    {
        $this->addAclPermission("getLogs", "read", "You're not allowed to see the logs.");
        $this->addAclPermission("deleteLogs", "delete", "You're not allowed to delete the logs.");
        $this->addAclPermission("downloadLogFile", "system", "You're not allowed to see the system logs.");
        $this->addAclPermission("getLogFileList", "system", "You're not allowed to see the system logs.");
        $this->addAclPermission("getLogList", "system", "You're not allowed to delete the system logs.");
    }

    /**
     * {@inheritdoc}
     */
    public function getWhitelistedCSRFActions()
    {
        return [
            'downloadLogFile'
        ];
    }

    /**
     * Disable template engine for some actions
     *
     * @return void
     */
    public function preDispatch()
    {
        if ($this->Request()->getActionName() == 'downloadLogFile') {
            $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        } elseif (!in_array($this->Request()->getActionName(), array('index', 'load'))) {
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

        //order data
        $order = (array)$this->Request()->getParam('sort', array());

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
        )->from('Shopware\Models\Log\Log', 'log');

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


        $this->View()->assign(array('success' => true, 'data' => $result, 'total' => $total));
    }

    /**
     * This function is called when the user wants to delete a log.
     * It only handles the deletion.
     */
    public function deleteLogsAction()
    {
        try {
            $params = $this->Request()->getParams();
            unset($params['module']);
            unset($params['controller']);
            unset($params['action']);
            unset($params['_dc']);

            if ($params[0]) {
                $data = array();
                foreach ($params as $values) {
                    $logModel = Shopware()->Models()->find('\Shopware\Models\Log\Log', $values['id']);

                    Shopware()->Models()->remove($logModel);
                    Shopware()->Models()->flush();
                    $data[] = Shopware()->Models()->toArray($logModel);
                }
            } else {
                $logModel = Shopware()->Models()->find('\Shopware\Models\Log\Log', $params['id']);

                Shopware()->Models()->remove($logModel);
                Shopware()->Models()->flush();
            }
            $this->View()->assign(array('success' => true, 'data' => $params));
        } catch (Exception $e) {
            $this->View()->assign(array('success' => false, 'errorMsg' => $e->getMessage()));
        }
    }

    /**
     * This method is called when a new log is made automatically.
     * It sets the different values and saves the log into s_core_log
     */
    public function createLogAction()
    {
        try {
            $request = $this->Request();
            $params = $request->getParams();
            $params['key'] = html_entity_decode($params['key']);

            $logModel = new Shopware\Models\Log\Log;

            $logModel->fromArray($params);
            $logModel->setDate(new \DateTime("now"));
            $logModel->setIpAddress($request->getClientIp());
            $logModel->setUserAgent($request->getServer('HTTP_USER_AGENT', 'Unknown'));

            Shopware()->Models()->persist($logModel);
            Shopware()->Models()->flush();

            $data = Shopware()->Models()->toArray($logModel);

            $this->View()->assign(array('success' => true, 'data' => $data));
        } catch (Exception $e) {
            $this->View()->assign(array('success' => false, 'errorMsg' => $e->getMessage()));
        }
    }

    /**
     * Returns an array of all log files in the given directory.
     *
     * @param $logDir
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
     * @param $files
     * @param null $name
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
     * @param $files
     * @return false|string
     */
    private function getDefaultLogFile(array $files)
    {
        foreach ($files as $file) {
            return $file[0];
        }

        return false;
    }

    public function downloadLogFileAction()
    {
        $logDir = $this->get('kernel')->getLogDir();
        $files = $this->getLogFiles($logDir);

        $logFile = $this->Request()->getParam('logFile');
        $logFile = $this->getLogFile($files, $logFile);

        if (!$logFile) {
            new RuntimeException('Log file not found.');
        }

        $logFilePath = $logDir . '/' . $this->getLogFile($files, $logFile);

        $response = $this->Response();
        $response->setHeader('Cache-Control', 'public');
        $response->setHeader('Content-Type', 'application/octet-stream');
        $response->setHeader('Content-Description', 'File Transfer');
        $response->setHeader('Content-Disposition', 'attachment; filename=' . $logFile);
        $response->setHeader('Content-Transfer-Encoding', 'binary');
        $response->setHeader('Content-Length', filesize($logFilePath));
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
                'default' => $file[0] == $defaultFile
            ];
        }, $files);

        $start = $this->Request()->getParam('start', 0);
        $limit = $this->Request()->getParam('limit', 100);

        $count = count($files);
        $files = array_slice($files, $start, $limit);

        $this->View()->assign([
            'success' => true,
            'data' => $files,
            'total' => $count
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
                'count' => 0
            ]);
            return;
        }

        $start = $this->Request()->getParam('start', 0);
        $limit = $this->Request()->getParam('limit', 100);
        $sort = $this->Request()->getParam('sort');

        $reverse = false;
        if (!isset($sort[0]['direction']) || $sort[0]['direction'] == 'DESC') {
            $reverse = true;
        }

        /** @var \Shopware\Components\Log\Parser\LogfileParser $reader */
        $reader = $this->get('shopware.log.fileparser')->parseLogFile(
            $logDir . '/' . $logFile,
            $start,
            $limit,
            $reverse
        );

        $count = $this->getNumberOfLines($logDir . '/' . $logFile);

        $this->View()->assign([
            'success' => true,
            'data' => $reader,
            'count' => $count
        ]);
    }

    /**
     * Return the number of lines for the given file.
     * This is faster than iterating fgets.
     * @param string $filePath
     * @return int
     */
    private function getNumberOfLines($filePath)
    {
        $file = new \SplFileObject($filePath, 'r');
        $file->seek(PHP_INT_MAX);

        return $file->key();
    }
}
