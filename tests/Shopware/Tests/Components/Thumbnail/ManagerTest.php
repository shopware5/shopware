<?php
class Shopware_Tests_Components_Thumbnail_ManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testManagerInstance()
    {
        $manager = Shopware()->ResourceLoader()->get('thumbnail_manager');
        $this->assertInstanceOf('\Shopware\Components\Thumbnail\Manager', $manager);
    }

    public function testThumbnailGeneration()
    {
        $manager = Shopware()->ResourceLoader()->get('thumbnail_manager');

        $media = $this->getMediaModel();

        $sizes = array(
            '100x110',
            array(120, 130),
            array(140),
            array(
                'width'  => 150,
                'height' => 160
            )
        );

        $manager->createMediaThumbnail($media, $sizes);

        $thumbnailDir = Shopware()->DocPath('media_' . strtolower($media->getType()) . '_thumbnail');

        $path = $thumbnailDir . $media->getName();
        $this->assertFileExists($path . '_100x110.jpg');
        $this->assertFileExists($path . '_120x130.jpg');
        $this->assertFileExists($path . '_140x140.jpg');
        $this->assertFileExists($path . '_150x160.jpg');
    }

    private function getMediaModel()
    {
        $media = new \Shopware\Models\Media\Media();

        $imagePath = __DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'sw_icon.png';

        $file = new \Symfony\Component\HttpFoundation\File\File($imagePath);

        $media->setFile($file);
        $media->setAlbumId(-10);
        $media->setAlbum(Shopware()->Models()->find('Shopware\Models\Media\Album', -10));
        $media->setPath(str_replace(Shopware()->DocPath(), '', $imagePath));
        $media->setDescription('');
        $media->setUserId(0);

        return $media;
    }

    public function testGenerationWithoutPassedSizes()
    {
        $manager = Shopware()->ResourceLoader()->get('thumbnail_manager');

        $media = $this->getMediaModel();

        $sizes = array(
            '200x210',
            '220x230',
            '240x250'
        );

        $media->getAlbum()->getSettings()->setThumbnailSize($sizes);

        $manager->createMediaThumbnail($media);

        $thumbnailDir = Shopware()->DocPath('media_' . strtolower($media->getType()) . '_thumbnail');

        $path = $thumbnailDir . $media->getName();

        foreach ($sizes as $size) {
            $this->assertFileExists($path . '_' . $size . '.jpg');
        }
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage No album configured for the passed media object and no size passed!
     */
    public function testGenerationWithoutAlbum()
    {
        $media = new \Shopware\Models\Media\Media();

        $imagePath = __DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'sw_icon.png';

        $file = new \Symfony\Component\HttpFoundation\File\File($imagePath);

        $media->setFile($file);
        $media->setPath(str_replace(Shopware()->DocPath(), '', $imagePath));

        $manager = Shopware()->ResourceLoader()->get('thumbnail_manager');
        $manager->createMediaThumbnail($media);

        $thumbnailDir = Shopware()->DocPath('media_' . strtolower($media->getType()) . '_thumbnail');
        $path = $thumbnailDir . $media->getName();
        $this->assertFileExists($path . '_140x140.jpg');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage File is not an image
     */
    public function testGenerationWithEmptyMedia()
    {
        $media = new \Shopware\Models\Media\Media();

        $manager = Shopware()->ResourceLoader()->get('thumbnail_manager');
        $manager->createMediaThumbnail($media);
    }

    public function testThumbnailCleanUp()
    {
        $manager = Shopware()->ResourceLoader()->get('thumbnail_manager');

        $media = $this->getMediaModel();

        $manager->createMediaThumbnail($media);

        $thumbnailDir = Shopware()->DocPath('media_' . strtolower($media->getType()) . '_thumbnail');
        $path = $thumbnailDir . $media->getName();

        $defaultSize = $media->getDefaultThumbnails();
        $defaultSize = $defaultSize[0];

        $this->assertFileExists($path . '_' . $defaultSize[0] . 'x' . $defaultSize[1] . '.jpg');

        $manager->removeMediaThumbnails($media);

        $this->assertFileNotExists($path . '_' . $defaultSize[0] . 'x' . $defaultSize[1] . '.jpg');
    }
}
