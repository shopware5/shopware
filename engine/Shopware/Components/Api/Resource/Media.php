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

namespace Shopware\Components\Api\Resource;

use Doctrine\ORM\ORMException;
use Shopware\Components\Api\Exception as ApiException;
use Shopware\Components\Random;
use Shopware\Components\Thumbnail\Manager;
use Shopware\Models\Media\Album;
use Shopware\Models\Media\Media as MediaModel;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Media API Resource
 */
class Media extends Resource
{
    const FILENAME_LENGTH = 200;

    /**
     * @return \Shopware\Models\Media\Repository
     */
    public function getRepository()
    {
        return $this->getManager()->getRepository(MediaModel::class);
    }

    /**
     * @param int $id
     *
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     *
     * @return array|MediaModel
     */
    public function getOne($id)
    {
        $this->checkPrivilege('read');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException('id');
        }

        $filters = [['property' => 'media.id', 'expression' => '=', 'value' => $id]];
        $query = $this->getRepository()->getMediaListQuery($filters, [], 1);

        /** @var MediaModel|array $media */
        $media = $query->getOneOrNullResult($this->getResultMode());

        if (!$media) {
            throw new ApiException\NotFoundException(sprintf('Media by id %d not found', $id));
        }

        $mediaService = Shopware()->Container()->get('shopware_media.media_service');
        if (is_array($media)) {
            $media['path'] = $mediaService->getUrl($media['path']);
        } else {
            $media->setPath($mediaService->getUrl($media->getPath()));
        }

        return $media;
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function getList($offset = 0, $limit = 25, array $criteria = [], array $orderBy = [])
    {
        $this->checkPrivilege('read');

        $query = $this->getRepository()->getMediaListQuery($criteria, $orderBy, $limit, $offset);
        $query->setHydrationMode($this->resultMode);

        $paginator = $this->getManager()->createPaginator($query);

        // Returns the total count of the query
        $totalResult = $paginator->count();

        // Returns the category data
        $media = $paginator->getIterator()->getArrayCopy();

        $mediaService = Shopware()->Container()->get('shopware_media.media_service');
        array_walk($media, function (&$item) use ($mediaService) {
            $item['path'] = $mediaService->getUrl($item['path']);
        });

        return ['data' => $media, 'total' => $totalResult];
    }

    /**
     * @throws \Shopware\Components\Api\Exception\ValidationException
     * @throws \Exception
     *
     * @return MediaModel
     */
    public function create(array $params)
    {
        $this->checkPrivilege('create');

        $params = $this->prepareMediaData($params);

        $media = new MediaModel();
        $media->fromArray($params);

        $path = $this->prepareFilePath($media->getPath(), $media->getFileName());
        $media->setPath($path);

        $violations = $this->getManager()->validate($media);
        if ($violations->count() > 0) {
            throw new ApiException\ValidationException($violations);
        }

        $this->getManager()->persist($media);
        $this->flush();

        if ($media->getType() === MediaModel::TYPE_IMAGE) {
            /** @var Manager $manager */
            $manager = $this->getContainer()->get('thumbnail_manager');

            $manager->createMediaThumbnail($media, [], true);
        }

        return $media;
    }

    /**
     * @param int $id
     *
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     *
     * @return MediaModel
     */
    public function update($id, array $params)
    {
        $this->checkPrivilege('update');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException('id');
        }

        /** @var MediaModel|null $media */
        $media = $this->getRepository()->find($id);

        if (!$media) {
            throw new ApiException\NotFoundException(sprintf('Media by id "%d" not found', $id));
        }

        if (!empty($params['file'])) {
            $path = $this->load($params['file'], $media->getFileName());
            $file = new UploadedFile($path, $params['file']);

            try {
                $this->getContainer()->get('shopware_media.replace_service')->replace($id, $file);
                @unlink($path);
            } catch (\Exception $exception) {
                @unlink($path);
                throw new ApiException\CustomValidationException($exception->getMessage());
            }
        }

        return $media;
    }

    /**
     * @param int $id
     *
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     *
     * @return MediaModel
     */
    public function delete($id)
    {
        $this->checkPrivilege('delete');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException('id');
        }

        /** @var MediaModel|null $media */
        $media = $this->getRepository()->find($id);

        if (!$media) {
            throw new ApiException\NotFoundException(sprintf('Media by id %d not found', $id));
        }

        $this->getManager()->remove($media);
        $this->flush();

        return $media;
    }

    /**
     * Internal helper function which is used to upload the passed image link
     * to the server and create a media object for the image.
     *
     * @param string $link
     * @param int    $albumId
     *
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     *
     * @return MediaModel
     */
    public function internalCreateMediaByFileLink($link, $albumId = -1)
    {
        $name = pathinfo($link, PATHINFO_FILENAME);
        $ext = pathinfo($link, PATHINFO_EXTENSION);
        $name = $name . '.' . $ext;
        $path = $this->load($link, $name);
        $name = pathinfo($path, PATHINFO_FILENAME);
        $file = new UploadedFile($path, $link);

        $media = new MediaModel();

        $media->setAlbumId($albumId);
        $media->setFile($file);
        $media->setName($name);
        $media->setDescription('');
        $media->setCreated(new \DateTime());
        $media->setUserId(0);

        /** @var Album|null $album */
        $album = $this->getManager()->find(Album::class, $albumId);
        if (!$album) {
            // Cleanup temporary file
            $this->deleteTmpFile($file);
            throw new ApiException\CustomValidationException(
                sprintf('Album by id %s not found', $albumId)
            );
        }

        $media->setAlbum($album);

        try {
            // Persist the model into the model manager this uploads and resizes the image
            $this->getManager()->persist($media);
        } catch (ORMException $e) {
            throw new ApiException\CustomValidationException(
                sprintf('Some error occurred while persisting your media')
            );
        } finally {
            // Cleanup temporary file
            $this->deleteTmpFile($file);
        }

        if ($media->getType() === MediaModel::TYPE_IMAGE) {
            /** @var Manager $manager */
            $manager = Shopware()->Container()->get('thumbnail_manager');

            $manager->createMediaThumbnail($media, [], true);
        }

        return $media;
    }

    /**
     * @param string $url          URL of the resource that should be loaded (ftp, http, file)
     * @param string $baseFilename Optional: Instead of creating a hash, create a filename based on the given one
     *
     * @throws \InvalidArgumentException
     * @throws \Exception
     *
     * @return bool|string returns the absolute path of the downloaded file
     */
    public function load($url, $baseFilename = null)
    {
        $destPath = tempnam(sys_get_temp_dir(), '');
        unlink($destPath);

        if (!@mkdir($destPath) && !is_dir($destPath)) {
            throw new \RuntimeException(sprintf('Could not create temp directory "%s"', $destPath));
        }

        if (strpos($url, 'data:image') !== false) {
            return $this->uploadBase64File($url, $destPath, $baseFilename);
        }

        $this->getContainer()->get('shopware.components.stream_protocol_validator')->validate($url);

        $filename = $this->getUniqueFileName($destPath, $baseFilename);
        $filePath = sprintf('%s/%s', $destPath, $filename);

        if (!$put_handle = fopen($filePath, 'wb+')) {
            throw new \Exception(sprintf('Could not open %s for writing', $filePath));
        }

        if (!$get_handle = fopen($url, 'rb')) {
            throw new \Exception(sprintf('Could not open %s for reading', $url));
        }

        while (!feof($get_handle)) {
            fwrite($put_handle, fgets($get_handle, 4096));
        }

        fclose($get_handle);
        fclose($put_handle);

        return sprintf('%s/%s', $destPath, $filename);
    }

    /**
     * Helper function to get a unique file name for the passed destination path.
     *
     * @param string      $destPath
     * @param string|null $baseFileName
     *
     * @return string|null
     */
    public function getUniqueFileName($destPath, $baseFileName = null)
    {
        if ($baseFileName !== null) {
            $baseFileName = basename($baseFileName);
        }

        $mediaService = Shopware()->Container()->get('shopware_media.media_service');
        if ($baseFileName !== null && !$mediaService->has("$destPath/$baseFileName")) {
            return substr($baseFileName, 0, self::FILENAME_LENGTH);
        }

        $counter = 1;
        if ($baseFileName === null) {
            $filename = Random::getAlphanumericString(32);
        } else {
            $filename = $baseFileName;
        }

        $filename = substr($filename, 0, self::FILENAME_LENGTH);

        while ($mediaService->has("$destPath/$filename")) {
            if ($baseFileName) {
                $filename = "$counter-$baseFileName";
                ++$counter;
            } else {
                $filename = Random::getAlphanumericString(32);
            }
            $filename = substr($filename, 0, self::FILENAME_LENGTH);
        }

        return $filename;
    }

    /**
     * Helper function which downloads the passed image url
     * and save the image with a unique file name in the destination path.
     * If the passed baseFilename already exists in the destination path,
     * the function creates a unique file name.
     *
     * @param string $url
     * @param string $destinationPath
     * @param string $baseFilename
     *
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     * @throws \Exception
     *
     * @return string
     */
    protected function uploadBase64File($url, $destinationPath, $baseFilename)
    {
        if (!$get_handle = fopen($url, 'r')) {
            throw new \Exception(sprintf('Could not open %s for reading', $url));
        }

        $meta = stream_get_meta_data($get_handle);
        if (strpos($meta['mediatype'], 'image/') === false) {
            throw new ApiException\CustomValidationException(sprintf('No valid media type passed for the product image: %s', $url));
        }

        $extension = str_replace('image/', '', $meta['mediatype']);
        $filename = $this->getUniqueFileName($destinationPath, $baseFilename) . '.' . $extension;
        $destinationFilePath = sprintf('%s/%s', $destinationPath, $filename);

        if (!$put_handle = fopen("$destinationPath/$filename", 'wb+')) {
            throw new \Exception("Could not open $destinationPath/$filename for writing");
        }
        while (!feof($get_handle)) {
            fwrite($put_handle, fgets($get_handle, 4096));
        }
        fclose($get_handle);
        fclose($put_handle);

        return $destinationFilePath;
    }

    /**
     * @param MediaModel $media
     *
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Exception
     *
     * @return array
     */
    private function prepareMediaData(array $params, $media = null)
    {
        // in create mode, album is a required param
        if (!$media && (!isset($params['album']) || empty($params['album']))) {
            throw new ApiException\ParameterMissingException('album');
        }

        if (!$media && (!isset($params['file']) || empty($params['file']))) {
            throw new ApiException\ParameterMissingException('file');
        }

        if (!$media && (!isset($params['description']) || empty($params['description']))) {
            throw new ApiException\ParameterMissingException('description');
        }

        if (!$media && (!isset($params['userId']) || empty($params['userId']))) {
            $params['userId'] = 0;
        }

        if (!$media && (!isset($params['created']) || empty($params['created']))) {
            $params['created'] = new \DateTime();
        }

        // Check / set album
        if (isset($params['album'])) {
            $album = Shopware()->Models()->find(Album::class, $params['album']);
            if (!$album) {
                throw new ApiException\CustomValidationException(sprintf('Album by id %s not found', $params['album']));
            }
            $params['album'] = $album;
        }

        if (isset($params['file']) && !($params['file'] instanceof File)) {
            if (!isset($params['name'])) {
                $params['name'] = pathinfo($params['file'], PATHINFO_FILENAME);
            }
            $params['name'] = $this->getUniqueFileName($params['file'], $params['name']);
            $originalName = $params['file'];

            $this->getContainer()->get('shopware.components.stream_protocol_validator')->validate($params['file']);

            if (!file_exists($params['file']) || strpos($params['file'], 'ftp://') === 0) {
                try {
                    $path = $this->load($params['file'], $params['name']);

                    if (strpos($params['file'], 'data:image') !== false) {
                        $originalName = $params['name'];
                    }
                } catch (\Exception $e) {
                    throw new \Exception(sprintf('Could not load image %s', $params['file']), $e->getCode(), $e);
                }
            } else {
                $path = str_replace('file://', '', $params['file']);
            }
            $params['file'] = new UploadedFile($path, $originalName);
        }

        return $params;
    }

    /**
     * Replaces the filename in the path with the short filename because
     * the media object holds the old path with over FILENAME_LENGTH characters.
     * This is necessary because the thumbnail manager uses the path from the media object.
     *
     * @param string $oldPath
     * @param string $filename
     *
     * @return string|bool
     */
    private function prepareFilePath($oldPath, $filename)
    {
        $oldFilename = pathinfo($oldPath, PATHINFO_BASENAME);

        if (strlen($oldFilename) >= self::FILENAME_LENGTH) {
            return str_replace($oldFilename, $filename, $oldPath);
        }

        return $oldPath;
    }

    /**
     * @param string $file
     */
    private function deleteTmpFile($file)
    {
        if (file_exists($file)) {
            unlink($file);
            rmdir(dirname($file));
        }
    }
}
