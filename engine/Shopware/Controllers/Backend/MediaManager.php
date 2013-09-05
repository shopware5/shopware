<?php
/**
 * Shopware 4.0
 * Copyright © 2013 shopware AG
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

use DoctrineExtensions\Paginate\Paginate;
use Symfony\Component\HttpFoundation\File\UploadedFile as UploadedFile;
use Shopware\Models\Media\Album as Album;
use Shopware\Models\Media\Settings as Settings;
use Shopware\Models\Media\Media as Media;
/**
 * Shopware MediaManager Controller
 *
 * The media manager backend controller handles all actions around the media manager backend module
 * and the quick selection in other modules.
 *
 * @category  Shopware
 * @package   Shopware\Controllers\Backend
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Backend_MediaManager extends Shopware_Controllers_Backend_ExtJs
{

    protected $blackList = array(
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
    );

    /**
     * Entity Manager
     * @var null
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
     * Internal helper function to get access to the entity manager.
     * @return null
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
     * Unlike the other Shopware 4 backend controller actions, this action uses the standard method "find".
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

        @set_time_limit(0);
        $response = $this->Response();
        $response->setHeader('Cache-Control', 'public');
        $response->setHeader('Content-Description', 'File Transfer');
        $response->setHeader('Content-disposition', 'attachment; filename=' . $tmpFileName);
        $response->setHeader('Content-Transfer-Encoding', 'binary');
        $response->setHeader('Content-Length', filesize($file));
        readfile($file);
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
        $repository = Shopware()->Models()->Media();
        $query = $repository->getAlbumMediaQuery($albumID, $filter, $order, $offset, $limit, $validTypes);

        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query);

        //returns the total count of the query
        $totalResult = $paginator->count();
        //returns the customer data
        $media = $query->getResult();
        $mediaData = $query->getArrayResult();
        $images = array();

        /** @var $image \Shopware\Models\Media\Media */
        for ($i = 0; $i <= count($media) - 1; $i++) {
            $image = $media[$i];
            $data = $mediaData[$i];
            if ($image->getType() === Media::TYPE_IMAGE) {
                $size = getimagesize($image->getPath());
                $thumbnails = $image->getThumbnails();
                $data['thumbnail'] = $thumbnails['140x140'];
                $data['width'] = $size[0];
                $data['height'] = $size[1];
            }
            $images[] = $data;
        }

        $this->View()->assign(array('success' => true, 'data' => $images, 'total' => $totalResult));
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


    public function getMediaAction()
    {
        $id = $this->Request()->getParam('mediaId', null);

        if (empty($id)) {
            $this->View()->assign(array('success' => false, 'error' => 'No id passed'));
        }
        $builder = $this->getMedia($id);
        $data = $builder->getQuery()->getArrayResult();

        $this->View()->assign(array('success' => true, 'data' => $data[0]));
    }

    /**
     * Internal helper function to get a single media.
     * @param integer $id
     * @return Doctrine\ORM\QueryBuilder
     */
    private function getMedia($id)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        return $builder->select(array('media', 'attribute'))
                ->from('Shopware\Models\Media\Media', 'media')
                ->leftJoin('media.attribute', 'attribute')
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
        if (in_array(strtolower($extension), $this->blackList)) {
            unlink($file->getPathname());
            unlink($file);
            die(json_encode(array('success' => false, 'blacklist' => true, 'extension' => $extension)));
        }

        //create a new model and set the properties
        $media = new Media();
        $params['attribute'] = $params['attribute'][0];

        //set media album, if no one passed set the unsorted album.
        if (isset($params['albumID'])) {
            $media->setAlbumId($params['albumID']);
            $media->setAlbum(Shopware()->Models()->find('Shopware\Models\Media\Album', $params['albumID']));
        } else {
            $media->setAlbumId(-10);
            $media->setAlbum(Shopware()->Models()->find('Shopware\Models\Media\Album', -10));
        }

        $media->setDescription('');
        $media->setCreated(new DateTime());

        $identity = Shopware()->Auth()->getIdentity();
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
     *  thumbnailSize    => [int]    Flag if thumbnails should be created.
     *  createThumbnails => [array]  Array of thumbnail sizes
     * </code>
     *
     * @return bool
     */
    public function saveAlbumAction()
    {
        $params = $this->Request()->getParams();

        //check if the album id passed. If no id passed, create a new album and album settings.
        if (isset($params['id']) && !empty($params['id']) && $params['id'] !== 0) {
            $album = Shopware()->Models()->find('Shopware\Models\Media\Album', $params['id']);
            $settings = $album->getSettings();
        } else {
            $album = new Album();
            $settings = new Settings();
            $settings->setCreateThumbnails(0);
            $settings->setIcon('sprite-blue-folder');
            $settings->setThumbnailSize('');
        }

        //check if the album is loaded.
        if ($album === null || $settings === null) {
            $this->View()->assign(array('success' => false, 'message' => 'Album/Settings not found'));
            return false;
        }

        //validate name
        if (isset($params['text']) && !empty($params['text'])) {
            $album->setName($params['text']);
        } else {
            $this->View()->assign(array('success' => false, 'message' => 'No name passed!'));
            return false;
        }

        //validate parent ID
        if (isset($params['parentId']) && !empty($params['parentId']) && $params['parentId'] !== 0 && $params['parentId'] !== 'root') {
            $parentId = $params['parentId'];
        } else {
            $parentId = null;
        }

        //validate position
        if (isset($params['position']) && !empty($params['position'])) {
            if ($album->getId() !== 0) {
                $this->changePosition($album, $params['position'], $parentId);
            }
            $album->setPosition($params['position']);
        } else {
            //get last position + 1
            if ($parentId === null) {
                $sql = "SELECT MAX(position) + 1 FROM s_media_album WHERE parentID IS NULL";
                $position = Shopware()->Db()->fetchOne($sql);
            } else {
                $sql = "SELECT MAX(position) + 1 FROM s_media_album WHERE parentID = ?";
                $position = Shopware()->Db()->fetchOne($sql, array($parentId));
            }
            if ($position === null) {
                $position = 1;
            }
            $album->setPosition($position);
        }

        //Important! Parent must be set only after updating the position.
        $album->setParentId($parentId);

        //validate icon property of the album settings
        if (isset($params['iconCls']) && !empty($params['iconCls'])) {
            $settings->setIcon($params['iconCls']);
        }

        //validate create thumbnail property of the album settings
        if (isset($params['createThumbnails'])) {
            $settings->setCreateThumbnails($params['createThumbnails']);
        }

        //validate thumbnail size property of the album settings
        if (isset($params['thumbnailSize'])) {
            $sizes = array();
            foreach ($params['thumbnailSize'] as $size) {
                if (!empty($size['value']) && $size['value'] !== '') {
                    $sizes[] = $size['value'];
                }
            }
            if (count($sizes) > 0) {
                $settings->setThumbnailSize($sizes);
            } else {
                $settings->setThumbnailSize('');
            }
        }

        if ($parentId !== null && empty($params['id'])) {
            $builder = Shopware()->Models()->createQueryBuilder();
            $builder->select(array('settings'))
                    ->from('Shopware\Models\Media\Settings', 'settings')
                    ->where('settings.albumId = :albumId')
                    ->setParameter('albumId', $parentId);

            $settingData = $builder->getQuery()->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
            $settings->fromArray($settingData);
        }

        //try save
        try {
            Shopware()->Models()->persist($album);
            Shopware()->Models()->flush();

            //after the album is saved, set the album id to the settings model and save the settings
            $settings->setAlbumId($album->getId());
            $settings->setAlbum($album);
            Shopware()->Models()->persist($settings);
            Shopware()->Models()->flush();

            //return the album node properties to refresh the tree.
            $node = $this->toTree(array($album));
            $this->View()->assign(array('success' => true, 'data' => $node));
        } catch (\Doctrine\ORM\ORMException $e) {
            $this->View()->assign(array('success' => false, 'message' => $e->getMessage()));
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

        //check if the name passed and is valid
        if (!empty($name)) {
            $path = 'media/' . strtolower($media->getType()) . '/' .   $name . '.' . $media->getExtension();
            $path = Shopware()->DocPath() . $path;

            if (file_exists($path) && $name !== $oldName) {
                $this->View()->assign(array('success' => false, 'message' => 'Name already exist'));
                return;
            }
        } else {
            $media->setName($oldName);
        }

        $media->setAttribute($params['attribute'][0]);
        //check if the album id passed and is valid
        if (isset($params['newAlbumID'])
                && $params['newAlbumID'] !== null
                && $params['newAlbumID'] !== 0
                && $params['newAlbumID'] !== '')
        {
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
     * If the album has sub-albums, the children iterate recursive.
     *
     * @param Shopware\Models\Media\Album $album
     * @return array
     */
    private function getAlbumNodeProperties(\Shopware\Models\Media\Album $album)
    {
        /** @var $repository \Shopware\Models\Media\Repository */
        $repository = Shopware()->Models()->Media();
        $query = $repository->getAlbumMediaQuery($album->getId());

        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query);

        //returns the total count of the query
        $totalResult = $paginator->count();

        $node = array(
            'id' => $album->getId(),
            'text' => $album->getName(),
            'position' => $album->getPosition(),
            'mediaCount' => $totalResult,
            'parentId' => $album->getParentId()
        );

        //to get fresh album settings from new albums too
        $settingsQuery = $repository->getAlbumWithSettingsQuery($album->getId());
        $albumData = $settingsQuery->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        $settings = $albumData["settings"];

        if (!empty($settings) && $settings !== null) {
            $node["iconCls"] = $settings["icon"];
            $node["createThumbnails"] = $settings["createThumbnails"];
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
     * Checks if the position changed and update the position of the old and new level.
     * If the parent changed the old and next level must updated.
     *  OldLevel =>  from OldPosition to MAX -1          <br>
     *  NewLevel =>  from newPosition to MAX +1
     *
     * @param Shopware\Models\Media\Album $album
     * @param $newPosition
     * @param $newParent
     */
    private function changePosition(Album $album, $newPosition, $newParent)
    {
        //position or level not changed?
        if ($newParent == $album->getParentId() && $newPosition == $album->getPosition()) {
            return;
        }

        //0 can not be used, because Doctrine would otherwise check if the album with the id 0 exists.
        $newParent = ($newParent == 0) ? null : $newParent;

        //moved up or down? down => -1 / up = +1
        $step = ($newPosition < $album->getPosition()) ? 1 : -1;

        //Start and end values ​​determined.
        $fromPosition = ($step == 1) ? $newPosition - 1 : $album->getPosition();
        $toPosition = ($step == 1) ? $album->getPosition() : $newPosition + 1;

        //same parent? Then change only the position of the current album level.
        if ($newParent == $album->getParentId()) {
            $this->changePositionBetween($fromPosition, $toPosition, $step, $album->getParentId());
        } else {
            //change the position of the old level
            $this->changePositionBetween($album->getPosition(), 99999, -1, $album->getParentId());

            //change the position of the new level
            $this->changePositionBetween($newPosition - 1, 99999, +1, $newParent);
        }
    }

    /**
     * Change the position of the album between the fromPosition and the toPosition parameters with the passed step parameter.
     * <code>
     * Example:
     *  => fromPosition = 3
     *  => toPosition   = 6
     *  => step         = +1
     * Increase the albums position of the albums on position 4,5 with +1
     * </code>
     *
     * @param $fromPosition
     * @param $toPosition
     * @param $step
     * @param $parentId
     */
    private function changePositionBetween($fromPosition, $toPosition, $step, $parentId)
    {
        $parentId = ($parentId == 0) ? null : $parentId;

        //if the parent id is NULL the IS NULL operator has to be used
        if ($parentId === null) {
            $sql = "UPDATE s_media_album SET position = position + ? WHERE parentID IS NULL AND position > ? AND position < ?";
            Shopware()->Db()->query($sql, array($step, $fromPosition, $toPosition));
        } else {
            $sql = "UPDATE s_media_album SET position = position + ? WHERE parentID = ? AND position > ? AND position < ?";
            Shopware()->Db()->query($sql, array($step, $parentId, $fromPosition, $toPosition));
        }
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
}
