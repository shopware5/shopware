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

namespace ShopwarePlugins\SwagUpdate\Components\Checks;

use Enlight_Components_Snippet_Namespace as SnippetNamespace;
use ShopwarePlugins\SwagUpdate\Components\CheckInterface;
use ShopwarePlugins\SwagUpdate\Components\FileSystem;
use ShopwarePlugins\SwagUpdate\Components\Validation;

class WritableCheck implements CheckInterface
{
    const CHECK_TYPE = 'writable';

    /**
     * @var SnippetNamespace
     */
    private $namespace;

    /**
     * @var FileSystem
     */
    private $fileSystem;

    public function __construct(FileSystem $fileSystem, SnippetNamespace $namespace)
    {
        $this->namespace = $namespace;
        $this->fileSystem = $fileSystem;
    }

    /**
     * {@inheritdoc}
     */
    public function canHandle($requirement)
    {
        return $requirement['type'] == self::CHECK_TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function check($requirement)
    {
        $directories = [];
        $checkedDirectories = [];

        $successMessage = $this->namespace->get('controller/check_writable_success', 'The following directories are writeable <br/>%s');
        $failMessage = $this->namespace->get('controller/check_writable_failure', 'The following directories are not writable: <br> %s');

        foreach ($requirement['value'] as $path) {
            $fullPath = rtrim(Shopware()->DocPath($path), '/');
            $checkedDirectories[] = $fullPath;

            $fixPermissions = true;
            $directories = array_merge(
                $directories,
                $this->fileSystem->checkSingleDirectoryPermissions($fullPath, $fixPermissions)
            );
        }

        if (empty($directories)) {
            return [
                'type' => self::CHECK_TYPE,
                'errorLevel' => Validation::REQUIREMENT_VALID,
                'message' => sprintf(
                    $successMessage,
                    implode('<br>', $checkedDirectories)
                ),
            ];
        }

        return [
                'type' => self::CHECK_TYPE,
                'errorLevel' => $requirement['level'],
                'message' => sprintf(
                    $failMessage,
                    implode('<br>', $directories)
                ),
            ];
    }
}
