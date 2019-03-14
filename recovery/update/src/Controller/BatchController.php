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

namespace Shopware\Recovery\Update\Controller;

use Gaufrette\Filesystem;
use Shopware\Components\Migrations\Manager;
use Shopware\Recovery\Common\Utils;
use Shopware\Recovery\Update\DependencyInjection\Container;
use Shopware\Recovery\Update\FilesystemFactory;
use Shopware\Recovery\Update\PathBuilder;
use Shopware\Recovery\Update\Steps\FinishResult;
use Shopware\Recovery\Update\Steps\MigrationStep;
use Shopware\Recovery\Update\Steps\ResultMapper;
use Shopware\Recovery\Update\Steps\SnippetStep;
use Shopware\Recovery\Update\Steps\UnpackStep;
use Shopware\Recovery\Update\Steps\ValidResult;
use Slim\Http\Request;
use Slim\Http\Response;

class BatchController
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var ResultMapper
     */
    private $resultMapper;

    public function __construct(Request $request, Response $response, Container $container)
    {
        $this->request = $request;
        $this->response = $response;
        $this->container = $container;

        $this->resultMapper = new ResultMapper();
    }

    public function importSnippets()
    {
        $dump = $this->container->get('dump');
        if (!$dump) {
            $result = new FinishResult(0, 0);
            $this->toJson(200, $this->resultMapper->toExtJs($result));

            return;
        }

        $conn = $this->container->get('db');
        $snippetStep = new SnippetStep($conn, $dump);

        $offset = $this->request->get('offset');
        $result = $snippetStep->run($offset);

        $this->toJson(200, $this->resultMapper->toExtJs($result));
    }

    public function applyMigrations()
    {
        /** @var Manager $migrationManger */
        $migrationManger = $this->container->get('migration.manager');
        $migrationStep = new MigrationStep($migrationManger);

        $offset = $this->request->get('offset');
        $total = $this->request->get('total');
        $result = $migrationStep->run($offset, $total);

        $this->toJson(200, $this->resultMapper->toExtJs($result));
    }

    /**
     * @throws \RuntimeException
     */
    public function unpack()
    {
        // Manual updates do not contain files to overwrite
        if (UPDATE_IS_MANUAL) {
            Utils::clearOpcodeCache();
            $this->toJson(200, $this->resultMapper->toExtJs(new FinishResult(0, 0)));

            return;
        }

        $offset = $this->request->get('offset');
        $total = $this->request->get('total');

        /** @var FilesystemFactory $factory */
        $factory = $this->container->get('filesystem.factory');

        $localFilesystem = $factory->createLocalFilesystem();
        $remoteFilesystem = $factory->createRemoteFilesystem();

        if ($offset == 0) {
            $this->validateFilesytems($localFilesystem, $remoteFilesystem);
        }

        /** @var PathBuilder $pathBuilder */
        $pathBuilder = $this->container->get('path.builder');

        $debug = false;
        $step = new UnpackStep($localFilesystem, $remoteFilesystem, $pathBuilder, $debug);

        $result = $step->run($offset, $total);

        if ($result instanceof ValidResult) {
            Utils::clearOpcodeCache();
        }

        $this->toJson(200, $this->resultMapper->toExtJs($result));
    }

    /**
     * @throws \RuntimeException
     */
    private function validateFilesytems(Filesystem $localFilesyste, Filesystem $remoteFilesyste)
    {
        if (!$remoteFilesyste->has('shopware.php')) {
            throw new \RuntimeException('shopware.php not found in remote filesystem');
        }

        if (!$localFilesyste->has('shopware.php')) {
            throw new \RuntimeException('shopware.php not found in local filesystem');
        }

        if ($localFilesyste->checksum('shopware.php') != $remoteFilesyste->checksum('shopware.php')) {
            throw new \RuntimeException('Filesytems does not seem to match');
        }
    }

    /**
     * @param int   $code
     * @param array $data
     */
    private function toJson($code, $data)
    {
        $this->response->header('Content-Type', 'application/json');
        $this->response->status($code);

        if (defined('JSON_PRETTY_PRINT')) {
            $this->response->body(json_encode($data, JSON_PRETTY_PRINT));
        } else {
            $this->response->body(json_encode($data));
        }
    }
}
