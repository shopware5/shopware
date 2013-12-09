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

namespace Shopware\Components\Api\Resource;

use Shopware\Components\Api\Exception as ApiException;

/**
 * Media API Resource
 *
 * @category  Shopware
 * @package   Shopware\Components\Api\Resource
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Media extends Resource
{
    /**
     * @return \Shopware\Models\Category\Repository
     */
    public function getRepository()
    {
        return $this->getManager()->getRepository('Shopware\Models\Media\Media');
    }

    /**
     * @param int $id
     * @return array|\Shopware\Models\Media\Media
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     */
    public function getOne($id)
    {

        $this->checkPrivilege('read');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }

        $filters = array(array('property' => 'media.id','expression' => '=','value' => $id));
        $query = $this->getRepository()->getMediaListQuery($filters, array(), 1);

        /** @var $media \Shopware\Models\Media\Media*/
        $media = $query->getOneOrNullResult($this->getResultMode());


        if (!$media) {
            throw new ApiException\NotFoundException("Media by id $id not found");
        }

        return $media;
    }

    /**
     * @param int $offset
     * @param int $limit
     * @param array $criteria
     * @param array $orderBy
     * @return array
     */
    public function getList($offset = 0, $limit = 25, array $criteria = array(), array $orderBy = array())
    {
        $this->checkPrivilege('read');

        $query = $this->getRepository()->getMediaListQuery($criteria, $orderBy, $limit, $offset);
        $query->setHydrationMode($this->resultMode);

        $paginator = $this->getManager()->createPaginator($query);

        //returns the total count of the query
        $totalResult = $paginator->count();

        //returns the category data
        $media = $paginator->getIterator()->getArrayCopy();

        return array('data' => $media, 'total' => $totalResult);
    }

    /**
     * @param array $params
     * @return \Shopware\Models\Media\Media
     * @throws \Shopware\Components\Api\Exception\ValidationException
     * @throws \Exception
     */
    public function create(array $params)
    {
        $this->checkPrivilege('create');

        $params = $this->prepareMediaData($params);

        $media = new \Shopware\Models\Media\Media();
        $media->fromArray($params);

        $violations = $this->getManager()->validate($media);
        if ($violations->count() > 0) {
            throw new ApiException\ValidationException($violations);
        }

        $this->getManager()->persist($media);
        $this->flush();

        return $media;
    }

    /**
     * @param int $id
     * @param array $params
     * @return \Shopware\Models\Media\Media
     * @throws \Shopware\Components\Api\Exception\ValidationException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     */
    public function update($id, array $params)
    {
        $this->checkPrivilege('update');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }

        /** @var $media \Shopware\Models\Media\Media */
        $media = $this->getRepository()->find($id);

        if (!$media) {
            throw new ApiException\NotFoundException("Media by id $id not found");
        }

        $params = $this->prepareMediaData($params, $media);
        $media->fromArray($params);

        $violations = $this->getManager()->validate($media);
        if ($violations->count() > 0) {
            throw new ApiException\ValidationException($violations);
        }

        // When a media's image was changed, we need to recreate thumbnails.
        // Therefore the onSave method of the media model needs to be called.
        // As ist listens to prePersist, this can only be done, when a model
        // is persisted for the first time.
        // In other words: Changing images for a media model is not possible
        // right now. It might also have massiv side-effects when other
        // modules use a specific image.
        // SW-4464
//        $media->onSave();

        $this->flush();

        return $media;
    }

    /**
     * @param int $id
     * @return \Shopware\Models\Media\Media
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     */
    public function delete($id)
    {
        $this->checkPrivilege('delete');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }

        /** @var $media \Shopware\Models\Media\Media */
        $media = $this->getRepository()->find($id);

        if (!$media) {
            throw new ApiException\NotFoundException("Media by id $id not found");
        }

        $this->getManager()->remove($media);
        $this->flush();

        return $media;
    }

    /**
     * @param array $params
     * @param \Shopware\Models\Media\Media $media
     * @return mixed
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Exception
     */
    private function prepareMediaData($params, $media = null)
    {
        // in create mode, album is a required param
        if (!$media && (!isset($params['album']) || empty($params['album']))) {
            throw new ApiException\ParameterMissingException();
        }

        if (!$media && (!isset($params['file']) || empty($params['file']))) {
            throw new ApiException\ParameterMissingException();

        }

        if (!$media && (!isset($params['description']) || empty($params['description']))) {
            throw new ApiException\ParameterMissingException();
        }

        if (!$media && (!isset($params['userId']) || empty($params['userId']))) {
            $params['userId'] = 0;
        }

        if (!$media && (!isset($params['created']) || empty($params['created']))) {
            $params['created'] = new \DateTime();
        }

        // Check / set album
        if (isset($params['album'])) {
            $album = Shopware()->Models()->find('\Shopware\Models\Media\Album', $params['album']);
            if (!$album) {
                throw new ApiException\CustomValidationException(sprintf("Album by id %s not found", $params['album']));
            }
            $params['album'] = $album;
        }

        if (isset($params['file'])) {
            if (!file_exists($params['file'])) {
                try {
                    $name = pathinfo($params['file'], PATHINFO_FILENAME);
                    $path = $this->load($params['file'], $name);
                } catch (\Exception $e) {
                    throw new \Exception(sprintf("Could not load image %s", $params['file'] ));
                }
            } else {
                $path = $params['file'];
            }
            $params['file'] = new \Symfony\Component\HttpFoundation\File\File($path);
            if (!isset($params['name'])) {
                $params['name'] = pathinfo($path, PATHINFO_FILENAME);
            }
        }

        return $params;
    }


    /**
     * Helper function to load a remote file
     * @param string $url URL of the resource that should be loaded (ftp, http, file)
     * @param string $baseFilename Optional: Instead of creating a hash, create a filename based on the given one
     * @return bool|string returns the absolute path of the downloaded file
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    protected function load($url, $baseFilename = null)
    {
        $destPath = Shopware()->DocPath('media_' . 'temp');
        if (!is_dir($destPath)) {
            mkdir($destPath, 0777, true);
        }

        $destPath = realpath($destPath);

        if (!file_exists($destPath)) {
            throw new \InvalidArgumentException(
                sprintf("Destination directory '%s' does not exist.", $destPath)
            );
        } elseif (!is_writable($destPath)) {
            throw new \InvalidArgumentException(
                sprintf("Destination directory '%s' does not have write permissions.", $destPath)
            );
        }

        $urlArray = parse_url($url);
        $urlArray['path'] = explode("/", $urlArray['path']);
        switch ($urlArray['scheme']) {
            case "ftp":
            case "http":
            case "file":
                $counter = 1;
                if ($baseFilename === null) {
                    $filename = md5(uniqid(rand(), true));
                } else {
                    $filename = $baseFilename;
                }

                while (file_exists("$destPath/$filename")) {
                    if ($baseFilename) {
                        $filename = "$counter-$baseFilename";
                        $counter++;
                    } else {
                        $filename = md5(uniqid(rand(), true));
                    }
                }

                if (!$put_handle = fopen("$destPath/$filename", "w+")) {
                    throw new \Exception("Could not open $destPath/$filename for writing");
                }

                if (!$get_handle = fopen($url, "r")) {
                    throw new \Exception("Could not open $url for reading");
                }
                while (!feof($get_handle)) {
                    fwrite($put_handle, fgets($get_handle, 4096));
                }
                fclose($get_handle);
                fclose($put_handle);

                return "$destPath/$filename";
        }
        throw new \InvalidArgumentException(
            sprintf("Unsupported schema '%s'.", $urlArray['scheme'])
        );
    }
}
