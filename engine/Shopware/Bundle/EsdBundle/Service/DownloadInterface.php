<?php

namespace Shopware\Bundle\EsdBundle\Service;

interface DownloadInterface
{
    /**
     * @param string $file
     * @return string
     */
    public function getAbsoluteFilePath($file);

    /**
     * @param string $file
     * @return string
     */
    public function getRelativeFilePath($file);

    /**
     * @param string $file
     * @return bool
     */
    public function existsFile($file);

    /**
     * @param string $file
     */
    public function sendFile($file);
}
