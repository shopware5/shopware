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

namespace Shopware\Components\File;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\FileBag;

/**
 * Class to handle file uploads.
 *
 * @category  Shopware
 * @package   Shopware\Components\HttpCache
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Manager
{
    /**
     * Helper function which creates a UploadedFile object
     * for the fileId element in the $_FILES object.
     *
     * @param array $files Global $_FILES array
     * @param $id
     * @throws \Exception
     * @return UploadedFile
     */
    public function getUpload(array $files, $id)
    {
        if (!array_key_exists($id, $files)) {
            throw new \Exception("File key don't exist in the passed array");
        }

        $file = $files[$id];

        if ($file['size'] < 1 && $file['error'] === 1 || empty($files)) {
            throw new \Exception("The file exceeds the max file size.");
        }

        $fileInfo = pathinfo($file['name']);
        $fileExtension = strtolower($fileInfo['extension']);
        $file['name'] = $fileInfo['filename'] . "." . $fileExtension;

        $files[$id]['name'] = $file['name'];

        $fileBag = new FileBag($_FILES);

        return $fileBag->get($id);
    }

    /**
     * Removes the temporary created upload file.
     *
     * @param UploadedFile $file
     */
    public function remove(UploadedFile $file)
    {
        unlink($file->getPathname());
        unlink($file);
    }

    /**
     * Helper function to decompress zip files.
     * @param UploadedFile $file
     * @param $targetDirectory
     */
    public function unzip(UploadedFile $file, $targetDirectory)
    {
        $filter = new \Zend_Filter_Decompress(array(
            'adapter' => $file->getClientOriginalExtension(),
            'options' => array('target' => $targetDirectory)
        ));

        $filter->filter(
            $file->getPath() . DIRECTORY_SEPARATOR . $file->getFilename()
        );
    }
}