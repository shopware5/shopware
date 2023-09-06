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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Shopware\Bundle\MediaBundle\MediaReplaceServiceInterface;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Components\CSRFWhitelistAware;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Model\QueryBuilder;
use Shopware\Components\Thumbnail\Manager;
use Shopware\Models\Media\Album;
use Shopware\Models\Media\Media;
use Shopware\Models\Media\Repository;
use Shopware\Models\Media\Settings;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Shopware_Controllers_Backend_MediaManager extends Shopware_Controllers_Backend_ExtJs implements CSRFWhitelistAware
{
    private const UNSORTED_ALBUM_ID = -10;
    private const FILE_NOT_FOUND_MESSAGE = 'file not found';
    private const FALLBACK_USER_ID = 0;

    public static $fileUploadBlacklist = [
        'php',
        'php3',
        'php4',
        'php5',
        'phtml',
        'cgi',
        'pl',
        'sh',
        'com',
        'bat',
        '',
        'py',
        'rb',
        'exe',
    ];

    /**
     * Entity Manager
     *
     * @var ModelManager
     */
    protected $manager;

    /**
     * {@inheritdoc}
     */
    public function getWhitelistedCSRFActions()
    {
        return [
            'download',
        ];
    }

    /**
     * Returns a JSON string containing all media albums.
     * Unlike the other Shopware backend controller actions, this action uses the standard method "find".
     * The "find" method provides an automatic recursive call to determine the sub-albums with them,
     * so that the sub-albums not to be loaded on demand.
     *
     * @return void
     */
    public function getAlbumsAction()
    {
        $builder = $this->get('models')->createQueryBuilder();
        $albumId = $this->Request()->getParam('albumId');

        $builder->select(['album'])
            ->from(Album::class, 'album')
            ->where('album.parentId IS NULL')
            ->orderBy('album.position', 'ASC');

        if (!empty($albumId)) {
            $albumId = explode(',', $albumId);
            $builder->andWhere('album.id IN(:albumId)')
                ->setParameter('albumId', $albumId);
        }

        $albums = $builder->getQuery()->getResult();

        $albums = $this->toTree($albums);
        $filter = $this->Request()->getParam('albumFilter');

        if (!empty($filter)) {
            $albums = $this->filterAlbums($albums, $filter);
        }
        $this->View()->assign(['success' => true, 'data' => $albums, 'total' => \count($albums)]);
    }

    /**
     * Provides a way to download the original resource in the media manager. The
     * method sets the correct HTTP-Header to trigger the save dialog of the browser
     * and disables all available renderers.
     *
     * @return void
     */
    public function downloadAction()
    {
        Shopware()->Front()->Plugins()->ViewRenderer()->setNoRender();
        $this->Front()->Plugins()->Json()->setRenderer(false);

        $mediaId = $this->Request()->getParam('mediaId');
        $media = $this->getMedia($mediaId)->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);

        if (!$media) {
            echo self::FILE_NOT_FOUND_MESSAGE;

            return;
        }

        $file = $media['path'];
        $tmpFileName = sprintf('%s.%s', $media['name'], $media['extension']);
        $mediaService = $this->get(MediaServiceInterface::class);

        @set_time_limit(0);
        $response = $this->Response();
        $response->headers->set('cache-control', 'public', true);
        $response->headers->set('content-description', 'File Transfer');
        $response->headers->set('content-disposition', 'attachment; filename=' . $tmpFileName);
        $response->headers->set('content-transfer-encoding', 'binary');
        $response->headers->set('content-length', (string) $mediaService->getSize($file));
        echo $mediaService->read($file);
    }

    /**
     * The getAlbumMediaAction returns the associated media for the passed album id.
     * Is used for the listing of the media.
     * The media listing supports a filter, paging and order function, which can be controlled
     * by the parameters: "filter", "order", "limit", "start"
     *
     * @return void
     */
    public function getAlbumMediaAction()
    {
        $order = $this->prefixProperties($this->Request()->getParam('sort', []), 'media');
        $limit = $this->Request()->getParam('limit');
        $offset = $this->Request()->getParam('start');
        $filter = $this->Request()->getParam('filter');
        $filter = $filter[0]['value'];
        $albumID = $this->Request()->getParam('albumID');
        // Restrict to certain file types
        $validTypes = $this->Request()->getParam('validTypes', '');
        $validTypes = explode('|', $validTypes);

        if ($albumID === null || $albumID === 0) {
            // If no albumId is given load the unsorted album
            $albumID = self::UNSORTED_ALBUM_ID;
        }

        /** @var Repository $repository */
        $repository = $this->get(ModelManager::class)->getRepository(Media::class);
        $query = $repository->getAlbumMediaQuery($albumID, $filter, $order, $offset, $limit, $validTypes);

        $paginator = $this->getModelManager()->createPaginator($query);

        // Returns the total count of the query
        $totalResult = $paginator->count();

        $mediaList = $query->getResult(AbstractQuery::HYDRATE_ARRAY);
        $mediaService = $this->get(MediaServiceInterface::class);

        foreach ($mediaList as &$media) {
            $media['path'] = $mediaService->getUrl($media['path']);
            $media['virtualPath'] = $mediaService->normalize($media['path']);

            if (!\in_array($media['type'], [Media::TYPE_VECTOR, Media::TYPE_IMAGE], true)) {
                continue;
            }

            $media['thumbnail'] = $media['path'];

            if ($media['type'] === Media::TYPE_IMAGE) {
                $thumbnails = $this->getMediaThumbnailPaths($media);
                $availableThumbs = [];

                foreach ($thumbnails as $index => $thumbnail) {
                    if ($mediaService->has($thumbnail)) {
                        $availableThumbs[$index] = $mediaService->getUrl($thumbnail);
                    }
                }
                $media['thumbnails'] = $availableThumbs;

                if (!empty($availableThumbs['140x140'])) {
                    $media['thumbnail'] = $availableThumbs['140x140'];
                }
            }

            $media['timestamp'] = time();
        }

        $this->View()->assign(['success' => true, 'data' => $mediaList, 'total' => $totalResult]);
    }

    /**
     * The getMediaDetailAction returns the detail media data of the passed media id.
     * The detailed media data contains the following data: <br>
     * <code>
     *  id          => identifier
     *  albumID     => id of the associated album
     *  name        => media name
     *  description => detailed description of the media
     *  path        => path of the media file
     *  type        => internal media type (IMAGE, VIDEO, MUSIC, PDF, UNKNOWN, ARCHIVE)
     *  extension   => file extension (jpg, mp3, pdf, ...)
     *  userID      => id of the user, which uploaded the media
     *  created     => upload date
     * </code>
     *
     * @return void
     */
    public function getMediaDetailAction()
    {
        $params = $this->Request()->getParams();

        if (!isset($params['mediaID']) || empty($params['mediaID'])) {
            $this->View()->assign(['success' => false, 'message' => 'No valid media Id']);

            return;
        }

        $mediaID = $this->Request()->getParam('mediaID');
        $query = $this->getMedia($mediaID)->getQuery();
        /** @var Media[] $mediaArray */
        $mediaArray = $query->getResult();
        $data = $query->getArrayResult();
        $data = $data[0];
        $media = $mediaArray[0];

        if ($media->getType() === Media::TYPE_IMAGE) {
            $thumbnails = $media->getThumbnails();
            $data['path'] = $thumbnails['153x153'];
        }

        $this->View()->assign(['success' => true, 'data' => $data, 'total' => 1]);
    }

    /**
     * Controller action which allows to request the media
     * data over a mediaId parameter or over the path property of a media model.
     *
     * @retrun void
     */
    public function getMediaAction()
    {
        $id = $this->Request()->getParam(
            'mediaId',
            $this->Request()->getParam('id')
        );

        $path = $this->Request()->getParam(
            'path',
            $this->Request()->getParam('virtualPath')
        );

        if (empty($id) && empty($path)) {
            $this->View()->assign(['success' => false, 'error' => 'No id or path passed']);

            return;
        }

        $builder = $this->get('models')->createQueryBuilder();
        $builder->select(['media'])
            ->from(Media::class, 'media')
            ->setMaxResults(1);

        if (!empty($id)) {
            $builder->where('media.id = :id');
            $builder->setParameter('id', $id);
        } else {
            $builder->where('media.path = :path');
            $builder->setParameter('path', $path);
        }

        $data = $builder->getQuery()->getArrayResult();
        $data = $data[0];

        $mediaService = $this->get(MediaServiceInterface::class);
        if ($data['path']) {
            $data['path'] = $mediaService->getUrl($data['path']);
        }

        $this->View()->assign(['success' => true, 'data' => $data]);
    }

    /**
     * Removes the media from the file system and from the database.
     * Expects the media id. Throws an exception if the media id isn't passed or the media
     * can't be found.
     *
     * @return void
     */
    public function removeMediaAction()
    {
        $params = $this->Request()->getParams();
        if (!isset($params['id']) || empty($params['id'])) {
            $this->View()->assign(['success' => false, 'message' => 'No valid media Id']);
        }

        $id = $params['id'];

        // Load the media model
        $media = $this->get('models')->find(Media::class, $id);

        // Check if the media is loaded.
        if ($media === null || empty($media)) {
            $this->View()->assign(['success' => false, 'message' => sprintf('Media with id %s not found', $id)]);

            return;
        }

        // Try to remove the media and the uploaded files.
        try {
            $this->get('models')->remove($media);
            $this->get('models')->flush();
            $this->View()->assign(['success' => true]);
        } catch (ORMException $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * The removeAlbumAction function removes an album, which identified by the passed albumID parameter.
     * System albums (id < 0) can't be removed.
     *
     * @return void
     */
    public function removeAlbumAction()
    {
        $params = $this->Request()->getParams();

        // Batch processing!! albums
        if (isset($params['albums'])) {
            foreach ($params['albums'] as $album) {
                $this->removeAlbum($album);
            }

            return;
        }

        $this->removeAlbum($params);
    }

    /**
     * The uploadAction function is responsible for the uploading of media.
     * If no album id passed, the uploaded media is assigned to the unsorted album.
     *
     * @return void
     */
    public function uploadAction()
    {
        $params = $this->Request()->getParams();

        if (!$this->Request()->files->has('fileId')) {
            $this->View()->assign(['success' => false]);

            return;
        }

        // Try to get the transferred file
        try {
            /** @var UploadedFile $file */
            $file = $this->Request()->files->get('fileId');

            if (!$file->isValid()) {
                throw new Exception('The file exceeds the max file size.');
            }
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);

            return;
        }

        // Create a new model and set the properties
        $media = new Media();

        $albumId = !empty($params['albumID']) ? $params['albumID'] : self::UNSORTED_ALBUM_ID;
        /** @var Album|null $album */
        $album = $this->get('models')->find(Album::class, $albumId);

        if (!$album) {
            $this->View()->assign(['success' => false, 'message' => 'Invalid album id passed']);

            return;
        }

        $media->setAlbum($album);
        $media->setDescription('');
        $media->setCreated(new DateTime());

        $identity = $this->get('auth')->getIdentity();

        if ($identity !== null) {
            $media->setUserId($identity->id);
        } else {
            $media->setUserId(self::FALLBACK_USER_ID);
        }

        $this->Response()->headers->set('content-type', 'text/plain');

        try {
            // Set the upload file into the model. The model saves the file to the directory
            $media->setFile($file);

            // Persist the model into the model manager
            $this->get('models')->persist($media);
            $this->get('models')->flush();
            $data = $this->getMedia($media->getId())->getQuery()->getArrayResult();

            if ($media->getType() === Media::TYPE_IMAGE // GD doesn't support the following image formats
                && !\in_array($media->getExtension(), ['tif', 'tiff'], true)) {
                $manager = $this->get(Manager::class);
                $manager->createMediaThumbnail($media, [], true);
            }

            $mediaService = $this->get(MediaServiceInterface::class);
            $data[0]['path'] = $mediaService->getUrl($data[0]['path']);

            $this->View()->assign(['success' => true, 'data' => $data[0]]);
        } catch (Exception $e) {
            unlink($file->getPathname());

            $this->View()->assign(['success' => false, 'message' => $e->getMessage(), 'exception' => $this->parseExceptionForResponse($e)]);
        }
    }

    /**
     * The saveAlbumAction is used to save a new album and update an existing album.
     * The function expects the following parameters:
     * <code>
     *  id               => [int]    May be null if a new album to be saved.
     *  text             => [string] Name of the album to be saved.
     *  parentId         => [int]    Id of the parent album. 0 if the album is to be stored at the highest level.
     *  position         => [int]    Position of the album within the tree.
     *  iconCls          => [string] Css class for the album tree node
     *  createThumbnails => [int]    Flag if thumbnails should be created.
     *  thumbnailSize    => [array]  Array of thumbnail sizes
     * </code>
     *
     * @return void
     */
    public function saveAlbumAction()
    {
        $data = $this->Request()->getParams();
        $album = null;

        if (!empty($data['id'])) {
            $repo = $this->getManager()->getRepository(Media::class);
            $builder = $repo->getAlbumWithSettingsQueryBuilder($data['id']);

            $album = $builder->getQuery()->getOneOrNullResult(
                AbstractQuery::HYDRATE_OBJECT
            );

            if (!$album instanceof Album) {
                $this->View()->assign(['success' => false, 'message' => 'Invalid album id passed']);

                return;
            }
        }

        if (!$album instanceof Album) {
            $album = new Album();
            $this->getManager()->persist($album);
        }

        try {
            $data = $this->resolveAlbumData($data, $album);
            $album->fromArray($data);

            $this->getManager()->flush($album);
            $this->getManager()->flush($album->getSettings());

            $this->View()->assign(['success' => true, 'data' => ['id' => $album->getId()]]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Updates the meta information of a media. Handles the batch process and
     * the single process to save a media.
     * Each media can have the following parameters:
     * <code>
     *  - id          => required to identify the media
     *  - name        => name/alias of the media
     *  - newAlbumID  => To move the media into another album
     *  - description => detailed description of the media
     * </code>
     *
     * @return void
     */
    public function saveMediaAction()
    {
        // Get request params
        $params = $this->Request()->getParams();

        // Check for batch processing
        if (isset($params['media'])) {
            // Iterate passed media
            foreach ($params['media'] as $media) {
                $this->saveMedia($media);
            }

            return;
        }

        $this->saveMedia($params);
    }

    /**
     * This method creates thumbnails based on the request.
     *
     * @return void
     */
    public function createThumbnailsAction()
    {
        $offset = $this->Request()->getParam('offset');
        $limit = $this->Request()->getParam('limit');
        $albumId = $this->Request()->getParam('albumId');

        $medias = $this->getMediaForAlbumId($albumId, $offset, $limit);

        $settings = $this->getAlbumSettings($albumId);
        $thumbnailSizes = $settings->getThumbnailSize();

        if (empty($thumbnailSizes) || empty($thumbnailSizes[0])) {
            $this->View()->assign(['success' => false]);

            return;
        }

        /** @var Manager $manager */
        $manager = $this->get(Manager::class);

        $fails = [];
        foreach ($medias as $media) {
            try {
                $manager->createMediaThumbnail($media, $thumbnailSizes, true);
            } catch (Exception $e) {
                $fails[] = $e->getMessage();
            }
        }

        $this->View()->assign(['success' => true, 'total' => \count($medias) * \count($thumbnailSizes), 'fails' => $fails]);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws ORMInvalidArgumentException
     *
     * @return void
     */
    public function emptyTrashAction()
    {
        $em = $this->get(ModelManager::class);
        $query = $em->getRepository(Media::class)->getAlbumMediaQuery(Album::ALBUM_GARBAGE);
        $query->setHydrationMode(AbstractQuery::HYDRATE_OBJECT);

        foreach ($query->toIterable() as $key => $media) {
            $em->remove($media);
            if ($key % 100 === 0) {
                $em->flush();
                $em->clear();
            }
        }
        $em->flush();
        $em->clear();

        $this->View()->assign(['success' => true]);
    }

    /**
     * Generates virtual paths to full qualified urls in batch
     *
     * @return void
     */
    public function getMediaUrlsAction()
    {
        $mediaService = $this->get(MediaServiceInterface::class);
        $input = $this->Request()->get('paths');
        $output = [];

        foreach ($input as $url) {
            $output[] = $mediaService->getUrl($url);
        }

        $this->View()->assign([
            'success' => \count($input) > 0,
            'data' => $output,
        ]);
    }

    /**
     * @return void
     */
    public function singleReplaceAction()
    {
        $file = $this->Request()->files->get('file');
        $mediaId = $this->request->get('mediaId');

        $mediaReplaceService = $this->container->get(MediaReplaceServiceInterface::class);

        try {
            $mediaReplaceService->replace($mediaId, $file);
        } catch (Exception $exception) {
            unlink($file->getPathname());
            $this->View()->assign([
                'success' => false,
                'message' => $exception->getMessage(),
                'exception' => $this->parseExceptionForResponse($exception),
            ]);

            return;
        }

        $this->View()->assign(['success' => true]);
    }

    protected function initAcl()
    {
        // Read
        $this->addAclPermission('getAlbums', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getAlbumMedia', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getMediaDetail', 'read', 'Insufficient Permissions');
        // Delete
        $this->addAclPermission('removeMedia', 'delete', 'Insufficient Permissions');
        $this->addAclPermission('removeAlbum', 'delete', 'Insufficient Permissions');
        // Upload
        $this->addAclPermission('upload', 'upload', 'Insufficient Permissions');
        // Create
        $this->addAclPermission('saveAlbum', 'create', 'Insufficient Permissions');
        $this->addAclPermission('saveMedia', 'create', 'Insufficient Permissions');
    }

    /**
     * This method resolves the given request data and returns
     * an array with album data
     *
     * @param Album $album
     *
     * @throws Exception
     *
     * @return array
     */
    protected function resolveAlbumData(array $data, $album)
    {
        $settings = $album->getSettings();

        if (!$settings) {
            $settings = new Settings();
            $settings->setAlbum($album);
        }
        // Validate album name
        if (empty($data['text'])) {
            throw new Exception('No valid album name passed!');
        }
        $data['name'] = $data['text'];

        $data['parent'] = null;
        if (!empty($data['parentId']) && $data['parentId'] !== 'root') {
            $parent = $this->getManager()->find(Album::class, $data['parentId']);
            if (!$parent) {
                throw new Exception('No valid parent album passed!');
            }
            $data['parent'] = $parent;
        }

        $thumbnailSizes = '';
        $createThumbnails = 0;
        $icon = 'sprite-blue-folder';

        if (isset($data['createThumbnails'])) {
            $createThumbnails = (int) $data['createThumbnails'];
        }

        if (isset($data['thumbnailSize'])) {
            $thumbnailSizes = [];

            foreach ($data['thumbnailSize'] as $size) {
                if (!empty($size['value'])) {
                    $thumbnailSizes[] = str_replace(' ', '', $size['value']);
                }
            }
        }

        if (isset($data['iconCls']) && !empty($data['iconCls'])) {
            $icon = $data['iconCls'];
        }

        $thumbnailHighDpi = isset($data['thumbnailHighDpi']) && $data['thumbnailHighDpi'];
        $thumbnailQuality = $data['thumbnailQuality'] ?? 90;
        $thumbnailHighDpiQuality = $data['thumbnailHighDpiQuality'] ?? 70;

        $albumId = $album->getId();
        if (empty($albumId) && $data['parent'] !== null) {
            /** @var Settings $parentSettings */
            $parentSettings = $data['parent']->getSettings();

            $thumbnailSizes = $parentSettings->getThumbnailSize();
            $thumbnailHighDpi = $parentSettings->isThumbnailHighDpi();
            $thumbnailHighDpiQuality = $parentSettings->getThumbnailHighDpiQuality();
            $thumbnailQuality = $parentSettings->getThumbnailQuality();
            $createThumbnails = $parentSettings->getCreateThumbnails();
        }

        $settings->setCreateThumbnails($createThumbnails);
        $settings->setThumbnailSize(empty($thumbnailSizes) ? '' : $thumbnailSizes);
        $settings->setThumbnailHighDpi($thumbnailHighDpi);
        $settings->setThumbnailQuality($thumbnailQuality);
        $settings->setThumbnailHighDpiQuality($thumbnailHighDpiQuality);
        $settings->setIcon($icon);

        $data['settings'] = $settings;

        return $data;
    }

    /**
     * Helper method to prefix properties
     *
     * @param string $prefix
     *
     * @return array
     */
    protected function prefixProperties(array $properties = [], $prefix = '')
    {
        foreach ($properties as $key => $property) {
            if (isset($property['property'])) {
                $properties[$key]['property'] = $prefix . '.' . $property['property'];
            }
        }

        return $properties;
    }

    private function getManager(): ModelManager
    {
        if ($this->manager === null) {
            $this->manager = $this->get('models');
        }

        return $this->manager;
    }

    /**
     * Filters the loaded tree node with the passed filter value.
     *
     * @return array<int, array<string, mixed>>
     */
    private function filterAlbums(array $albums, string $search): array
    {
        $found = [];
        $foundChildren = [];

        foreach ($albums as $album) {
            if (stripos($album['text'], $search) === 0) {
                $found[] = $album;
            }
            $children = $album['data'] ?? null;

            if (\is_array($children) && \count($children) > 0) {
                $foundChildren[] = $this->filterAlbums($children, $search);
            }
        }

        return array_merge($found, ...$foundChildren);
    }

    /**
     * Returns all thumbnails paths according to the given media object
     *
     * @return array<string, string>
     */
    private function getMediaThumbnailPaths(array $media): array
    {
        if ($media['type'] !== Media::TYPE_IMAGE) {
            return [];
        }
        $sizes = ['140x140'];

        $album = $this->getManager()->find(Album::class, $media['albumId']);

        // Check if the album has loaded correctly.
        if ($album && $album->getSettings() && $album->getSettings()->getCreateThumbnails() === 1) {
            $sizes = array_merge($album->getSettings()->getThumbnailSize(), $sizes);
            $sizes = array_unique($sizes);
        }
        $thumbnails = [];

        // Iterate thumbnail sizes
        foreach ($sizes as $size) {
            if (strpos($size, 'x') === false) {
                $size = sprintf('%sx%s', $size, $size);
            }

            $projectDir = $this->container->getParameter('shopware.app.rootDir');

            if (!\is_string($projectDir)) {
                throw new RuntimeException('Parameter shopware.app.rootDir has to be an string');
            }

            $thumbnailDir = $projectDir . 'media' . DIRECTORY_SEPARATOR . strtolower($media['type']) . DIRECTORY_SEPARATOR . 'thumbnail' . DIRECTORY_SEPARATOR;
            $path = $thumbnailDir . $this->removeSpecialCharacters($media['name']) . '_' . $size . '.' . $media['extension'];

            $path = str_replace(Shopware()->DocPath(), '', $path);
            if (DIRECTORY_SEPARATOR !== '/') {
                $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
            }
            $thumbnails[(string) $size] = $path;
        }

        return $thumbnails;
    }

    /**
     * Removes special characters from a filename
     */
    private function removeSpecialCharacters(string $name): string
    {
        $name = iconv('utf-8', 'ascii//translit', $name);
        $name = preg_replace('#[^A-Za-z0-9\-_]#', '-', $name);
        $name = (string) preg_replace('#-{2,}#', '-', $name);
        $name = trim($name, '-');

        return mb_substr($name, 0, 180);
    }

    /**
     * Internal helper function to get a single media.
     */
    private function getMedia(int $id): QueryBuilder
    {
        $builder = $this->get('models')->createQueryBuilder();

        return $builder->select(['media'])
            ->from(Media::class, 'media')
            ->where('media.id = ?1')
            ->setParameter(1, $id);
    }

    /**
     * Internal helper function to handle batch processing to remove the albums.
     *
     * @param array<string, mixed> $params
     */
    private function removeAlbum(array $params): void
    {
        $albumId = (int) $params['albumID'];
        // Album id passed?
        if (empty($albumId)) {
            $this->View()->assign(['success' => false, 'message' => 'No valid album Id']);

            return;
        }

        // System albums have a negative id, such albums can't be deleted
        if ($albumId < 0) {
            $this->View()->assign(['success' => false, 'message' => 'System albums can not be deleted']);

            return;
        }

        $album = $this->get('models')->find(Album::class, $albumId);
        $repo = $this->get('models')->getRepository(Settings::class);
        $settings = $repo->findOneBy(['albumId' => $albumId]);

        // Album can't be found
        if (!$album instanceof Album) {
            $this->View()->assign(['success' => false, 'message' => 'Album not found']);

            return;
        }

        // Try to delete the album
        try {
            // Save the album id temporary
            $this->get('models')->remove($album);
            $this->get('models')->remove($settings);
            $this->get('models')->flush();

            $this->View()->assign(['success' => true]);
        } catch (ORMException $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Internal helper function to set the passed params to the media model and save the media.
     * Created to handle the batch processing.
     *
     * @param array<string, mixed> $params
     */
    private function saveMedia(array $params): void
    {
        if (isset($params['id']) && !empty($params['id']) && $params['id'] > 0) {
            /** @var Media|null $media */
            $media = $this->get('models')->find(Media::class, $params['id']);
        } else {
            $this->View()->assign(['success' => false, 'message' => 'No valid media Id']);

            return;
        }

        if (!$media instanceof Media) {
            $this->View()->assign(['success' => false, 'message' => 'Media not found']);

            return;
        }

        $oldName = $media->getName();
        $media->setName($params['name']);
        $name = $media->getName();
        $mediaService = $this->get(MediaServiceInterface::class);
        $projectDir = Shopware()->Container()->getParameter('shopware.app.rootDir');

        if (!\is_string($projectDir)) {
            throw new RuntimeException('Parameter shopware.app.rootDir has to be an string');
        }

        // Check if the name passed and is valid
        if (!empty($name)) {
            $path = 'media/' . strtolower($media->getType()) . '/' . $name . '.' . $media->getExtension();
            $path = $projectDir . $path;

            if ($name !== $oldName && $mediaService->has($path)) {
                $this->View()->assign(['success' => false, 'message' => 'Name already exist']);

                return;
            }
        } else {
            $media->setName($oldName);
        }

        // Check if a new album id is passed and is valid
        if (isset($params['newAlbumID']) && !empty($params['newAlbumID'])) {
            $album = $this->get(ModelManager::class)->getRepository(Album::class)->find($params['newAlbumID']);

            if ($album instanceof Album) {
                $media->setAlbum($album);
                $media->setAlbumId($params['newAlbumID']);
            }

            $this->createThumbnailsForMovedMedia($media);
        }

        // Check if the description is passed
        if (isset($params['description'])) {
            $media->setDescription($params['description']);
        }

        try {
            $this->get('models')->persist($media);
            $this->get('models')->flush();

            // Additional flush to save changes in postUpdate-Event
            $this->get('models')->flush();

            $data = $this->getMedia($media->getId())->getQuery()->getArrayResult();
            $this->View()->assign(['success' => true, 'data' => $data, 'total' => 1]);
        } catch (ORMException $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * @throws Exception
     */
    private function createThumbnailsForMovedMedia(Media $media): void
    {
        $albumRepository = $this->get(ModelManager::class)->getRepository(Album::class);

        $album = $albumRepository->find($media->getAlbumId());

        if ($album instanceof Album) {
            $media->removeAlbumThumbnails($album->getSettings()->getThumbnailSize(), $media->getFileName());
            $media->createAlbumThumbnails($album);
        }
    }

    /**
     * The internal toTree method iterates the given model and converts it into an array.
     * At the same time number of associated media is determined to the album.
     * If an album have sub-albums, the number of associated media of the sub-album added to the
     * media count of the parent album.
     *
     * @param array<string, mixed>|ArrayCollection $data
     * @param array<string, mixed>|null            $parent
     *
     * @return array<int, array<string, mixed>>
     */
    private function toTree($data, &$parent = null): array
    {
        $result = [];
        $count = 0;
        /** @var Album $element */
        foreach ($data as $element) {
            $node = $this->getAlbumNodeProperties($element);
            $result[] = $node;
            $count += $node['mediaCount'];
        }
        if (\is_array($parent)) {
            $parent['mediaCount'] += $count;
        }

        return $result;
    }

    /**
     * Converts the album properties into tree node properties.
     * If the album has sub-albums, iterates the children recursively.
     *
     * @return array<string, mixed>
     */
    private function getAlbumNodeProperties(Album $album): array
    {
        /** @var Repository $repository */
        $repository = $this->get(ModelManager::class)->getRepository(Media::class);
        $query = $repository->getAlbumMediaQuery($album->getId());

        // Returns the total count of the query
        $totalResult = $this->getModelManager()->createPaginator($query)->count();

        $parentId = null;

        if ($album->getParent()) {
            $parentId = $album->getParent()->getId();
        }

        $node = [
            'id' => $album->getId(),
            'text' => $album->getName(),
            'position' => $album->getPosition(),
            'garbageCollectable' => $album->getGarbageCollectable(),
            'mediaCount' => $totalResult,
            'parentId' => $parentId,
        ];

        // To get fresh album settings from new albums too
        $settingsQuery = $repository->getAlbumWithSettingsQuery($album->getId());
        $albumData = $settingsQuery->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);
        $settings = $albumData['settings'];

        if (!empty($settings)) {
            $node['iconCls'] = $settings['icon'];
            $node['createThumbnails'] = $settings['createThumbnails'];
            $node['thumbnailHighDpi'] = $settings['thumbnailHighDpi'];
            $node['thumbnailQuality'] = $settings['thumbnailQuality'];
            $node['thumbnailHighDpiQuality'] = $settings['thumbnailHighDpiQuality'];
            $thumbnails = explode(';', $settings['thumbnailSize']);
            $node['thumbnailSize'] = [];
            $count = \count($thumbnails);

            // Convert the thumbnail to an array width the index and value
            for ($i = 0; $i <= $count; ++$i) {
                if (empty($thumbnails[$i])) {
                    continue;
                }
                $node['thumbnailSize'][] = ['id' => $i, 'index' => $i, 'value' => $thumbnails[$i]];
            }
        }

        // Has sub-albums, then iterate and add the media count to the parent album.
        if (\count($album->getChildren()) > 0) {
            $node['data'] = $this->toTree($album->getChildren(), $node);
            $node['leaf'] = false;
        } else {
            $node['leaf'] = true;
        }

        return $node;
    }

    /**
     * @throws NonUniqueResultException
     *
     * @return Settings
     */
    private function getAlbumSettings(int $albumId): ?Settings
    {
        $builder = $this->getModelManager()->createQueryBuilder();

        $builder
            ->select(['settings'])
            ->from(Settings::class, 'settings')
            ->where('settings.albumId = :albumId')
            ->setParameter('albumId', $albumId);

        return $builder->getQuery()->getOneOrNullResult();
    }

    /**
     * @return Media[]
     */
    private function getMediaForAlbumId(int $albumId, int $offset, int $limit): array
    {
        $builder = $this->getModelManager()->createQueryBuilder();

        $builder
            ->select(['media'])
            ->from(Media::class, 'media')
            ->where('media.albumId = :albumId')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->setParameter('albumId', $albumId);

        return $builder->getQuery()->getResult();
    }

    /**
     * @return array<string, mixed>
     */
    private function parseExceptionForResponse(Exception $exception): array
    {
        return array_merge(
            json_decode(json_encode($exception), true),
            ['_class' => \get_class($exception)]
        );
    }
}
