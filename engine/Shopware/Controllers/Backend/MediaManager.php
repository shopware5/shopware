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

use Symfony\Component\HttpFoundation\File\UploadedFile as UploadedFile;
use Doctrine\ORM\AbstractQuery;
use Shopware\Models\Media\Album as Album;
use Shopware\Models\Media\Settings as Settings;
use Shopware\Models\Media\Media as Media;
use Shopware\Components\CSRFWhitelistAware;

/**
 * Shopware MediaManager Controller
 *
 * The media manager backend controller handles all actions around the media manager backend module
 * and the quick selection in other modules.
 *
 * @category  Shopware
 * @package   Shopware\Controllers\Backend
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Backend_MediaManager extends Shopware_Controllers_Backend_ExtJs implements CSRFWhitelistAware
{
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
        'exe'
    ];

    /**
     * Entity Manager
     * @var \Shopware\Components\Model\ModelManager
     */
    protected $manager = null;

    protected function initAcl()
    {
        // read
        $this->addAclPermission('getAlbums', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getAlbumMedia', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getMediaDetail', 'read', 'Insufficient Permissions');
        // delete
        $this->addAclPermission('removeMedia', 'delete', 'Insufficient Permissions');
        $this->addAclPermission('removeAlbum', 'delete', 'Insufficient Permissions');
        // upload
        $this->addAclPermission('upload', 'upload', 'Insufficient Permissions');
        // create
        $this->addAclPermission('saveAlbum', 'create', 'Insufficient Permissions');
        $this->addAclPermission('saveMedia', 'create', 'Insufficient Permissions');
    }

    /**
     * @inheritdoc
     */
    public function getWhitelistedCSRFActions()
    {
        return [
            'download'
        ];
    }

    /**
     * Internal helper function to get access to the entity manager.
     * @return \Shopware\Components\Model\ModelManager
     */
    private function getManager()
    {
        if ($this->manager === null) {
            $this->manager = Shopware()->Models();
        }
        return $this->manager;
    }

    /**
     * Enable json renderer for index / load action
     * Check acl rules
     *
     * @return void
     */
    public function preDispatch()
    {
        if ($this->Request()->getActionName() !== 'upload') {
            parent::preDispatch();
        }
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
        $builder = Shopware()->Models()->createQueryBuilder();
        $albumId = $this->Request()->getParam('albumId', null);

        $builder->select(array('album'))
                ->from('Shopware\Models\Media\Album', 'album')
                ->where('album.parentId IS NULL')
                ->orderBy('album.position', 'ASC');

        if (!empty($albumId)) {
            if (strpos($albumId, ',') !== false) {
                $albumId = explode(',', $albumId);
            } else {
                $albumId = array($albumId);
            }
            $builder->andWhere('album.id IN(:albumId)')
                    ->setParameter('albumId', $albumId);
        }

        $albums = $builder->getQuery()->getResult();
        $albums = $this->toTree($albums);
        $filter = $this->Request()->albumFilter;
        if (!empty($filter)) {
            $albums = $this->filterAlbums($albums, $filter);
        }
        $this->View()->assign(array('success' => true, 'data' => $albums, 'total' => count($albums)));
    }

    /**
     * Filters the loaded tree node with the passed filter value.
     *
     * @param $albums
     * @param $search
     * @return array
     */
    private function filterAlbums($albums, $search)
    {
        $founded = array();

        /** @var $album \Shopware\Models\Media\Album */
        foreach ($albums as $album) {
            if (stripos($album['text'], $search) === 0) {
                $founded[] = $album;
            }
            $children = $album['data'];
            if (count($children) > 0) {
                $childrenFounded = $this->filterAlbums($children, $search);
                $founded = array_merge($founded, $childrenFounded);
            }
        }
        return $founded;
    }

    /**
     * Provides a way to download the original resource in the media manager. The
     * method sets the correct HTTP-Header to trigger the save dialog of the browser
     * and disables all available renderer's.
     *
     * @return void
     */
    public function downloadAction()
    {
        Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
        $this->Front()->Plugins()->Json()->setRenderer(false);

        $mediaId = $this->Request()->getParam('mediaId');
        $media = $this->getMedia($mediaId)->getQuery()->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        if (!$media) {
            echo 'file not found';
            return;
        }

        $file = $media['path'];
        $tmpFileName = $media['name'] . '.' . $media['extension'];
        $mediaService = $this->get('shopware_media.media_service');

        @set_time_limit(0);
        $response = $this->Response();
        $response->setHeader('Cache-Control', 'public');
        $response->setHeader('Content-Description', 'File Transfer');
        $response->setHeader('Content-disposition', 'attachment; filename=' . $tmpFileName);
        $response->setHeader('Content-Transfer-Encoding', 'binary');
        $response->setHeader('Content-Length', $mediaService->getSize($file));
        print $mediaService->read($file);
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
        $order = $this->prefixProperties($this->Request()->getParam('sort', array()), 'media');
        $limit = $this->Request()->getParam('limit');
        $offset = $this->Request()->getParam('start');
        $filter = $this->Request()->filter;
        $filter = $filter[0]["value"];
        $albumID = $this->Request()->getParam('albumID');
        // Restrict to certain file types
        $validTypes = $this->Request()->getParam('validTypes');
        if (!empty($validTypes)) {
            $validTypes = explode("|", $validTypes);
        } else {
            $validTypes = array();
        }

        if ($albumID === null || $albumID === 0) {
            //if no albumId is given load the unsorted album
            $albumID = -10;
        }

        /** @var $repository \Shopware\Models\Media\Repository */
        $repository = Shopware()->Models()->getRepository(Media::class);
        $query = $repository->getAlbumMediaQuery($albumID, $filter, $order, $offset, $limit, $validTypes);

        $paginator = $this->getModelManager()->createPaginator($query);

        //returns the total count of the query
        $totalResult = $paginator->count();

        $mediaList = $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        $mediaService = $this->get('shopware_media.media_service');

        /** @var $media Media */
        foreach ($mediaList as &$media) {
            $media['path'] = $mediaService->getUrl($media['path']);
            $media['virtualPath'] = $mediaService->normalize($media['path']);

            if ($media['type'] !== Media::TYPE_IMAGE) {
                continue;
            }

            $thumbnails = $this->getMediaThumbnailPaths($media);

            foreach ($thumbnails as $index => $thumbnail) {
                $thumbnails[$index] = $thumbnail;
            }

            if (!empty($thumbnails) && $mediaService->has($thumbnails['140x140'])) {
                $media['thumbnail'] = $mediaService->getUrl($thumbnails['140x140']);
            }
        }

        $this->View()->assign(array('success' => true, 'data' => $mediaList, 'total' => $totalResult));
    }

    /**
     * Returns all thumbnails paths according to the given media object
     *
     * @param $media
     * @return array
     */
    private function getMediaThumbnailPaths($media)
    {
        if ($media['type'] !== Media::TYPE_IMAGE) {
            return array();
        }
        $sizes = array('140x140');

        $album = $this->getManager()->find('Shopware\Models\Media\Album', $media['albumId']);

        //Check if the album has loaded correctly.
        if ($album && $album->getSettings() && $album->getSettings()->getCreateThumbnails() === 1) {
            $sizes = array_merge($album->getSettings()->getThumbnailSize(), $sizes);
            $sizes = array_unique($sizes);
        }
        $thumbnails = array();

        //iterate thumbnail sizes
        foreach ($sizes as $size) {
            if (strpos($size, 'x') === false) {
                $size = $size . 'x' . $size;
            }

            $thumbnailDir = Shopware()->DocPath('media_' . strtolower($media['type'])) . 'thumbnail' . DIRECTORY_SEPARATOR;
            $path = $thumbnailDir . $this->removeSpecialCharacters($media['name']) . '_' . $size . '.' . $media['extension'];

            $path = str_replace(Shopware()->DocPath(), '', $path);
            if (DIRECTORY_SEPARATOR !== '/') {
                $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
            }
            $thumbnails[$size] = $path;
        }
        return $thumbnails;
    }

    /**
     * Removes special characters from a filename
     *
     * @param $name
     * @return string
     */
    private function removeSpecialCharacters($name)
    {
        $name = iconv('utf-8', 'ascii//translit', $name);
        $name = preg_replace('#[^A-z0-9\-_]#', '-', $name);
        $name = preg_replace('#-{2,}#', '-', $name);
        $name = trim($name, '-');
        return mb_substr($name, 0, 180);
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
     * @return void
     */
    public function getMediaDetailAction()
    {
        $params = $this->Request()->getParams();
        if (!isset($params['mediaID']) || empty($params['mediaID'])) {
            $this->View()->assign(array('success' => false, 'message' => 'No valid media Id'));
            return;
        }

        $mediaID = $this->Request()->getParam('mediaID');
        $query = $this->getMedia($mediaID)->getQuery();
        $media = $query->getResult();
        $data = $query->getArrayResult();
        $data = $data[0];
        $media = $media[0];

        if ($media->getType() === Media::TYPE_IMAGE) {
            $thumbnails = $media->getThumbnails();
            $data['path'] = $thumbnails['153x153'];
        }

        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => 1));
    }


    /**
     * Controller action which allows to request the media
     * data over a mediaId parameter or over the path property of a media model.
     */
    public function getMediaAction()
    {
        $id = $this->Request()->getParam(
            'mediaId',
            $this->Request()->getParam('id', null)
        );

        $path = $this->Request()->getParam('path', null);

        if (empty($id) && empty($path)) {
            $this->View()->assign(array('success' => false, 'error' => 'No id or path passed'));
            return;
        }

        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('media'))
               ->from('Shopware\Models\Media\Media', 'media')
               ->setMaxResults(1);

        if (!empty($id)) {
            $builder->where('media.id = :id');
            $builder->setParameter('id', $id);
        } elseif (!empty($path)) {
            $builder->where('media.path = :path');
            $builder->setParameter('path', $path);
        }

        $data = $builder->getQuery()->getArrayResult();
        $data = $data[0];

        $mediaService = $this->get('shopware_media.media_service');
        if ($data['path']) {
            $data['path'] = $mediaService->getUrl($data['path']);
        }

        $this->View()->assign(array('success' => true, 'data' => $data));
    }

    /**
     * Internal helper function to get a single media.
     * @param integer $id
     * @return Doctrine\ORM\QueryBuilder
     */
    private function getMedia($id)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        return $builder->select(array('media'))
                ->from('Shopware\Models\Media\Media', 'media')
                ->where('media.id = ?1')
                ->setParameter(1, $id);
    }

    /**
     * Removes the media from the file system and from the database.
     * Expects the media id. Throws an exception if the media id isn't passed or the media
     * can't be find.
     *
     * @return bool
     */
    public function removeMediaAction()
    {
        $params = $this->Request()->getParams();
        if (!isset($params['id']) || empty($params['id'])) {
            $this->View()->assign(array('success' => false, 'message' => 'No valid media Id'));
        }
        $id = $params["id"];

        //load the media model
        $media = Shopware()->Models()->find('Shopware\Models\Media\Media', $id);

        //check if the media is loaded.
        if ($media === null || empty($media)) {
            $this->View()->assign(array('success' => false, 'message' => 'Media not found'));
            return true;
        }

        //try to remove the media and the uploaded files.
        try {
            Shopware()->Models()->remove($media);
            Shopware()->Models()->flush();
            $this->View()->assign(array('success' => true));
        } catch (\Doctrine\ORM\ORMException $e) {
            $this->View()->assign(array('success' => false, 'message' => $e->getMessage()));
        }
    }

    /**
     * The removeAlbumAction function removes an album, which identified by the passed albumID parameter.
     * System albums (id < 0) can't be removed.
     *
     * @return bool
     */
    public function removeAlbumAction()
    {
        $params = $this->Request()->getParams();

        //batch processing!! albums
        if (isset($params['albums'])) {
            foreach ($params['albums'] as $album) {
                $this->removeAlbum($album);
            }
        } else {
            $this->removeAlbum($params);
        }
    }

    /**
     * Internal helper function to handle batch processing to remove the albums.
     * @param $params
     * @return bool
     */
    private function removeAlbum($params)
    {
        $albumId = (int) $params['albumID'];
        //album id passed?
        if (!isset($albumId) || empty($albumId)) {
            $this->View()->assign(array('success' => false, 'message' => 'No valid album Id'));
            return false;
        }

        //system albums have a negative id, such albums can't be deleted
        if ($albumId < 0) {
            $this->View()->assign(array('success' => false, 'message' => 'System albums can not be deleted'));
            return false;
        }

        /** @var $album \Shopware\Models\Media\Album */
        $album = Shopware()->Models()->find('Shopware\Models\Media\Album', $albumId);
        $repo = Shopware()->Models()->getRepository('Shopware\Models\Media\Settings');
        $settings = $repo->findOneBy(array('albumId' => $albumId));

        //album can't be founded
        if ($album === null || empty($album)) {
            $this->View()->assign(array('success' => false, 'message' => 'Album not found'));
            return false;
        }

        //try to delete the album
        try {
            //save the album id temporary
            Shopware()->Models()->remove($album);
            Shopware()->Models()->remove($settings);
            Shopware()->Models()->flush();

            $this->View()->assign(array('success' => true));
        } catch (\Doctrine\ORM\ORMException $e) {
            $this->View()->assign(array('success' => false, 'message' => $e->getMessage()));
        }
    }

    /**
     * The uploadAction function is responsible for the uploading of media.
     * If no album id passed, the uploaded media is assigned to the unsorted album.
     *
     * @throws Exception
     * @return bool
     */
    public function uploadAction()
    {
        $params = $this->Request()->getParams();

        //try to get the transferred file
        try {
            $file = $_FILES['fileId'];

            if ($file['size'] < 1 && $file['error'] === 1 || empty($_FILES)) {
                throw new Exception("The file exceeds the max file size.");
            }

            $fileInfo = pathinfo($file['name']);
            $fileExtension = strtolower($fileInfo['extension']);
            $file['name'] = $fileInfo['filename'] . "." . $fileExtension;
            $_FILES['fileId']['name'] = $file['name'];

            $fileBag = new \Symfony\Component\HttpFoundation\FileBag($_FILES);

            /** @var $file UploadedFile */
            $file = $fileBag->get('fileId');
        } catch (Exception $e) {
            die(json_encode(array('success' => false, 'message' => $e->getMessage())));
        }
        if ($file === null) {
            die(json_encode(array('success' => false)));
        }

        $fileInfo = pathinfo($file->getClientOriginalName());
        $extension = $fileInfo['extension'];
        if (in_array(strtolower($extension), static::$fileUploadBlacklist)) {
            unlink($file->getPathname());
            unlink($file);
            die(json_encode(array('success' => false, 'blacklist' => true, 'extension' => $extension)));
        }

        //create a new model and set the properties
        $media = new Media();

        $albumId = !empty($params['albumID']) ? $params['albumID'] : -10;
        $album = Shopware()->Models()->find('Shopware\Models\Media\Album', $albumId);

        if (!$album) {
            $this->View()->assign(array('success' => false, 'message' => 'Invalid album id passed'));
            return;
        }

        $media->setAlbum($album);
        $media->setDescription('');
        $media->setCreated(new DateTime());

        $identity = $this->get('Auth')->getIdentity();
        if ($identity !== null) {
            $media->setUserId($identity->id);
        } else {
            $media->setUserId(0);
        }

        //set the upload file into the model. The model saves the file to the directory
        $media->setFile($file);

        try { //persist the model into the model manager
            Shopware()->Models()->persist($media);
            Shopware()->Models()->flush();
            $data = $this->getMedia($media->getId())->getQuery()->getArrayResult();

            if ($media->getType() === Media::TYPE_IMAGE) {
                $manager = $this->get('thumbnail_manager');
                $manager->createMediaThumbnail($media, array(), true);
            }

            $mediaService = $this->get('shopware_media.media_service');
            $data[0]['path'] = $mediaService->getUrl($data[0]['path']);

            $this->Response()->setHeader('Content-Type', 'text/plain');

            die(json_encode(array('success' => true, 'data' => $data[0])));
        } catch (\Doctrine\ORM\ORMException $e) {
            die(json_encode(array('success' => false, 'message' => $e->getMessage())));
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
     * @throws Exception
     * @return bool
     */
    public function saveAlbumAction()
    {
        $data = $this->Request()->getParams();

        if (!empty($data['id'])) {
            $repo = $this->getManager()->getRepository('Shopware\Models\Media\Media');
            $builder = $repo->getAlbumWithSettingsQueryBuilder($data['id']);

            $album = $builder->getQuery()->getOneOrNullResult(
                \Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT
            );
            if (!$album) {
                $this->View()->assign(['success' => false, 'message' => 'Invalid album id passed']);
                return false;
            }
        } else {
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
     * This method resolves the given request data and returns
     * an array with album data
     *
     * @param $data
     * @param Album $album
     * @throws Exception
     * @return array|bool
     */
    protected function resolveAlbumData($data, $album)
    {
        $settings = $album->getSettings();

        if (!$settings) {
            $settings = new Settings();
            $settings->setAlbum($album);
        }
        // validate album name
        if (empty($data['text'])) {
            throw new Exception('No valid album name passed!');
        }
        $data['name'] = $data['text'];

        $data['parent'] = null;
        if (!empty($data['parentId']) && $data['parentId'] != 'root') {
            $parent = $this->getManager()->find('Shopware\Models\Media\Album', $data['parentId']);
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
            $thumbnailSizes = array();

            foreach ($data['thumbnailSize'] as $size) {
                if (!empty($size['value']) && $size['value'] !== '') {
                    $thumbnailSizes[] = $size['value'];
                }
            }
        }

        if (isset($data['iconCls']) && !empty($data['iconCls'])) {
            $icon = $data['iconCls'];
        }

        $thumbnailHighDpi = (isset($data['thumbnailHighDpi']) && $data['thumbnailHighDpi']);
        $thumbnailQuality = $data['thumbnailQuality'] ? : 90;
        $thumbnailHighDpiQuality = $data['thumbnailHighDpiQuality'] ? : 70;

        $albumId = $album->getId();
        if (empty($albumId) && $data['parent'] !== null) {
            /** @var Settings $parentSettings */
            $parentSettings = $data['parent']->getSettings();

            $thumbnailSizes = $parentSettings->getThumbnailSize();
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
     * Updates the meta information of a media. Handles the batch process and
     * the single process to save a media.
     * Each media can have the following parameters:
     * <code>
     *  - id          => required to identify the media
     *  - name        => name/alias of the media
     *  - newAlbumID  => To move the media into another album
     *  - description => detailed description of the media
     * </code>
     */
    public function saveMediaAction()
    {
        //get request params
        $params = $this->Request()->getParams();

        //check for batch processing
        if (isset($params['media'])) {
            //iterate passed media
            foreach ($params['media'] as $media) {
                $this->saveMedia($media);
            }
        } else {
            $this->saveMedia($params);
        }
    }

    /**
     * Internal helper function to set the passed params to the media model and save the media.
     * Created to handle the batch processing.
     *
     * @param $params
     * @return mixed
     */
    private function saveMedia($params)
    {
        /** @var $media Shopware\Models\Media\Media */
        if (isset($params['id']) && !empty($params['id']) && $params['id'] > 0) {
            $media = Shopware()->Models()->find('Shopware\Models\Media\Media', $params['id']);
        } else {
            $this->View()->assign(array('success' => false, 'message' => 'No valid media Id'));
            return;
        }

        if ($media === null) {
            $this->View()->assign(array('success' => false, 'message' => 'Media not found'));
            return;
        }

        $oldName = $media->getName();
        $media->setName($params['name']);
        $name = $media->getName();
        $mediaService = $this->get('shopware_media.media_service');

        //check if the name passed and is valid
        if (!empty($name)) {
            $path = 'media/' . strtolower($media->getType()) . '/' .   $name . '.' . $media->getExtension();
            $path = Shopware()->DocPath() . $path;

            if ($mediaService->has($path) && $name !== $oldName) {
                $this->View()->assign(array('success' => false, 'message' => 'Name already exist'));
                return;
            }
        } else {
            $media->setName($oldName);
        }

        //check if the album id passed and is valid
        if (isset($params['newAlbumID']) && !empty($params['newAlbumID'])) {
            $media->setAlbumId($params['newAlbumID']);
        }

        //check if the description is passed
        if (isset($params['description'])) {
            $media->setDescription($params['description']);
        }

        try {
            Shopware()->Models()->persist($media);
            Shopware()->Models()->flush();

            // Additional flush to save changes in postUpdate-Event
            Shopware()->Models()->flush();

            $data = $this->getMedia($media->getId())->getQuery()->getArrayResult();
            $this->View()->assign(array('success' => true, 'data' => $data, 'total' => 1));
        } catch (\Doctrine\ORM\ORMException $e) {
            $this->View()->assign(array('success' => false, 'message' => $e->getMessage()));
        }
    }

    /**
     * The internal toTree method iterates the given model and converts it into an array.
     * At the same time number of associated media is determined to the album.
     * If an album have sub-albums, the number of associated media of the sub-album added to the
     * media count of the parent album.
     *
     * @param      $data
     * @param null $parent
     * @return array
     */
    private function toTree($data, &$parent = null)
    {
        $result = array();
        $count = 0;
        /** @var $element \Shopware\Models\Media\Album */
        foreach ($data as $element) {
            $node = $this->getAlbumNodeProperties($element);
            $result[] = $node;
            $count += $node['mediaCount'];
        }
        $parent['mediaCount'] += $count;
        return $result;
    }

    /**
     * Converts the album properties into tree node properties.
     * If the album has sub-albums, iterates the children recursively.
     *
     * @param Shopware\Models\Media\Album $album
     * @return array
     */
    private function getAlbumNodeProperties(\Shopware\Models\Media\Album $album)
    {
        /** @var $repository \Shopware\Models\Media\Repository */
        $repository = Shopware()->Models()->getRepository(Media::class);
        $query = $repository->getAlbumMediaQuery($album->getId());

        $paginator = $this->getModelManager()->createPaginator($query);

        //returns the total count of the query
        $totalResult = $paginator->count();

        $parentId = null;
        if ($album->getParent()) {
            $parentId = $album->getParent()->getId();
        }

        $node = array(
            'id' => $album->getId(),
            'text' => $album->getName(),
            'position' => $album->getPosition(),
            'mediaCount' => $totalResult,
            'parentId' => $parentId
        );

        //to get fresh album settings from new albums too
        $settingsQuery = $repository->getAlbumWithSettingsQuery($album->getId());
        $albumData = $settingsQuery->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        $settings = $albumData["settings"];

        if (!empty($settings) && $settings !== null) {
            $node["iconCls"] = $settings["icon"];
            $node["createThumbnails"] = $settings["createThumbnails"];
            $node["thumbnailHighDpi"] = $settings["thumbnailHighDpi"];
            $node["thumbnailQuality"] = $settings["thumbnailQuality"];
            $node["thumbnailHighDpiQuality"] = $settings["thumbnailHighDpiQuality"];
            $thumbnails = explode(";", $settings["thumbnailSize"]);
            $node["thumbnailSize"] = array();
            $count = count($thumbnails);

            //convert the thumbnail to an array width the index and value
            for ($i = 0; $i <= $count; $i++) {
                if ($thumbnails[$i] === '' || $thumbnails[$i] === null) {
                    continue;
                }
                $node["thumbnailSize"][] = array('id' => $i, 'index' => $i, 'value' => $thumbnails[$i]);
            }
        }

        //has sub-albums, then iterate and add the media count to the parent album.
        if (count($album->getChildren()) > 0) {
            $node['data'] = $this->toTree($album->getChildren(), $node);
            $node['leaf'] = false;
        } else {
            $node['leaf'] = true;
        }

        return $node;
    }

    /**
     * Helper method to prefix properties
     *
     * @param array $properties
     * @param string $prefix
     * @return array
     */
    protected function prefixProperties($properties = array(), $prefix = '')
    {
        foreach ($properties as $key => $property) {
            if (isset($property['property'])) {
                $properties[$key]['property'] = $prefix . '.' . $property['property'];
            }
        }

        return $properties;
    }

    /**
     * This method creates thumbnails based on the request.
     */
    public function createThumbnailsAction()
    {
        $offset  = $this->Request()->getParam('offset');
        $limit   = $this->Request()->getParam('limit');
        $albumId = $this->Request()->getParam('albumId');

        $medias = $this->getMediaForAlbumId($albumId, $offset, $limit);

        $settings = $this->getAlbumSettings($albumId);
        $thumbnailSizes = $settings->getThumbnailSize();

        if (empty($thumbnailSizes) || empty($thumbnailSizes[0])) {
            $this->View()->assign(array('success' => false));
            return;
        }

        /** @var $manager Shopware\Components\Thumbnail\Manager **/
        $manager = $this->get('thumbnail_manager');

        $fails = array();
        foreach ($medias as $media) {
            try {
                $manager->createMediaThumbnail($media, $thumbnailSizes, true);
            } catch (Exception $e) {
                $fails[] = $e->getMessage();
            }
        }

        $this->View()->assign(array('success' => true, 'total' => count($medias) * count($thumbnailSizes), 'fails' => $fails));
    }

    /**
     * @param integer $albumId
     * @return Settings
     */
    private function getAlbumSettings($albumId)
    {
        $builder = $this->getModelManager()->createQueryBuilder();

        $builder
            ->select(array('settings'))
            ->from('Shopware\Models\Media\Settings', 'settings')
            ->where('settings.albumId = :albumId')
            ->setParameter('albumId', $albumId);

        return $builder->getQuery()->getOneOrNullResult();
    }

    /**
     * @param integer $albumId
     * @param integer $offset
     * @param integer $limit
     * @return Media[]
     */
    private function getMediaForAlbumId($albumId, $offset, $limit)
    {
        $builder = $this->getModelManager()->createQueryBuilder();

        $builder
            ->select(array('media'))
            ->from('Shopware\Models\Media\Media', 'media')
            ->where('media.albumId = :albumId')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->setParameter('albumId', $albumId);

        return $builder->getQuery()->getResult();
    }

    /**
     * Empty albumId -13
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function emptyTrashAction()
    {
        /** @var \Shopware\Components\Model\ModelManager $em */
        $em = $this->get('models');
        /** @var \Shopware\Models\Media\Repository $repository */
        $repository = $em->getRepository(Media::class);

        $query = $repository->getAlbumMediaQuery(-13);
        $query->setHydrationMode(AbstractQuery::HYDRATE_OBJECT);

        $iterableResult = $query->iterate();
        foreach ($iterableResult as $key => $row) {
            $media = $row[0];
            $em->remove($media);
            if ($key % 100 == 0) {
                $em->flush();
                $em->clear();
            }
        }
        $em->flush();
        $em->clear();

        $this->View()->assign(array('success' => true));
    }

    /**
     * Generates virtual paths to full qualified urls in batch
     */
    public function getMediaUrlsAction()
    {
        $mediaService = $this->get('shopware_media.media_service');
        $input = $this->Request()->get('paths');
        $output = [];

        foreach ($input as $url) {
            $output[] = $mediaService->getUrl($url);
        }

        $this->View()->assign([
            'success' => count($input) > 0,
            'data' => $output
        ]);
    }
}
