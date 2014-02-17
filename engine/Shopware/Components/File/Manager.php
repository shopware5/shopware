<?php

namespace Shopware\Components\File;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\FileBag;

class Manager
{

    /**
     * Helper function which creates a UploadedFile object
     * for the fileId element in the $_FILES object.
     *
     * @param array $files Global $_FILES object
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