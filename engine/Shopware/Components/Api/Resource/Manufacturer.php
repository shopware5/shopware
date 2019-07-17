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

use Shopware\Components\Api\Exception as ApiException;
use Shopware\Models\Article\Supplier as ManufacturerModel;
use Shopware\Models\Media\Album;
use Shopware\Models\Media\Media as MediaModel;

/**
 * Supplier API Resource
 */
class Manufacturer extends Resource
{
    /**
     * @return \Shopware\Models\Article\SupplierRepository
     */
    public function getRepository()
    {
        return $this->getManager()->getRepository(\Shopware\Models\Article\Supplier::class);
    }

    /**
     * @param int $id
     *
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     *
     * @return array|\Shopware\Models\Article\Supplier
     */
    public function getOne($id)
    {
        $this->checkPrivilege('read');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException('id');
        }

        $query = $this->getRepository()->getDetailQuery($id);

        /** @var \Shopware\Models\Article\Supplier|null $manufacturer */
        $manufacturer = $query->getOneOrNullResult($this->getResultMode());

        if (!$manufacturer) {
            throw new ApiException\NotFoundException(sprintf('Manufacturer by id %d not found', $id));
        }

        return $manufacturer;
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

        $query = $this->getRepository()->getListQuery($criteria, $orderBy, $limit, $offset);
        $query->setHydrationMode(self::HYDRATE_ARRAY);

        $paginator = $this->getManager()->createPaginator($query);

        // Returns the total count of the query
        $totalResult = $paginator->count();

        // Returns the manufacturer data
        $manufacturers = $paginator->getIterator()->getArrayCopy();

        return ['data' => $manufacturers, 'total' => $totalResult];
    }

    /**
     * @throws \Shopware\Components\Api\Exception\ValidationException
     *
     * @return \Shopware\Models\Article\Supplier
     */
    public function create(array $params)
    {
        $this->checkPrivilege('create');

        $manufacturer = new \Shopware\Models\Article\Supplier();

        $params = $this->prepareManufacturerData($params);
        $params = $this->prepareMediaData($params, $manufacturer);

        $manufacturer->fromArray($params);

        if (isset($params['id'])) {
            $metaData = $this->getManager()->getMetadataFactory()->getMetadataFor(\Shopware\Models\Article\Supplier::class);
            $metaData->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
            $manufacturer->setPrimaryIdentifier($params['id']);
        }

        $violations = $this->getManager()->validate($manufacturer);
        if ($violations->count() > 0) {
            throw new ApiException\ValidationException($violations);
        }

        $this->getManager()->persist($manufacturer);
        $this->flush();

        return $manufacturer;
    }

    /**
     * @param int $id
     *
     * @throws \Shopware\Components\Api\Exception\ValidationException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     *
     * @return \Shopware\Models\Article\Supplier
     */
    public function update($id, array $params)
    {
        $this->checkPrivilege('update');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException('id');
        }

        /** @var \Shopware\Models\Article\Supplier|null $manufacturer */
        $manufacturer = $this->getRepository()->findOneBy(['id' => $id]);

        if (!$manufacturer) {
            throw new ApiException\NotFoundException(sprintf('Manufacturer by id %d not found', $id));
        }

        $params = $this->prepareManufacturerData($params);
        $params = $this->prepareMediaData($params, $manufacturer);
        $manufacturer->fromArray($params);

        $violations = $this->getManager()->validate($manufacturer);
        if ($violations->count() > 0) {
            throw new ApiException\ValidationException($violations);
        }

        $this->flush();

        return $manufacturer;
    }

    /**
     * @param int $id
     *
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     *
     * @return \Shopware\Models\Article\Supplier
     */
    public function delete($id)
    {
        $this->checkPrivilege('delete');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException('id');
        }

        /** @var \Shopware\Models\Article\Supplier|null $manufacturer */
        $manufacturer = $this->getRepository()->findOneBy(['id' => $id]);

        if (!$manufacturer) {
            throw new ApiException\NotFoundException(sprintf('Manufacturer by id %d not found', $id));
        }

        $this->getManager()->remove($manufacturer);
        $this->flush();

        return $manufacturer;
    }

    /**
     * @throws ApiException\CustomValidationException
     *
     * @return array
     */
    private function prepareManufacturerData(array $params)
    {
        if (!isset($params['name'])) {
            throw new ApiException\CustomValidationException('A name is required');
        }

        if (!empty($params['attribute'])) {
            foreach ($params['attribute'] as $key => $value) {
                if (is_numeric($key)) {
                    $params['attribute']['attribute' . $key] = $value;
                    unset($params[$key]);
                }
            }
        }

        return $params;
    }

    /**
     * @throws ApiException\CustomValidationException
     *
     * @return array
     */
    private function prepareMediaData(array $data, ManufacturerModel $manufacturerModel)
    {
        if (!isset($data['image'])) {
            return $data;
        }

        $media = null;

        if (isset($data['image']['link'])) {
            /** @var Media $resource */
            $resource = $this->getContainer()->get('shopware.api.media');
            $media = $resource->internalCreateMediaByFileLink($data['image']['link'], Album::ALBUM_SUPPLIER);
        } elseif (!empty($data['image']['mediaId'])) {
            $media = $this->getManager()->find(
                \Shopware\Models\Media\Media::class,
                (int) $data['image']['mediaId']
            );

            if (!($media instanceof MediaModel)) {
                throw new ApiException\CustomValidationException(sprintf('Media by mediaId %s not found', $data['image']['mediaId']));
            }
        }

        $manufacturerModel->setImage($media ? $media->getPath() : '');
        unset($data['image']);

        return $data;
    }
}
