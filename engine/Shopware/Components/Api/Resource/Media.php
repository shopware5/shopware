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

namespace Shopware\Components\Api\Resource;

use DateTime;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query;
use Exception;
use InvalidArgumentException;
use RuntimeException;
use Shopware\Bundle\MediaBundle\MediaReplaceServiceInterface;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Components\Api\Exception\CustomValidationException;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Exception\ParameterMissingException;
use Shopware\Components\Api\Exception\ValidationException;
use Shopware\Components\Random;
use Shopware\Components\Thumbnail\Manager;
use Shopware\Models\Attribute\Media as MediaAttribute;
use Shopware\Models\Media\Album;
use Shopware\Models\Media\Media as MediaModel;
use Shopware\Models\Media\Repository;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Media API Resource
 */
class Media extends Resource
{
    public const FILENAME_LENGTH = 200;

    /**
     * @return Repository
     */
    public function getRepository()
    {
        return $this->getManager()->getRepository(MediaModel::class);
    }

    /**
     * @param int $id
     *
     * @throws ParameterMissingException
     * @throws NotFoundException
     *
     * @return array|MediaModel
     */
    public function getOne($id)
    {
        $this->checkPrivilege('read');

        if (empty($id)) {
            throw new ParameterMissingException('id');
        }

        $filters = [['property' => 'media.id', 'expression' => '=', 'value' => $id]];
        $query = $this->getRepository()->getMediaListQuery($filters, [], 1);

        /** @var MediaModel|array $media */
        $media = $query->getOneOrNullResult($this->getResultMode());

        if (!$media) {
            throw new NotFoundException(sprintf('Media by id %d not found', $id));
        }

        $mediaService = Shopware()->Container()->get(MediaServiceInterface::class);
        if (\is_array($media)) {
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

        /** @var Query<MediaModel|array<string, mixed>> $query */
        $query = $this->getRepository()->getMediaListQuery($criteria, $orderBy, $limit, $offset);
        $query->setHydrationMode($this->resultMode);

        $paginator = $this->getManager()->createPaginator($query);

        // Returns the total count of the query
        $totalResult = $paginator->count();

        // Returns the category data
        $media = iterator_to_array($paginator);

        $mediaService = Shopware()->Container()->get(MediaServiceInterface::class);
        array_walk($media, function (&$item) use ($mediaService) {
            if (\is_array($item)) {
                $item['path'] = $mediaService->getUrl($item['path']);
            }
        });

        return ['data' => $media, 'total' => $totalResult];
    }

    /**
     * @throws ValidationException
     * @throws Exception
     *
     * @return MediaModel
     */
    public function create(array $params)
    {
        $this->checkPrivilege('create');

        $params = $this->prepareMediaData($params);

        $media = new MediaModel();
        $media->fromArray($params);
        $attribute = new MediaAttribute();

        if (isset($params['attribute']) && \is_array($params['attribute'])) {
            $attribute->fromArray($params['attribute']);
        }
        $media->setAttribute($attribute);

        $path = $this->prepareFilePath($media->getPath(), $media->getFileName());
        $media->setPath($path);

        $violations = $this->getManager()->validate($media);
        if ($violations->count() > 0) {
            throw new ValidationException($violations);
        }

        $this->getManager()->persist($attribute);
        $this->getManager()->persist($media);
        $this->flush();

        if ($media->getType() === MediaModel::TYPE_IMAGE) {
            /** @var Manager $manager */
            $manager = $this->getContainer()->get(Manager::class);

            $manager->createMediaThumbnail($media, [], true);
        }

        return $media;
    }

    /**
     * @param int $id
     *
     * @throws NotFoundException
     * @throws ParameterMissingException
     * @throws CustomValidationException
     *
     * @return MediaModel
     */
    public function update($id, array $params)
    {
        $this->checkPrivilege('update');

        if (empty($id)) {
            throw new ParameterMissingException('id');
        }

        /** @var MediaModel|null $media */
        $media = $this->getRepository()->find($id);

        if (!$media) {
            throw new NotFoundException(sprintf('Media by id "%d" not found', $id));
        }

        if (!empty($params['file'])) {
            $path = $this->load($params['file'], $media->getFileName());
            $file = new UploadedFile($path, $params['file']);

            try {
                $this->getContainer()->get(MediaReplaceServiceInterface::class)->replace($id, $file);
                @unlink($path);
            } catch (Exception $exception) {
                @unlink($path);
                throw new CustomValidationException($exception->getMessage());
            }
        }

        if (isset($params['attribute']) && \is_array($params['attribute'])) {
            $attribute = $media->getAttribute();
            $attribute->fromArray($params['attribute']);

            $media->setAttribute($attribute);
            $this->getManager()->persist($attribute);
            $this->getManager()->flush();
        }

        return $media;
    }

    /**
     * @param int $id
     *
     * @throws ParameterMissingException
     * @throws NotFoundException
     *
     * @return MediaModel
     */
    public function delete($id)
    {
        $this->checkPrivilege('delete');

        if (empty($id)) {
            throw new ParameterMissingException('id');
        }

        /** @var MediaModel|null $media */
        $media = $this->getRepository()->find($id);

        if (!$media) {
            throw new NotFoundException(sprintf('Media by id %d not found', $id));
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
     * @throws CustomValidationException
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
        $media->setCreated(new DateTime());
        $media->setUserId(0);

        /** @var Album|null $album */
        $album = $this->getManager()->find(Album::class, $albumId);
        if (!$album) {
            // Cleanup temporary file
            $this->deleteTmpFile($file);
            throw new CustomValidationException(sprintf('Album by id %s not found', $albumId));
        }

        $media->setAlbum($album);

        try {
            // Persist the model into the model manager this uploads and resizes the image
            $this->getManager()->persist($media);
        } catch (ORMException $e) {
            throw new CustomValidationException(sprintf('Some error occurred while persisting your media'));
        } finally {
            // Cleanup temporary file
            $this->deleteTmpFile($file);
        }

        if ($media->getType() === MediaModel::TYPE_IMAGE) {
            /** @var Manager $manager */
            $manager = Shopware()->Container()->get(Manager::class);

            $manager->createMediaThumbnail($media, [], true);
        }

        return $media;
    }

    /**
     * @param string      $url          URL of the resource that should be loaded (ftp, http, file)
     * @param string|null $baseFilename Optional: Instead of creating a hash, create a filename based on the given one
     *
     * @throws InvalidArgumentException
     * @throws Exception
     *
     * @return string returns the absolute path of the downloaded file
     */
    public function load($url, $baseFilename = null)
    {
        $destPath = tempnam(sys_get_temp_dir(), '');
        unlink($destPath);

        if (!\is_string($destPath) || (!@mkdir($destPath) && !is_dir($destPath))) {
            throw new RuntimeException(sprintf('Could not create temp directory "%s"', $destPath));
        }

        $this->getContainer()->get('shopware.components.stream_protocol_validator')->validate($url);

        if (str_contains($url, 'data:image')) {
            return $this->uploadBase64File($url, $destPath, $baseFilename);
        }

        $filename = $this->getUniqueFileName($destPath, $baseFilename);
        $filePath = sprintf('%s/%s', $destPath, $filename);

        $put_handle = fopen($filePath, 'wb+');
        if (!\is_resource($put_handle)) {
            throw new Exception(sprintf('Could not open %s for writing', $filePath));
        }

        $get_handle = fopen($url, 'rb');
        if (!\is_resource($get_handle)) {
            throw new Exception(sprintf('Could not open %s for reading', $url));
        }

        while (!feof($get_handle)) {
            $read = fgets($get_handle, 4096);
            if (!\is_string($read)) {
                continue;
            }
            fwrite($put_handle, $read);
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
     * @return string
     */
    public function getUniqueFileName($destPath, $baseFileName = null)
    {
        if ($baseFileName !== null) {
            $baseFileName = basename($baseFileName);
        }

        $mediaService = Shopware()->Container()->get(MediaServiceInterface::class);
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
     * @param string      $url
     * @param string      $destinationPath
     * @param string|null $baseFilename
     *
     * @throws CustomValidationException
     * @throws Exception
     *
     * @return string
     */
    protected function uploadBase64File($url, $destinationPath, $baseFilename)
    {
        $get_handle = fopen($url, 'r');
        if (!\is_resource($get_handle)) {
            throw new Exception(sprintf('Could not open %s for reading', $url));
        }

        $meta = stream_get_meta_data($get_handle);
        if (!\array_key_exists('mediatype', $meta) || !str_contains($meta['mediatype'], 'image/')) {
            throw new CustomValidationException(sprintf('No valid media type passed for the product image: %s', $url));
        }

        $extension = str_replace('image/', '', $meta['mediatype']);
        $filename = $this->getUniqueFileName($destinationPath, $baseFilename) . '.' . $extension;
        $destinationFilePath = sprintf('%s/%s', $destinationPath, $filename);

        $put_handle = fopen("$destinationPath/$filename", 'wb+');
        if (!\is_resource($put_handle)) {
            throw new Exception("Could not open $destinationPath/$filename for writing");
        }

        while (!feof($get_handle)) {
            $read = fgets($get_handle, 4096);
            if (!\is_string($read)) {
                continue;
            }
            fwrite($put_handle, $read);
        }

        fclose($get_handle);
        fclose($put_handle);

        return $destinationFilePath;
    }

    /**
     * @param MediaModel $media
     *
     * @throws CustomValidationException
     * @throws ParameterMissingException
     * @throws Exception
     *
     * @return array
     */
    private function prepareMediaData(array $params, $media = null)
    {
        // in create mode, album is a required param
        if (!$media && (!isset($params['album']) || empty($params['album']))) {
            throw new ParameterMissingException('album');
        }

        if (!$media && (!isset($params['file']) || empty($params['file']))) {
            throw new ParameterMissingException('file');
        }

        if (!$media && (!isset($params['description']) || empty($params['description']))) {
            throw new ParameterMissingException('description');
        }

        if (!$media && (!isset($params['userId']) || empty($params['userId']))) {
            $params['userId'] = 0;
        }

        if (!$media && (!isset($params['created']) || empty($params['created']))) {
            $params['created'] = new DateTime();
        }

        // Check / set album
        if (isset($params['album'])) {
            $album = Shopware()->Models()->find(Album::class, $params['album']);
            if (!$album) {
                throw new CustomValidationException(sprintf('Album by id %s not found', $params['album']));
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

            if (!file_exists($params['file']) || str_starts_with($params['file'], 'ftp://')) {
                try {
                    $path = $this->load($params['file'], $params['name']);

                    if (str_contains($params['file'], 'data:image')) {
                        $originalName = $params['name'];
                    }
                } catch (Exception $e) {
                    throw new Exception(sprintf('Could not load image %s', $params['file']), $e->getCode(), $e);
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
     * @return string
     */
    private function prepareFilePath($oldPath, $filename)
    {
        $oldFilename = pathinfo($oldPath, PATHINFO_BASENAME);

        if (\strlen($oldFilename) >= self::FILENAME_LENGTH) {
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
            rmdir(\dirname($file));
        }
    }
}
