<?php

namespace Shopware\Bundle\EsdBundle\Service\Core;

use Enlight_Controller_Front;
use Shopware\Bundle\EsdBundle\Service\DownloadInterface;
use Shopware_Components_Config;

class DownloadService implements DownloadInterface
{
    /**
     * @var string
     */
    private $shopwareRootDirectory;

    /**
     * @var Shopware_Components_Config
     */
    private $config;

    /**
     * @var Enlight_Controller_Front
     */
    private $front;

    /**
     * @param $shopwareRootDirectory
     * @param Shopware_Components_Config $config
     * @param Enlight_Controller_Front $front
     */
    public function __construct(
        $shopwareRootDirectory,
        Shopware_Components_Config $config,
        Enlight_Controller_Front $front
    ) {
        $this->shopwareRootDirectory = $shopwareRootDirectory;
        $this->config = $config;
        $this->front = $front;
    }

    /**
     * {@inheritdoc}
     */
    public function getAbsoluteFilePath($file)
    {
        return $this->shopwareRootDirectory . $this->getRelativeFilePath($file);
    }

    /**
     * {@inheritdoc}
     */
    public function getRelativeFilePath($file)
    {
        return 'files/' . $this->config->get('sESDKEY') . '/' . $file;
    }

    /**
     * {@inheritdoc}
     */
    public function existsFile($file)
    {
        return file_exists($this->getAbsoluteFilePath($file));
    }

    /**
     * {@inheritdoc}
     */
    public function sendFile($file)
    {
        $relativeFilePath = $this->getRelativeFilePath($file);
        $absoluteFilePath = $this->getAbsoluteFilePath($file);
        $fileName = basename($absoluteFilePath);

        switch ($this->config->get('esdDownloadStrategy')) {
            case 0:
                $url = $this->front->Request()->getBasePath() . '/' . $relativeFilePath;
                $this->front->Response()->setRedirect($url);
                break;
            case 1:
                @set_time_limit(0);
                $this->front->Response()
                    ->setHeader('Content-Type', 'application/octet-stream')
                    ->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"')
                    ->setHeader('Content-Length', filesize($absoluteFilePath));

                $this->front->Plugins()->ViewRenderer()->setNoRender();

                readfile($absoluteFilePath);
                break;
            case 2:
                // Apache2 + X-Sendfile
                $this->front->Response()
                    ->setHeader('Content-Type', 'application/octet-stream')
                    ->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"')
                    ->setHeader('X-Sendfile', $absoluteFilePath);

                $this->front->Plugins()->ViewRenderer()->setNoRender();

                break;
            case 3:
                // Nginx + X-Accel
                $this->front->Response()
                    ->setHeader('Content-Type', 'application/octet-stream')
                    ->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"')
                    ->setHeader('X-Accel-Redirect', '/' . $relativeFilePath);

                $this->front->Plugins()->ViewRenderer()->setNoRender();

                break;
        }
    }
}
