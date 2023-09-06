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

namespace Shopware\Recovery\Update;

use Exception;
use Throwable;

/**
 * Changes the permissions defined in the given array.
 */
class FilePermissionChanger
{
    /**
     * Format:
     * [
     *      ['chmod' => 0755, 'filePath' => '/path/to/some/file'],
     * ]
     *
     * @var array
     */
    private $filePermissions = [];

    /**
     * @param array
     */
    public function __construct(array $filePermissions)
    {
        $this->filePermissions = $filePermissions;
    }

    /**
     * Performs the chmod command on all permission arrays previously provided.
     */
    public function changePermissions()
    {
        foreach ($this->filePermissions as $filePermission) {
            if (\array_key_exists('filePath', $filePermission)
                && \array_key_exists('chmod', $filePermission)
                && is_writable($filePermission['filePath'])) {
                // If the owner of a file is not the user of the currently running process, "is_writable" might return true
                // while "chmod" below fails. So we suppress any errors in that case.

                try {
                    @chmod($filePermission['filePath'], $filePermission['chmod']);
                } catch (Exception $e) {
                    // Don't block the update process
                } catch (Throwable $e) {
                    // Don't block the update process
                }
            }
        }
    }
}
