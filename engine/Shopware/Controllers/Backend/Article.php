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

use Doctrine\DBAL\Connection;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Shopware\Bundle\MediaBundle\Exception\MediaFileExtensionIsBlacklistedException;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\AdditionalTextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\Core\ContextService;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Components\CSRFWhitelistAware;
use Shopware\Components\Model\Exception\ModelNotFoundException;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Model\ModelRepository;
use Shopware\Components\Model\QueryBuilder;
use Shopware\Components\ShopRegistrationServiceInterface;
use Shopware\Components\Thumbnail\Manager;
use Shopware\Components\Translation\SwapProductVariantTranslationService;
use Shopware\Models\Article\Article as Product;
use Shopware\Models\Article\Configurator\Dependency;
use Shopware\Models\Article\Configurator\Group;
use Shopware\Models\Article\Configurator\Option;
use Shopware\Models\Article\Configurator\Set;
use Shopware\Models\Article\Configurator\Template\Template;
use Shopware\Models\Article\Detail as ProductVariant;
use Shopware\Models\Article\Download;
use Shopware\Models\Article\Esd;
use Shopware\Models\Article\EsdSerial;
use Shopware\Models\Article\Image;
use Shopware\Models\Article\Image\Mapping;
use Shopware\Models\Article\Image\Rule;
use Shopware\Models\Article\Link;
use Shopware\Models\Article\Price;
use Shopware\Models\Article\Repository as ProductRepository;
use Shopware\Models\Article\SeoCategory;
use Shopware\Models\Article\Supplier;
use Shopware\Models\Article\Unit;
use Shopware\Models\Attribute\Article as ProductAttribute;
use Shopware\Models\Category\Category;
use Shopware\Models\Category\Repository as CategoryRepository;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Customer\Group as CustomerGroup;
use Shopware\Models\Customer\Repository as CustomerRepository;
use Shopware\Models\Media\Media;
use Shopware\Models\Price\Group as PriceGroup;
use Shopware\Models\ProductStream\ProductStream;
use Shopware\Models\Property\Group as PropertyGroup;
use Shopware\Models\Property\Option as PropertyOption;
use Shopware\Models\Property\Value;
use Shopware\Models\Shop\Repository as ShopRepository;
use Shopware\Models\Shop\Shop;
use Shopware\Models\Tax\Tax;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\ConstraintViolationList;

class Shopware_Controllers_Backend_Article extends Shopware_Controllers_Backend_ExtJs implements CSRFWhitelistAware
{
    /**
     * Repository for the product model.
     *
     * @var ProductRepository
     */
    protected $repository;

    /**
     * Repository for the shop model
     *
     * @var ShopRepository
     */
    protected $shopRepository;

    /**
     * Repository for the customer model
     *
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * Repository for the category model
     *
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var ProductRepository
     */
    protected $articleDetailRepository;

    /**
     * @var ModelRepository<CustomerGroup>
     */
    protected $customerGroupRepository;

    /**
     * Entity Manager
     *
     * @var ModelManager
     */
    protected $manager;

    /**
     * @var ModelRepository<Dependency>
     */
    protected $configuratorDependencyRepository;

    /**
     * @deprecated in 5.6, will be removed in 5.7 without a replacement
     *
     * @var ModelRepository<Group>
     */
    protected $configuratorGroupRepository;

    /**
     * @var ModelRepository<Option>
     */
    protected $configuratorOptionRepository;

    /**
     * @var Shopware_Components_Translation
     */
    protected $translation;

    /**
     * @var ModelRepository<Set>
     */
    protected $configuratorSetRepository;

    /**
     * @var ModelRepository<Value>
     */
    protected $propertyValueRepository;

    /**
     * @var array
     */
    protected $esdFileUploadBlacklist = [
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
    ];

    public function initAcl()
    {
        $this->addAclPermission('loadStores', 'read', 'Insufficient Permissions');
        $this->addAclPermission('duplicateArticle', 'save', 'Insufficient Permissions');
        $this->addAclPermission('save', 'save', 'Insufficient Permissions');
        $this->addAclPermission('delete', 'delete', 'Insufficient Permissions');
    }

    /**
     * Disable template engine for all actions
     */
    public function preDispatch()
    {
        if (!\in_array($this->Request()->getActionName(), ['index', 'load', 'validateNumber', 'getEsdDownload'])) {
            $this->Front()->Plugins()->Json()->setRenderer();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getWhitelistedCSRFActions()
    {
        return [
            'previewDetail',
            'getEsdDownload',
        ];
    }

    /**
     * Event listener function of the product backend module. Fired when the user
     * edit or create an product and clicks the save button which displayed on bottom of the product
     * detail window.
     */
    public function saveAction()
    {
        $data = $this->Request()->getParams();

        if ($this->Request()->has('id')) {
            $productId = (int) $this->Request()->getParam('id');
            $product = $this->getRepository()->find($productId);
            if (!$product instanceof Product) {
                throw new ModelNotFoundException(Product::class, $productId);
            }

            // Check whether the product has been modified in the meantime
            try {
                $lastChanged = new DateTime($data['changed']);
            } catch (Exception $e) {
                // If we have a invalid date caused by product imports
                $lastChanged = $product->getChanged();
            }

            if ($lastChanged->getTimestamp() < 0 && $product->getChanged()->getTimestamp() < 0) {
                $lastChanged = $product->getChanged();
            }

            $diff = abs($product->getChanged()->getTimestamp() - $lastChanged->getTimestamp());

            // We have timestamp conversion issues on Windows Users
            if ($diff > 1) {
                $namespace = $this->get('snippets')->getNamespace('backend/article/controller/main');

                $this->View()->assign([
                    'success' => false,
                    'overwriteAble' => true,
                    'data' => $this->getArticle($product->getId()),
                    'message' => $namespace->get('product_has_been_changed', 'The product has been changed in the meantime. To prevent overwriting these changes, saving the product was aborted. Please close the product and re-open it.'),
                ]);

                return;
            }
        } else {
            $product = new Product();
        }
        $this->saveArticle($data, $product);
    }

    /**
     * Event listener function of the configurator set model in the product backend module.
     */
    public function saveConfiguratorSetAction()
    {
        $data = $this->Request()->getParams();
        $id = (int) $data['id'];
        $productId = (int) $data['articleId'];

        if (!empty($productId)) {
            $product = $this->get('models')->find(Product::class, $productId);
            if ($product->getConfiguratorSet()->getId() !== $id) {
                $this->get('models')->remove($product->getConfiguratorSet());
                $this->get('models')->flush();
            }
        }

        if (!empty($id) && $id > 0) {
            $configuratorSet = $this->get('models')->find(Set::class, $id);
        } else {
            $configuratorSet = new Set();
        }
        if (!$configuratorSet) {
            $this->View()->assign([
                'success' => false,
                'noId' => true,
            ]);

            return;
        }

        $groups = [];
        foreach ($data['groups'] as $groupData) {
            if (!empty($groupData['id']) && $groupData['active']) {
                $group = $this->get('models')->find(Group::class, $groupData['id']);
                $group->setPosition($groupData['position']);
                $groups[] = $group;
            }
        }
        $data['groups'] = $groups;

        $options = [];
        foreach ($data['options'] as $optionData) {
            if (!empty($optionData['id']) && $optionData['active']) {
                $option = $this->get('models')->find(Option::class, $optionData['id']);
                $option->setPosition($optionData['position']);
                $options[] = $option;
            }
        }
        $data['options'] = $options;
        $configuratorSet->getOptions()->clear();
        $configuratorSet->getGroups()->clear();
        $configuratorSet->fromArray($data);
        $this->get('models')->persist($configuratorSet);
        $this->get('models')->flush();

        if (!empty($productId)) {
            $product = $this->get('models')->find(Product::class, $productId);
            $product->setConfiguratorSet($configuratorSet);
            $this->get('models')->persist($product);
            $this->get('models')->flush();
        }

        $data = $this->getRepository()->getConfiguratorSetQuery($configuratorSet->getId())->getArrayResult();
        $this->View()->assign([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Event listener function of the backend product module. Fired when the user want to accept the
     * variant data of the main detail to the selected variant(s).
     */
    public function acceptMainDataAction()
    {
        $data = $this->Request()->getParams();
        $productId = (int) $data['articleId'];
        if (empty($productId)) {
            $this->View()->assign(['success' => false, 'noId' => true]);

            return;
        }

        $product = $this->get('models')->find(Product::class, $productId);
        $mainDetail = $product->getMainDetail();
        $mainData = $this->getMappingData($mainDetail, $data);
        $variants = $this->getVariantsForMapping($productId, $mainDetail, $data);
        if (!empty($variants)) {
            foreach ($variants as $variant) {
                $variant->fromArray($mainData);
                $this->get('models')->persist($variant);
            }
            $this->get('models')->flush();
            if ($data['translations']) {
                $this->overrideVariantTranslations($productId, $variants);
            }
        }
        $this->View()->assign(['success' => true]);
    }

    /**
     * Event listener function of the product backend module. Fired when the user clicks the "duplicate product" button
     * on the detail page to duplicate the whole product configuration for a new product.
     */
    public function duplicateArticleAction()
    {
        $productId = (int) $this->Request()->getParam('articleId');

        if (empty($productId)) {
            $this->View()->assign([
                'success' => false,
                'noId' => true,
            ]);
        }

        $product = $this->get('models')->find(Product::class, $productId);
        if ($product->getConfiguratorSet() !== null) {
            $isConfigurator = true;
            $mailDetailId = $product->getMainDetail()->getId();
        } else {
            $isConfigurator = false;
            $mailDetailId = null;
        }

        $this->duplicateArticleData($productId);
        $newProductId = (int) Shopware()->Db()->lastInsertId('s_articles');
        $this->duplicateArticleCategories($productId, $newProductId);
        $this->duplicateArticleCustomerGroups($productId, $newProductId);
        $this->duplicateArticleRelated($productId, $newProductId);
        $this->duplicateArticleSimilar($productId, $newProductId);
        $this->duplicateArticleTranslations($productId, $newProductId);
        $this->duplicateArticleDetails($productId, $newProductId, $mailDetailId);
        $this->duplicateArticleLinks($productId, $newProductId);
        $this->duplicateArticleImages($productId, $newProductId);
        $this->duplicateArticleProperties($productId, $newProductId);
        $this->duplicateArticleDownloads($productId, $newProductId);
        $setId = $this->duplicateArticleConfigurator($productId);

        $sql = 'UPDATE s_articles, s_articles_details SET main_detail_id = s_articles_details.id
                    WHERE s_articles_details.articleID = s_articles.id
                    AND s_articles.id = ?
                    AND s_articles_details.kind = 1';
        Shopware()->Db()->query($sql, [$newProductId]);

        if ($setId !== null) {
            $sql = 'UPDATE s_articles SET configurator_set_id = ?
                        WHERE s_articles.id = ?';
            Shopware()->Db()->query($sql, [$setId, $newProductId]);
        }

        $this->View()->assign([
            'success' => true,
            'articleId' => $newProductId,
            'isConfigurator' => $isConfigurator,
        ]);
    }

    public function deleteAllVariantsAction()
    {
        $productId = (int) $this->Request()->getParam('articleId');
        if (empty($productId)) {
            $this->View()->assign([
                'success' => false,
            ]);

            return;
        }
        $this->removeAllConfiguratorVariants($productId);
        $this->View()->assign([
            'success' => true,
        ]);
    }

    /**
     * @return void
     */
    public function saveMediaMappingAction()
    {
        $imageId = (int) $this->Request()->getParam('id');
        $mappings = $this->Request()->getParam('mappings');

        if (empty($imageId) || $imageId <= 0) {
            $this->View()->assign(['success' => false, 'noId' => true]);

            return;
        }

        $query = $this->getRepository()->getArticleImageDataQuery($imageId);
        $image = $query->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);
        if (!$image instanceof Image) {
            throw new ModelNotFoundException(Image::class, $imageId);
        }

        $imageData = $query->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);
        $this->getRepository()->getDeleteImageChildrenQuery($imageId)->execute();

        $mappingModels = [];
        foreach ($mappings as $mappingData) {
            if (empty($mappingData['rules'])) {
                continue;
            }
            if (empty($mappingData['id'])) {
                $mapping = new Mapping();
            } else {
                $mapping = $this->get('models')->find(Mapping::class, $mappingData['id']);
            }

            $mapping->getRules()->clear();
            $options = [];
            foreach ($mappingData['rules'] as $ruleData) {
                $rule = new Rule();
                $option = $this->get('models')->getReference(Option::class, $ruleData['optionId']);
                $rule->setMapping($mapping);
                $rule->setOption($option);
                $mapping->getRules()->add($rule);
                $options[] = $option;
            }
            $mapping->setImage($image);
            $this->get('models')->persist($mapping);
            $this->createImagesForOptions($options, $imageData, $image);
            $mappingModels[] = $mapping;
        }
        $image->setMappings($mappingModels);
        $this->get('models')->persist($image);
        $this->get('models')->flush();

        $result = $this->getRepository()->getArticleImageQuery($imageId)->getArrayResult();

        $this->View()->assign(['success' => true, 'data' => $result]);
    }

    /**
     * Event listener function of the product backend module. Fired when the user
     * edit or create an product variant and clicks the save button which displayed on bottom of the product
     * variant detail window.
     */
    public function saveDetailAction()
    {
        $data = $this->Request()->getParams();
        $id = (int) $this->Request()->getParam('id');

        if ($id > 0) {
            $detail = $this->getArticleDetailRepository()->find($id);
            if (!$detail instanceof ProductVariant) {
                throw new ModelNotFoundException(ProductVariant::class, $id);
            }
        } else {
            $detail = new ProductVariant();
        }
        $detail = $this->saveDetail($data, $detail);
        $data['id'] = $detail->getId();
        $this->View()->assign([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Event listener function of the product backend module. Fired when the user saves or updates
     * an product configurator dependency in the dependency window.
     */
    public function saveConfiguratorDependencyAction()
    {
        $data = $this->Request()->getParams();
        $id = (int) $this->Request()->getParam('id');
        $dependency = null;
        if ($id > 0) {
            $dependency = $this->getConfiguratorDependencyRepository()->find($id);
        }
        if (!$dependency instanceof Dependency) {
            $dependency = new Dependency();
        }

        $data['childOption'] = $this->getConfiguratorOptionRepository()->find($data['childId']);
        $data['parentOption'] = $this->getConfiguratorOptionRepository()->find($data['parentId']);
        $data['configuratorSet'] = $this->getConfiguratorSetRepository()->find($data['configuratorSetId']);
        $dependency->fromArray($data);
        $this->get('models')->persist($dependency);
        $this->get('models')->flush();

        $builder = $this->get('models')->createQueryBuilder();
        $data = $builder->select(['dependency', 'dependencyParent', 'dependencyChild'])
            ->from(Dependency::class, 'dependency')
            ->leftJoin('dependency.parentOption', 'dependencyParent')
            ->leftJoin('dependency.childOption', 'dependencyChild')
            ->where('dependency.id = ?1')
            ->setParameter(1, $dependency->getId())
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);

        $this->View()->assign([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Event listener function of the product backend module.
     * Fired when the user want to load a configurator set in the configurator tab.
     * The function returns all public defined configurator sets without the passed ids.
     */
    public function getConfiguratorSetsAction()
    {
        $id = $this->Request()->getParam('setId');
        $sets = $this->getRepository()->getConfiguratorSetsWithExcludedIdsQuery($id)->getArrayResult();
        $this->View()->assign([
            'success' => true,
            'data' => $sets,
        ]);
    }

    /**
     * Event listener function of the product backend module. Fired when the user clicks the delete
     * button in the dependency window to delete a dependency.
     */
    public function deleteConfiguratorDependencyAction()
    {
        $id = (int) $this->Request()->getParam('id');
        if (empty($id)) {
            $this->View()->assign([
                'success' => false,
                'message' => 'No valid dependency id passed',
            ]);

            return;
        }
        $model = $this->get('models')->find(Dependency::class, $id);
        $this->get('models')->remove($model);
        $this->get('models')->flush();
        $this->View()->assign([
            'success' => true,
        ]);
    }

    /**
     * The loadStoresAction function is an ExtJs event listener method of the product backend module.
     * The function is used to load all required stores for the product detail page in one request.
     */
    public function loadStoresAction()
    {
        $id = $this->Request()->getParam('articleId');
        $priceGroups = $this->getRepository()->getPriceGroupQuery()->getArrayResult();
        $suppliers = $this->getRepository()->getSuppliersQuery()->getArrayResult();
        $shops = $this->getShopRepository()->createQueryBuilder('shops')->andWhere('shops.active = 1')->getQuery()->getArrayResult();
        $taxes = $this->getRepository()->getTaxesQuery()->getArrayResult();
        $templates = $this->getTemplates();
        $units = $this->getRepository()->getUnitsQuery()->getArrayResult();
        $customerGroups = $this->getCustomerRepository()->getCustomerGroupsQuery()->getArrayResult();
        $properties = $this->getRepository()->getPropertiesQuery()->getArrayResult();
        $configuratorGroups = $this->getRepository()->getConfiguratorGroupsQuery()->getArrayResult();

        if (!empty($id)) {
            $product = $this->getArticle($id);
        } else {
            $product = $this->getNewArticleData();
        }

        $this->View()->assign([
            'success' => true,
            'data' => [
                'shops' => $shops,
                'customerGroups' => $customerGroups,
                'taxes' => $taxes,
                'suppliers' => $suppliers,
                'templates' => $templates,
                'units' => $units,
                'properties' => $properties,
                'priceGroups' => $priceGroups,
                'article' => $product,
                'configuratorGroups' => $configuratorGroups,
                'settings' => [],
            ],
        ]);
    }

    public function getArticleAction()
    {
        $id = $this->Request()->getParam('articleId');
        if (empty($id)) {
            $this->View()->assign(['success' => false, 'error' => 'No product id passed!']);
        }
        $product = $this->getArticle($id);
        $this->View()->assign(['success' => true, 'data' => $product]);
    }

    public function getPropertyListAction()
    {
        $productId = $this->Request()->getParam('articleId');
        $propertyGroupId = $this->Request()->getParam('propertyGroupId');

        $builder = $this->get('models')->createQueryBuilder()
            ->from(PropertyOption::class, 'po')
            ->join('po.groups', 'pg', 'with', 'pg.id = :propertyGroupId')
            ->setParameter('propertyGroupId', $propertyGroupId)
            ->select(['PARTIAL po.{id,name}']);

        $query = $builder->getQuery();
        $options = [];
        foreach ($query->getArrayResult() as $option) {
            $options[$option['id']] = $option;
        }

        $builder = $this->get('models')->createQueryBuilder()
            ->from(Value::class, 'pv')
            ->orderBy('pv.position', 'ASC')
            ->join('pv.articles', 'pa', 'with', 'pa.id = :articleId')
            ->setParameter('articleId', $productId)
            ->join('pv.option', 'po')
            ->select(['po.id as optionId', 'pv.id', 'pv.value']);

        $query = $builder->getQuery();
        $values = $query->getArrayResult();

        foreach ($values as $value) {
            $optionId = $value['optionId'];
            if (!isset($options[$optionId])) {
                continue;
            }
            $options[$optionId]['value'][] = [
                'id' => $value['id'],
                'value' => $value['value'],
            ];
        }

        $this->View()->assign([
            'data' => array_values($options),
            'total' => \count($options),
            'success' => true,
        ]);
    }

    public function createPropertyValueAction()
    {
        $groupId = $this->Request()->getParam('groupId');
        $value = $this->Request()->getParam('value');

        if (!$groupId) {
            return $this->View()->assign(['success' => false, 'message' => 'No property group selected!']);
        }
        if (!$this->Request()->has('value')) {
            return $this->View()->assign(['success' => false, 'message' => 'No property value provided!']);
        }

        $entityManager = Shopware()->Container()->get(ModelManager::class);
        $group = $entityManager->find(PropertyOption::class, $groupId);

        if (!$group) {
            return $this->View()->assign(['success' => false, 'message' => 'No property group selected!']);
        }

        $option = new Value($group, $value);
        $entityManager->persist($option);
        $entityManager->flush($option);

        $this->View()->assign([
            'success' => true,
            'data' => ['id' => $option->getId(), 'value' => $option->getValue()],
        ]);
    }

    /**
     * Returns the available property values
     */
    public function getPropertyValuesAction()
    {
        $propertyGroupId = $this->Request()->getParam('propertyGroupId');
        $searchValue = $this->Request()->getParam('query');
        $optionId = $this->Request()->getParam('optionId');

        $builder = $this->get('models')->getDBALQueryBuilder();
        $builder->select([
            'filterValues.id AS id',
            'filterValues.value AS value',
            'filterOptions.id AS optionId', ])
            ->from('s_filter_values', 'filterValues')
            ->innerJoin('filterValues', 's_filter_options', 'filterOptions', 'filterValues.optionID = filterOptions.id')
            ->innerJoin('filterOptions', 's_filter_relations', 'filterRelations', 'filterOptions.id = filterRelations.optionID')
            ->innerJoin('filterRelations', 's_filter', 'filter', 'filter.id = filterRelations.groupID AND (filter.id = :propertyGroupId)')
            ->setParameter('propertyGroupId', $propertyGroupId);

        if (!empty($searchValue)) {
            $builder->where('filterValues.value like :searchValue');
            $builder->setParameter('searchValue', '%' . $searchValue . '%');
        }
        if (!empty($optionId)) {
            $builder->andWhere('filterOptions.id = :optionId');
            $builder->setParameter('optionId', $optionId);
        }
        $statement = $builder->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($data as &$row) {
            $row['id'] = (int) $row['id'];
            $row['optionId'] = (int) $row['optionId'];
        }
        $this->View()->assign([
            'data' => $data,
            'success' => true,
        ]);
    }

    /**
     * saves the property list values in the product module
     */
    public function setPropertyListAction()
    {
        if (!$this->Request()->isPost()) {
            // don't save the property list on a get request. This will only occur when there is an ext js problem
            return;
        }
        $models = $this->get('models');
        $productId = $this->Request()->getParam('articleId');
        $product = $models->find(Product::class, $productId);
        $properties = $this->Request()->getParam('properties', []);

        if (empty($properties[0])) {
            $properties[0] = [
                'id' => $this->Request()->getParam('id'),
                'name' => $this->Request()->getParam('name'),
                'value' => $this->Request()->getParam('value'),
            ];
        }

        $propertyValues = $product->getPropertyValues();
        $propertyValues->clear();
        $models->flush();

        // If no property group is set for the product, don't recreate the property values
        $propertyGroup = $product->getPropertyGroup();
        if (!$propertyGroup) {
            $this->View()->assign(['success' => true]);

            return;
        }

        $propertyValueRepository = $this->getPropertyValueRepository();
        // recreate property values
        foreach ($properties as $property) {
            if (empty($property['value'])) {
                continue;
            }
            $option = $models->find(PropertyOption::class, $property['id']);
            foreach ((array) $property['value'] as $value) {
                $propertyValueModel = null;
                if (!empty($value['id'])) {
                    // search for property id
                    $propertyValueModel = $propertyValueRepository->find($value['id']);
                }
                if ($propertyValueModel === null) {
                    // search for property value
                    $propertyValueModel = $propertyValueRepository->findOneBy(
                        [
                            'value' => $value['value'],
                            'optionId' => $option->getId(),
                        ]
                    );
                }
                if ($propertyValueModel === null) {
                    $propertyValueModel = new Value($option, $value);
                    $models->persist($propertyValueModel);
                }
                if (!$propertyValues->contains($propertyValueModel)) {
                    // add only new values
                    $propertyValues->add($propertyValueModel);
                }
            }
        }
        $product->setPropertyValues($propertyValues);
        $models->flush();

        $this->View()->assign([
            'success' => true,
        ]);
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Used for the product backend module to load the product data into
     * the module. This function selects only some fragments for the whole product
     * data. The full product data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     *
     * @param int $articleId
     *
     * @return array
     */
    public function getArticleCategories($articleId)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $builder = $this->get('models')->createQueryBuilder();
        $builder->select(['categories.id'])
            ->from(Category::class, 'categories', 'categories.id')
            ->andWhere(':articleId MEMBER OF categories.articles')
            ->setParameter('articleId', $articleId);

        $result = $builder->getQuery()->getArrayResult();
        if (empty($result)) {
            return [];
        }

        $categories = [];
        foreach ($result as $item) {
            $categories[] = [
                'id' => $item['id'],
                'name' => $this->getCategoryRepository()->getPathById($item['id'], 'name', '>'),
            ];
        }

        return $categories;
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Used for the product backend module to load the product data into
     * the module. This function selects only some fragments for the whole product
     * data. The full product data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     *
     * @param int $articleId
     *
     * @return array
     */
    public function getArticleSimilars($articleId)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $result = $this->getRepository()
            ->getArticleSimilarsQuery($articleId)
            ->getArrayResult();

        if (empty($result[0]['similar'])) {
            return [];
        }

        return $result[0]['similar'];
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Loads related product streams data for the given product
     *
     * @param int $articleId
     *
     * @return array
     */
    public function getArticleRelatedProductStreams($articleId)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $result = $this->get(ModelManager::class)->getRepository(Product::class)
            ->getArticleRelatedProductStreamsQuery($articleId)
            ->getArrayResult();

        return $result ?: [];
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Used for the product backend module to load the product data into
     * the module. This function selects only some fragments for the whole product
     * data. The full product data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     *
     * @param int $articleId
     *
     * @return array
     */
    public function getArticleRelated($articleId)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $result = $this->getRepository()
            ->getArticleRelatedQuery($articleId)
            ->getArrayResult();

        if (empty($result[0]['related'])) {
            return [];
        }

        return $result[0]['related'];
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Used for the product backend module to load the product data into
     * the module. This function selects only some fragments for the whole product
     * data. The full product data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     *
     * @param int $articleId
     *
     * @return array
     */
    public function getArticleImages($articleId)
    {
        $mediaService = Shopware()->Container()->get(MediaServiceInterface::class);

        $thumbnailManager = Shopware()->Container()->get(Manager::class);

        $builder = $this->get('models')->createQueryBuilder();
        $builder->select(['images', 'media', 'imageMapping', 'mappingRule', 'ruleOption'])
                ->from(Image::class, 'images')
                ->leftJoin('images.article', 'article')
                ->leftJoin('images.media', 'media')
                ->leftJoin('images.mappings', 'imageMapping')
                ->leftJoin('imageMapping.rules', 'mappingRule')
                ->leftJoin('mappingRule.option', 'ruleOption')
                ->where('article.id = :articleId')
                ->andWhere('images.parentId IS NULL')
                ->orderBy('images.position')
                ->setParameter('articleId', $articleId);

        $result = $builder->getQuery()->getArrayResult();

        foreach ($result as &$item) {
            if (empty($item['media'])) {
                continue;
            }
            $thumbnails = $thumbnailManager->getMediaThumbnails(
                $item['media']['name'],
                $item['media']['type'],
                $item['media']['extension'],
                [
                    [
                        'width' => 140,
                        'height' => 140,
                    ],
                ]
            );

            $item['original'] = $mediaService->getUrl($item['media']['path']);

            if (!empty($thumbnails)) {
                $item['thumbnail'] = $mediaService->getUrl($thumbnails[0]['source']);
            } else {
                $item['thumbnail'] = $mediaService->getUrl($item['media']['path']);
            }
        }

        return $result;
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Used for the product backend module to load the product data into
     * the module. This function selects only some fragments for the whole product
     * data. The full product data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     *
     * @param int $articleId
     *
     * @return array
     */
    public function getArticleLinks($articleId)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $result = $this->getRepository()
            ->getArticleLinksQuery($articleId)
            ->getArrayResult();

        if (empty($result[0]['links'])) {
            return [];
        }
        // map the link target to the boolean format that is expected by the ExtJS backend module
        $links = $result[0]['links'];
        foreach ($links as &$linkData) {
            $linkData['target'] = $linkData['target'] === '_blank';
        }

        return $links;
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Used for the product backend module to load the product data into
     * the module. This function selects only some fragments for the whole product
     * data. The full product data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     *
     * @param int $articleId
     *
     * @return array
     */
    public function getArticleDownloads($articleId)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $result = $this->getRepository()
            ->getArticleDownloadsQuery($articleId)
            ->getArrayResult();

        if (empty($result[0]['downloads'])) {
            return [];
        }

        return $result[0]['downloads'];
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Used for the product backend module to load the product data into
     * the module. This function selects only some fragments for the whole product
     * data. The full product data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     *
     * @param int $articleId
     *
     * @return array
     */
    public function getArticleCustomerGroups($articleId)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $result = $this->getRepository()
            ->getArticleCustomerGroupsQuery($articleId)
            ->getArrayResult();

        if (empty($result[0]['customerGroups'])) {
            return [];
        }

        return $result[0]['customerGroups'];
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Used for the product backend module to load the product data into
     * the module. This function selects only some fragments for the whole product
     * data. The full product data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     *
     * @param int $articleId
     *
     * @return array
     */
    public function getArticleConfiguratorSet($articleId)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $builder = $this->get('models')->createQueryBuilder();
        $builder->select(['configuratorSet', 'groups', 'options'])
            ->from(Set::class, 'configuratorSet')
            ->innerJoin('configuratorSet.articles', 'article')
            ->leftJoin('configuratorSet.groups', 'groups')
            ->leftJoin('configuratorSet.options', 'options')
            ->addOrderBy('groups.position', 'ASC')
            ->addOrderBy('options.groupId', 'ASC')
            ->addOrderBy('options.position', 'ASC')
            ->where('article.id = :articleId')
            ->setParameter('articleId', $articleId);

        $result = $builder->getQuery()->getArrayResult();

        return $result[0];
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Used for the product backend module to load the product data into
     * the module. This function selects only some fragments for the whole product
     * data. The full product data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     *
     * @param int $configuratorSetId
     *
     * @return array
     */
    public function getArticleDependencies($configuratorSetId)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        return $this->getRepository()
            ->getConfiguratorDependenciesQuery($configuratorSetId)
            ->getArrayResult();
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Used for the product backend module to load the product data into
     * the module. This function selects only some fragments for the whole product
     * data. The full product data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     *
     * @param int   $articleId
     * @param array $tax
     *
     * @return array
     */
    public function getArticleConfiguratorTemplate($articleId, $tax)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $query = $this->getRepository()->getConfiguratorTemplateByArticleIdQuery($articleId);

        $configuratorTemplate = $query->getArrayResult();

        $prices = $configuratorTemplate[0]['prices'];

        if (!empty($prices)) {
            $configuratorTemplate[0]['prices'] = $this->formatPricesFromNetToGross($prices, $tax);
        }

        return $configuratorTemplate;
    }

    /**
     * Loads the variant listing for the product backend module.
     */
    public function detailListAction()
    {
        if (!$this->Request()->has('articleId')) {
            $this->View()->assign([
                'success' => false,
                'message' => 'No valid product id passed',
            ]);

            return;
        }
        $productId = $this->Request()->getParam('articleId');

        $product = $this->get('models')->find(Product::class, $productId);
        $tax = [
            'tax' => (float) $product->getTax()->getTax(),
        ];

        $idQuery = $this->getRepository()->getConfiguratorListIdsQuery(
            $productId,
            $this->Request()->getParam('filter'),
            $this->Request()->getParam('sort'),
            $this->Request()->getParam('start'),
            $this->Request()->getParam('limit', 20)
        );

        $total = $this->get('models')->getQueryCount($idQuery);
        $ids = $idQuery->getArrayResult();

        foreach ($ids as $key => $id) {
            $ids[$key] = $id['id'];
        }
        if (empty($ids)) {
            $this->View()->assign([
                'success' => true,
                'data' => [],
                'total' => 0,
            ]);

            return;
        }

        $query = $this->getRepository()->getDetailsByIdsQuery($ids, $this->Request()->getParam('sort'));
        $details = $query->getArrayResult();

        $return = [];
        foreach ($details as $key => $detail) {
            if (empty($detail['prices']) || empty($detail['configuratorOptions'])) {
                continue;
            }
            $detail['prices'] = $this->formatPricesFromNetToGross($detail['prices'], $tax);
            if ($detail['releaseDate']) {
                $releaseDate = $detail['releaseDate'];
                if ($releaseDate instanceof DateTime) {
                    $detail['releaseDate'] = $releaseDate->format('d.m.Y');
                }
            }
            $return[] = $detail;
        }

        $this->View()->assign([
            'success' => true,
            'data' => $return,
            'total' => $total,
        ]);
    }

    /**
     * Saves the passed configurator group data. If an id passed, the function updates the existing group, otherwise
     * a new group will be created.
     */
    public function saveConfiguratorGroupAction()
    {
        $id = (int) $this->Request()->getParam('id');
        if (!empty($id)) {
            $group = $this->get('models')->find(Group::class, $id);
        } else {
            $group = new Group();
        }
        $data = $this->Request()->getParams();
        unset($data['options']);
        $group->fromArray($data);
        $this->get('models')->persist($group);
        $this->get('models')->flush();
        $data['id'] = $group->getId();

        $this->View()->assign([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Saves the passed configurator option data. If an id passed, the function updates the existing options, otherwise
     * a new option will be created.
     */
    public function saveConfiguratorOptionAction()
    {
        $id = (int) $this->Request()->getParam('id');
        if (!empty($id)) {
            $option = $this->get('models')->find(Option::class, $id);
        } else {
            $option = new Option();
        }
        $data = $this->Request()->getParams();
        if (empty($data['groupId'])) {
            return;
        }
        $data['group'] = $this->get('models')->find(Group::class, $data['groupId']);

        $option->fromArray($data);
        $this->get('models')->persist($option);
        $this->get('models')->flush();
        $data['id'] = $option->getId();

        $this->View()->assign([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Called when the user clicks the "generateVariants" button in the product backend module.
     * The function expects that an product id passed and an array with active groups passed.
     */
    public function createConfiguratorVariantsAction()
    {
        // First get the id parameter of the request object
        $productId = (int) $this->Request()->getParam('articleId', 1);
        $groups = $this->Request()->getParam('groups');
        $offset = (int) $this->Request()->getParam('offset', 0);
        $limit = (int) $this->Request()->getParam('limit', 50);

        // The merge type defines if all variants has to been regenerated or if only new variants will be added.
        // 1 => Regenerate all variants
        // 2 => Merge variants
        $mergeType = (int) $this->Request()->getParam('mergeType', 1);

        $product = $this->getRepository()->find($productId);
        if (!$product instanceof Product) {
            throw new ModelNotFoundException(Product::class, $productId);
        }

        $generatorData = $this->prepareGeneratorData($groups, $offset, $limit);

        $detailData = $this->getDetailDataForVariantGeneration($product);

        if ($offset === 0 && $mergeType === 1) {
            $this->removeAllConfiguratorVariants($productId);
        } elseif ($offset === 0 && $mergeType === 2) {
            $this->deleteVariantsForAllDeactivatedOptions($product, $generatorData['allOptions']);
        }

        $this->get('models')->clear();
        $product = $this->getRepository()->find($productId);
        if (!$product instanceof Product) {
            throw new ModelNotFoundException(Product::class, $productId);
        }
        $detailData = $this->setDetailDataReferences($detailData, $product);

        $configuratorSet = $product->getConfiguratorSet();
        if (!$configuratorSet instanceof Set) {
            throw new Exception(sprintf('Should not happen. The product with the given ID "%s" must be a variant product at this point.', $productId));
        }
        $dependencies = $this->getRepository()->getConfiguratorDependenciesQuery($configuratorSet->getId())->getArrayResult();
        $priceVariations = $this->getRepository()->getConfiguratorPriceVariationsQuery($configuratorSet->getId())->getArrayResult();

        if (empty($generatorData)) {
            return;
        }

        $sql = $generatorData['sql'];
        $originals = $generatorData['originals'];
        $variants = Shopware()->Db()->fetchAll($sql);

        $counter = 1;
        if ($mergeType === 1) {
            $counter = $offset;
        }
        $allOptions = $this->getRepository()->getAllConfiguratorOptionsIndexedByIdQuery()->getResult();

        // Iterate all selected variants to insert them into the database
        foreach ($variants as $variant) {
            $variantData = $this->prepareVariantData($variant, $detailData, $counter, $dependencies, $priceVariations, $allOptions, $originals, $product, $mergeType);
            if ($variantData === false) {
                continue;
            }

            // Merge the data with the original main detail data
            $data = array_merge($detailData, $variantData);

            $existentDetailModel = $offset === 0 && $mergeType === 1;

            $data = $this->container->get('events')->filter(
                'Shopware_Controllers_Article_CreateConfiguratorVariants_FilterData',
                $data,
                [
                    'subject' => $this,
                    'existentDetailModel' => $existentDetailModel,
                ]
            );

            // Use only the main detail of the product as base object, if the merge type is set to "Override" and the current variant is the first generated variant.
            if ($existentDetailModel) {
                $detail = $product->getMainDetail();
            } else {
                $detail = new ProductVariant();
                $this->get('models')->persist($detail);
            }

            $detail->fromArray($data);
            $detail->setArticle($product);
            $this->get('models')->flush();

            $this->copyConfigurationTemplateTranslations($detailData, $detail);
            ++$offset;
        }

        $this->get('models')->clear();

        $product = $this->getRepository()->find($productId);
        if (!$product instanceof Product) {
            throw new ModelNotFoundException(Product::class, $productId);
        }

        // Check if the main detail variant was deleted
        if ($product->getMainDetail() === null) {
            $newMainDetail = $this->getArticleDetailRepository()->findOneBy(['articleId' => $productId]);
            if (!$newMainDetail instanceof ProductVariant) {
                throw new ModelNotFoundException(ProductVariant::class, $productId, 'articleId');
            }
            $product->setMainDetail($newMainDetail);
        }

        $this->get('models')->flush();

        $product = $this->getArticle($productId);
        $this->View()->assign([
            'success' => true,
            'data' => $product,
        ]);
    }

    /**
     * Event listener function of the product store of the backend module.
     */
    public function deleteAction()
    {
        if (!$this->Request()->has('id')) {
            return;
        }
        $id = (int) $this->Request()->getParam('id');
        $product = $this->getRepository()->find($id);
        if (!$product instanceof Product) {
            return;
        }
        $this->removePrices($product->getId());
        $this->removeArticleEsd($product->getId());
        $this->removeArticleDetails($product);
        $this->removeArticleTranslations($product);

        $this->get('models')->remove($product);
        $this->get('models')->flush();
        $this->View()->assign([
            'data' => $this->Request()->getParams(),
            'success' => true,
        ]);
    }

    /**
     * Event listener function of the configurator group model of the product backend module.
     * Fired when the user want to remove a configurator group.
     * The function requires a passed id to load the shopware model an remove it over the model manager.
     */
    public function deleteConfiguratorGroupAction()
    {
        if (!$this->Request()->has('id')) {
            $this->View()->assign([
                'success' => false,
                'message' => 'No valid id passed',
            ]);
        }
        $model = $this->get('models')->find(Group::class, (int) $this->Request()->getParam('id'));
        if (!$model instanceof Group) {
            $this->View()->assign([
                'success' => false,
                'message' => 'No valid id passed',
            ]);
        }
        $builder = $this->get('models')->createQueryBuilder();
        $boundedProducts = $builder->select(['articles'])
            ->from(ProductVariant::class, 'articles')
            ->innerJoin('articles.configuratorOptions', 'options')
            ->where('options.groupId = ?1')
            ->setParameter(1, (int) $this->Request()->getParam('id'))
            ->getQuery()
            ->getArrayResult();

        if (\count($boundedProducts) > 0) {
            $products = [];
            foreach ($boundedProducts as $boundedProduct) {
                $products[] = $boundedProduct['number'] . ' - ' . $boundedProduct['additionalText'];
            }

            $this->View()->assign([
                'success' => false,
                'articles' => $products,
                'message' => 'Articles bounded on this group!',
            ]);

            return;
        }

        $this->get('models')->remove($model);
        $this->get('models')->flush();
        $this->View()->assign([
            'success' => true,
        ]);
    }

    /**
     * Event listener function of the configurator listing. Fired when the user
     * selects one or many rows in the configurator listing and clicks the delete button.
     */
    public function deleteDetailAction()
    {
        $details = $this->Request()->getParam('details', [['id' => (int) $this->Request()->getParam('id')]]);

        $product = null;
        foreach ($details as $detail) {
            if (empty($detail['id'])) {
                continue;
            }
            $model = $this->get('models')->find(ProductVariant::class, $detail['id']);
            if (!$model instanceof ProductVariant) {
                continue;
            }
            if ($product === null) {
                $product = $model->getArticle();
            }
            if ($model->getId() !== $product->getMainDetail()->getId()) {
                $this->get('models')->remove($model);

                $configuratorSet = $product->getConfiguratorSet();
                if (!$configuratorSet instanceof Set) {
                    continue;
                }
                $setId = $configuratorSet->getId();
                foreach ($detail['configuratorOptions'] as $option) {
                    $sql = 'DELETE FROM s_article_configurator_set_option_relations WHERE set_id = :setId AND option_id = :optionId;';
                    $this->container->get(Connection::class)->executeQuery($sql, ['setId' => $setId, 'optionId' => $option['id']]);
                }
            }
        }
        $this->get('models')->flush();

        $this->View()->assign([
            'success' => true,
        ]);
    }

    /**
     * Event listener function of the configurator group model of the product backend module.
     * Fired when the user want to remove a configurator group.
     * The function requires a passed id to load the shopware model an remove it over the model manager.
     */
    public function deleteConfiguratorOptionAction()
    {
        $id = (int) $this->Request()->getParam('id');

        if (empty($id) || $id < 0) {
            $this->View()->assign([
                'success' => false,
                'message' => 'No valid id passed',
            ]);
        }
        $model = $this->get('models')->find(Option::class, $id);
        if (!$model instanceof Option) {
            $this->View()->assign([
                'success' => false,
                'message' => 'No valid id passed',
            ]);
        }
        $builder = $this->get('models')->createQueryBuilder();
        $boundedProducts = $builder->select(['articles'])
            ->from(ProductVariant::class, 'articles')
            ->innerJoin('articles.configuratorOptions', 'options')
            ->where('options.id = ?1')
            ->setParameter(1, $id)
            ->getQuery()
            ->getArrayResult();

        if (\count($boundedProducts) > 0) {
            $articles = [];
            foreach ($boundedProducts as $boundedProduct) {
                $articles[] = $boundedProduct['number'] . ' - ' . $boundedProduct['additionalText'];
            }

            $this->View()->assign([
                'success' => false,
                'articles' => $articles,
                'message' => 'Articles bounded on this option!',
            ]);

            return;
        }

        $this->get('models')->remove($model);
        $this->get('models')->flush();
        $this->View()->assign([
            'success' => true,
        ]);
    }

    /**
     * Event listener function of the product backend module.
     * Will be fired when the user changes to the ESD-Tab.
     */
    public function getEsdAction()
    {
        if ($this->Request()->getParam('filterCandidates', false)) {
            $productId = $this->Request()->getParam('articleId');

            $builder = $this->getManager()->createQueryBuilder();

            $builder->select([
                'articleDetail.id as id',
                'article.name as name',
                'articleDetail.id as articleDetailId',
                'articleDetail.additionalText as additionalText',
                'article.id as articleId',
                'articleDetail.number',
            ]);
            $builder->from(ProductVariant::class, 'articleDetail')
                ->leftJoin('articleDetail.esd', 'esd')
                ->leftJoin('articleDetail.article', 'article')
                ->where('articleDetail.articleId = :articleId')
                ->andWhere('esd.id IS NULL')
                ->setParameter('articleId', $productId);

            $query = $builder->getQuery();
            $query->setHydrationMode(AbstractQuery::HYDRATE_ARRAY);
            $paginator = $this->getModelManager()->createPaginator($query);

            // returns the total count of the query
            $totalResult = $paginator->count();

            // returns the customer data
            $result = iterator_to_array($paginator);

            $products = $this->buildListProducts($result);
            $products = $this->getAdditionalTexts($products);
            $result = $this->assignAdditionalText($result, $products);

            $this->View()->assign([
                'data' => $result,
                'total' => $totalResult,
                'success' => true,
            ]);

            return;
        }

        $productId = $this->Request()->getParam('articleId');

        $filter = $this->Request()->getParam('filter');
        $sort = $this->Request()->getParam('sort');
        $start = $this->Request()->getParam('start', 0);
        $limit = $this->Request()->getParam('limit', 20);

        $query = $this->getRepository()->getEsdByArticleQuery($productId, $filter, $limit, $start, $sort);
        $query->setHydrationMode(AbstractQuery::HYDRATE_ARRAY);

        $paginator = $this->getModelManager()->createPaginator($query);

        // returns the total count of the query
        $totalResult = $paginator->count();

        // returns the customer data
        $result = iterator_to_array($paginator);

        // inserts esd attributes into the result
        $result = $this->getEsdListingAttributes($result);

        $this->View()->assign([
            'data' => $result,
            'total' => $totalResult,
            'success' => true,
        ]);
    }

    /**
     * Event listener function of the product backend module.
     * Will be fired when the user clicks the edit esd-button.
     */
    public function getSerialsAction()
    {
        $esdId = $this->Request()->getParam('esdId');

        $filter = $this->Request()->getParam('filter');
        $sort = $this->Request()->getParam('sort');
        $start = $this->Request()->getParam('start', 0);
        $limit = $this->Request()->getParam('limit', 20);

        $query = $this->getRepository()->getSerialsByEsdQuery($esdId, $filter, $start, $limit, $sort);
        $query->setHydrationMode(AbstractQuery::HYDRATE_ARRAY);

        $paginator = $this->getModelManager()->createPaginator($query);

        // returns the total count of the query
        $totalResult = $paginator->count();

        // returns the customer data
        $result = iterator_to_array($paginator);

        $this->View()->assign([
            'data' => $result,
            'total' => $totalResult,
            'success' => true,
        ]);
    }

    public function createEsdAction()
    {
        $variantId = $this->Request()->getPost('articleDetailId');

        $variant = $this->get('models')->getRepository(ProductVariant::class)->find($variantId);
        if (!$variant) {
            $this->View()->assign([
                'success' => false,
                'message' => sprintf('Product variant by id %s not found', $variantId),
            ]);

            return;
        }

        $esd = new Esd();
        $esd->setArticleDetail($variant);

        $this->getManager()->persist($esd);
        $this->getManager()->flush();

        $this->View()->assign([
            'success' => true,
        ]);
    }

    /**
     * Event listener function of the product backend module.
     * Will be fired when the user saves ESD
     */
    public function saveEsdAction()
    {
        $esdId = $this->Request()->getPost('id');

        $esd = $this->get('models')->getRepository(Esd::class)->find($esdId);
        if (!$esd) {
            $this->View()->assign([
                'success' => false,
                'message' => sprintf('ESD by id %s not found', $esdId),
            ]);

            return;
        }

        $freeSerialsCount = $this->getFreeSerialCount($esdId);
        $variant = $esd->getArticleDetail();
        $variant->setInStock($freeSerialsCount);

        $esd->fromArray($this->Request()->getPost());
        $this->getManager()->flush();

        $this->View()->assign([
            'data' => $this->Request()->getPost(),
            'success' => true,
        ]);
    }

    /**
     * Event listener function of the product backend module.
     * Will be fired when the user deletes ESD
     */
    public function deleteEsdAction()
    {
        $details = $this->Request()->getParam('details', [['id' => $this->Request()->getParam('id')]]);

        foreach ($details as $detail) {
            if (empty($detail['id'])) {
                continue;
            }

            $model = $this->get('models')->find(Esd::class, $detail['id']);
            if (!$model) {
                continue;
            }
            $this->get('models')->remove($model);
        }

        $this->get('models')->flush();
        $this->View()->assign([
            'success' => true,
        ]);
    }

    /**
     * Event listener function of the product backend module.
     * Will be fired when the user deletes serials
     */
    public function deleteSerialsAction()
    {
        $esdId = $this->Request()->getParam('esdId');

        $details = $this->Request()->getParam('details', [['id' => $this->Request()->getParam('id')]]);

        foreach ($details as $detail) {
            if (empty($detail['id'])) {
                continue;
            }

            $model = $this->get('models')->find(EsdSerial::class, $detail['id']);
            if (!$model) {
                continue;
            }
            $this->get('models')->remove($model);
        }

        $this->get('models')->flush();
        $this->View()->assign([
            'success' => true,
        ]);

        // Update stock
        $esd = $this->get('models')->getRepository(Esd::class)->find($esdId);
        $freeSerialsCount = $this->getFreeSerialCount($esdId);
        $variant = $esd->getArticleDetail();
        $variant->setInStock($freeSerialsCount);
        $this->get('models')->flush();
    }

    /**
     * Event listener function of the product backend module.
     * Deletes unused serial numbers
     */
    public function deleteUnusedSerialsAction()
    {
        $esdId = $this->Request()->getParam('esdId');

        $query = $this->getRepository()->getUnusedSerialsByEsdQuery($esdId);
        $serials = $query->execute();
        $totalCount = \count($serials);

        foreach ($serials as $serial) {
            $this->getManager()->remove($serial);
        }

        $this->getManager()->flush();

        $this->View()->assign([
            'total' => $totalCount,
            'success' => true,
        ]);

        // Update stock
        $esd = $this->get('models')->getRepository(Esd::class)->find($esdId);
        $freeSerialsCount = $this->getFreeSerialCount($esdId);
        $variant = $esd->getArticleDetail();
        $variant->setInStock($freeSerialsCount);

        $this->getManager()->flush();
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Return number of free serials for given esdId
     *
     * @param int $esdId
     *
     * @return int
     */
    public function getFreeSerialCount($esdId)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $query = $this->getRepository()->getFreeSerialsCountByEsdQuery($esdId);

        return $query->getSingleScalarResult();
    }

    /**
     * Event listener function of the product backend module.
     * Creates new serial numbers
     */
    public function saveSerialsAction()
    {
        $esdId = $this->Request()->getParam('esdId');

        $esd = $this->get('models')->getRepository(Esd::class)->find($esdId);

        if (!$esd) {
            $this->View()->assign([
                'success' => false,
                'message' => sprintf('ESD by id %s not found', $esdId),
            ]);

            return;
        }

        $serials = $this->Request()->getParam('serials');

        // Split string at newlines (WIN, Linux, OSX)
        $serials = preg_split('/$\R?^/m', $serials);
        if ($serials === false) {
            $this->View()->assign([
                'success' => false,
                'message' => 'Could not split serials',
            ]);

            return;
        }

        // Trim every serial number
        $serials = array_map('trim', $serials);

        // Remove empty serial numbers
        $serials = array_filter($serials);

        // Remove duplicates
        $serials = array_unique($serials);

        $newSerials = 0;

        foreach ($serials as $serialnumber) {
            $serialnumber = trim($serialnumber);
            $serial = $this->get('models')->getRepository(EsdSerial::class)->findOneBy(['serialnumber' => $serialnumber]);
            if ($serial) {
                continue;
            }

            $serial = new EsdSerial();
            $serial->setSerialnumber($serialnumber);
            $serial->setEsd($esd);
            $this->getManager()->persist($serial);
            ++$newSerials;
        }
        $this->getManager()->flush();

        // Update stock
        $freeSerialsCount = $this->getFreeSerialCount($esdId);
        $variant = $esd->getArticleDetail();
        $variant->setInStock($freeSerialsCount);

        $this->getManager()->flush();

        $this->View()->assign([
            'success' => true,
            'total' => $newSerials,
        ]);
    }

    /**
     * Event listener function of the product backend module.
     * Returns list of ESD-Files
     */
    public function getEsdFilesAction()
    {
        $filesystem = $this->container->get('shopware.filesystem.private');
        $contents = $filesystem->listContents($this->container->get(Shopware_Components_Config::class)->offsetGet('esdKey'));

        $result = [];
        foreach ($contents as $file) {
            if ($file['type'] !== 'file') {
                continue;
            }

            $result[] = ['filename' => $file['basename']];
        }

        $this->View()->assign([
            'data' => $result,
            'total' => \count($result),
            'success' => true,
        ]);
    }

    /**
     * Event listener function of the product backend module.
     * Uploads ESD-File
     */
    public function uploadEsdFileAction()
    {
        $overwriteMode = $this->Request()->query->get('uploadMode');
        $file = $this->Request()->files->get('fileId');

        if (!$file instanceof UploadedFile) {
            $this->View()->assign(['success' => false]);

            return;
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $blacklist = $this->esdFileUploadBlacklist;

        $blacklist = $this->container->get('shopware.event_manager')->filter(
            'Shopware_Controllers_Backend_Article_UploadEsdFile_Filter_EsdFileUploadBlacklist',
            $blacklist,
            [
                'subject' => $this,
            ]
        );

        if (\in_array($extension, $blacklist, true)) {
            $e = new MediaFileExtensionIsBlacklistedException($extension);
            $this->View()->assign([
                'success' => false,
                'message' => $e->getMessage(),
                'exception' => [
                    '_class' => \get_class($e),
                    'extension' => $extension,
                ],
            ]);

            return;
        }

        $filesystem = $this->container->get('shopware.filesystem.private');
        $destinationPath = $this->container->get(Shopware_Components_Config::class)->offsetGet('esdKey') . '/' . ltrim($file->getClientOriginalName(), '.');

        if ($overwriteMode === 'rename') {
            $counter = 1;
            do {
                $newFilename = pathinfo(ltrim($file->getClientOriginalName()), PATHINFO_FILENAME) . '-' . $counter . '.' . pathinfo($destinationPath, PATHINFO_EXTENSION);
                $destinationPath = $this->container->get(Shopware_Components_Config::class)->offsetGet('esdKey') . '/' . ltrim($newFilename, '.');
                ++$counter;
            } while ($filesystem->has($destinationPath));

            $this->View()->assign('newName', pathinfo($destinationPath, PATHINFO_BASENAME));
        }

        if ($filesystem->has($destinationPath)) {
            if ($overwriteMode === 'overwrite') {
                $filesystem->delete($destinationPath);
            } else {
                $this->View()->assign(['fileExists' => true]);

                return;
            }
        }

        $filePath = (string) $file->getRealPath();
        $upstream = fopen($filePath, 'rb');
        if (!\is_resource($upstream)) {
            throw new RuntimeException(sprintf('Could not open file at: %s', $filePath));
        }
        $filesystem->writeStream($destinationPath, $upstream);
        fclose($upstream);

        $this->View()->assign(['success' => true]);
    }

    public function loadAction()
    {
        parent::loadAction();
        $this->view->assign('orderNumberRegex', $this->container->getParameter('shopware.product.orderNumberRegex'));
    }

    /**
     * Event listener function of the product backend module.
     * Downloads ESD-File
     */
    public function getEsdDownloadAction()
    {
        $filesystem = $this->container->get('shopware.filesystem.private');
        $path = $this->container->get(Shopware_Components_Config::class)->offsetGet('esdKey') . '/' . $this->Request()->getParam('filename');

        if ($filesystem->has($path) === false) {
            $this->Front()->Plugins()->Json()->setRenderer();
            $this->View()->assign(['message' => 'File not found', 'success' => false]);

            return;
        }

        $meta = $filesystem->getMetadata($path);
        $mimeType = $filesystem->getMimetype($path) ?: 'application/octet-stream';

        @set_time_limit(0);

        $this->Front()->Plugins()->ViewRenderer()->setNoRender();

        $response = $this->Response();
        $response->headers->set('content-type', $mimeType);
        $response->headers->set('content-disposition', sprintf('attachment; filename="%s"', basename($path)));
        $response->headers->set('content-length', $meta['size']);
        $response->headers->set('content-transfer-encoding', 'binary');
        $response->sendHeaders();
        $response->sendResponse();

        $upstream = $filesystem->readStream($path);
        if (!\is_resource($upstream)) {
            throw new RuntimeException(sprintf('Could not open file from: %s', $path));
        }
        $downstream = fopen('php://output', 'wb');
        if (!\is_resource($downstream)) {
            throw new RuntimeException('Could not open temporary stream');
        }

        ob_end_clean();

        while (!feof($upstream)) {
            $read = fread($upstream, 4096);
            if (!\is_string($read)) {
                continue;
            }
            fwrite($downstream, $read);
            flush();
        }
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Event listener function of the product backend module.
     * Returns statistical data
     */
    public function getChartData()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $productId = $this->Request()->getParam('articleId');
        $dateFormat = '%Y%m';
        $limit = 12;

        $sql = sprintf("
            SELECT
                SUM(price*quantity) AS revenue,
                SUM(quantity) AS orders,
                DATE_FORMAT(ordertime, '%s') as groupdate,
                WEEK(ordertime) as week,
                MONTH(ordertime) as month,
                ordertime as date
            FROM s_order_details, s_order
            WHERE articleID = ?
                AND s_order.id = s_order_details.orderID
                AND s_order.status != 4
                AND s_order.status != -1
                AND s_order_details.modus = 0
            GROUP BY groupdate
            ORDER BY groupdate ASC
            LIMIT %d
        ", $dateFormat, $limit);

        $stmt = Shopware()->Db()->query($sql, $productId);
        $result = $stmt->fetchAll();

        $this->View()->assign([
            'data' => $result,
            'total' => \count($result),
            'success' => true,
        ]);
    }

    /**
     * The "regenerateVariantOrderNumbersAction" allows the user to recreate
     * the product variant order number with an own number syntax.
     * Called from the product backend module.
     */
    public function regenerateVariantOrderNumbersAction()
    {
        $data = $this->Request()->getParams();
        $productId = $data['articleId'];
        $syntax = $data['syntax'];
        $offset = $this->Request()->getParam('offset');
        $limit = $this->Request()->getParam('limit');

        if (!($productId > 0) || $syntax === '') {
            return;
        }

        $builder = $this->getRepository()->createQueryBuilder('article');
        $builder->where('article.id = :id')
            ->setParameter('id', $productId);

        $product = $builder->getQuery()->getOneOrNullResult(
            AbstractQuery::HYDRATE_OBJECT
        );

        $abortId = $product->getMainDetail()->getId();
        $commands = $this->prepareNumberSyntax($syntax);

        $builder = $this->getVariantsWithOptionsBuilder($productId, $offset, $limit);
        /** @var Query<ProductVariant> $query */
        $query = $builder->getQuery();
        $query->setHydrationMode(AbstractQuery::HYDRATE_OBJECT);
        $paginator = $this->getModelManager()->createPaginator($query);
        $details = iterator_to_array($paginator);

        $counter = $offset;
        if ($offset == 0) {
            $counter = 1;
        }

        foreach ($details as $detail) {
            if ($detail->getId() === $abortId) {
                continue;
            }
            $number = $this->interpretNumberSyntax($product, $detail, $commands, $counter);
            ++$counter;
            if ($number === '') {
                continue;
            }
            $detail->setNumber($number);
            $this->get('models')->persist($detail);
        }
        $this->get('models')->flush();
        $this->View()->assign([
            'success' => true,
        ]);
    }

    /**
     * Event listener function of the product backend module.
     * Returns statistical data
     */
    public function getStatisticAction()
    {
        $productId = $this->Request()->getParam('articleId');

        if ($this->Request()->getParam('chart', false)) {
            return $this->getChartData();
        }

        $timeStamp = (int) mktime(0, 0, 0, (int) date('m'), 1, (int) date('Y'));
        $startDate = $this->Request()->getParam('fromDate', date('Y-m-d', $timeStamp));
        $endDate = $this->Request()->getParam('toDate', date('Y-m-d'));

        $sql = "
            SELECT
            SUM(price*quantity) AS revenue,
            SUM(quantity) AS orders,
            MONTH(ordertime) as month,
            DATE_FORMAT(ordertime, '%Y-%m-%d') as date
            FROM s_order_details, s_order
            WHERE articleID = :articleId
            AND s_order.id = s_order_details.orderID
            AND s_order.status != 4
            AND s_order.status != -1
            AND s_order_details.modus = 0
            AND TO_DAYS(ordertime) <= TO_DAYS(:endDate)
            AND TO_DAYS(ordertime) >= TO_DAYS(:startDate)
            GROUP BY TO_DAYS(ordertime)
            ORDER BY ordertime DESC
        ";

        $stmt = Shopware()->Db()->query($sql, [
            'endDate' => $endDate,
            'startDate' => $startDate,
            'articleId' => $productId,
        ]);
        $result = $stmt->fetchAll();

        $this->View()->assign([
            'data' => $result,
            'total' => \count($result),
            'success' => true,
        ]);
    }

    /**
     * Remote validator for the product order number field.
     * The passed value must be set and the number must be unique
     */
    public function validateNumberAction()
    {
        Shopware()->Front()->Plugins()->ViewRenderer()->setNoRender();

        $exist = $this->getRepository()
            ->getValidateNumberQuery($this->Request()->value, $this->Request()->param)
            ->getArrayResult();

        if (empty($exist) && $this->Request()->value !== '') {
            echo 'true';
        } else {
            return;
        }
    }

    /**
     * Event listener function of the backend module. Fired when the user select a shop in the shop combo in the option
     * panel of the sidebar and clicks on the "preview" button to display the product details in the store front.
     */
    public function previewDetailAction()
    {
        $shopId = (int) $this->Request()->getParam('shopId');
        $productId = (int) $this->Request()->getParam('articleId');

        $repository = $this->get('models')->getRepository(Shop::class);
        $shop = $repository->getActiveById($shopId);

        if (!$shop instanceof Shop) {
            throw new Exception('Invalid shop provided.');
        }

        $this->get(ShopRegistrationServiceInterface::class)->registerShop($shop);

        Shopware()->Session()->set('Admin', true);

        $url = $this->Front()->ensureRouter()->assemble(
            [
                'module' => 'frontend',
                'controller' => 'detail',
                'sArticle' => $productId,
            ]
        );

        $this->Response()->headers->setCookie(new Cookie('shop', (string) $shopId, 0, $shop->getBasePath()));
        $this->redirect($url);
    }

    /**
     * @return Shopware_Components_Translation
     */
    protected function getTranslationComponent()
    {
        if ($this->translation === null) {
            $this->translation = $this->container->get(Shopware_Components_Translation::class);
        }

        return $this->translation;
    }

    /**
     * Internal helper function to get access to the entity manager.
     *
     * @return ModelManager
     */
    protected function getManager()
    {
        if ($this->manager === null) {
            $this->manager = $this->get('models');
        }

        return $this->manager;
    }

    /**
     * Internal helper function to get access to the product repository.
     *
     * @return ProductRepository
     */
    protected function getRepository()
    {
        if ($this->repository === null) {
            $this->repository = $this->get('models')->getRepository(Product::class);
        }

        return $this->repository;
    }

    /**
     * Helper function to get access to the customerGroup repository.
     *
     * @return ModelRepository<CustomerGroup>
     */
    protected function getCustomerGroupRepository()
    {
        if ($this->customerGroupRepository === null) {
            $this->customerGroupRepository = $this->get('models')->getRepository(CustomerGroup::class);
        }

        return $this->customerGroupRepository;
    }

    /**
     * Helper function to get access to the articleDetail repository.
     *
     * @return ProductRepository
     */
    protected function getArticleDetailRepository()
    {
        if ($this->articleDetailRepository === null) {
            $this->articleDetailRepository = $this->get('models')->getRepository(ProductVariant::class);
        }

        return $this->articleDetailRepository;
    }

    /**
     * Internal helper function to get access to the customer repository.
     *
     * @return CustomerRepository
     */
    protected function getCustomerRepository()
    {
        if ($this->customerRepository === null) {
            $this->customerRepository = $this->get('models')->getRepository(Customer::class);
        }

        return $this->customerRepository;
    }

    /**
     * Internal helper function to get access on the shop repository.
     *
     * @return ShopRepository
     */
    protected function getShopRepository()
    {
        if ($this->shopRepository === null) {
            $this->shopRepository = $this->get('models')->getRepository(Shop::class);
        }

        return $this->shopRepository;
    }

    /**
     * Internal helper function to get access on the category repository.
     *
     * @return CategoryRepository
     */
    protected function getCategoryRepository()
    {
        if ($this->categoryRepository === null) {
            $this->categoryRepository = $this->get('models')->getRepository(Category::class);
        }

        return $this->categoryRepository;
    }

    /**
     * Helper function to get access to the ConfiguratorDependency repository.
     *
     * @return ModelRepository<Dependency>
     */
    protected function getConfiguratorDependencyRepository()
    {
        if ($this->configuratorDependencyRepository === null) {
            $this->configuratorDependencyRepository = $this->get('models')->getRepository(Dependency::class);
        }

        return $this->configuratorDependencyRepository;
    }

    /**
     * @deprecated in 5.6, will be removed in 5.7 without a replacement
     *
     * Helper function to get access to the configuratorGroup repository.
     *
     * @return ModelRepository<Group>
     */
    protected function getConfiguratorGroupRepository()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be removed with 5.7. Will be removed without replacement.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        if ($this->configuratorGroupRepository === null) {
            $this->configuratorGroupRepository = $this->get('models')->getRepository(Group::class);
        }

        return $this->configuratorGroupRepository;
    }

    /**
     * Helper function to get access to the configuratorOption repository.
     *
     * @return ModelRepository<Option>
     */
    protected function getConfiguratorOptionRepository()
    {
        if ($this->configuratorOptionRepository === null) {
            $this->configuratorOptionRepository = $this->get('models')->getRepository(Option::class);
        }

        return $this->configuratorOptionRepository;
    }

    /**
     * Helper function to get access to the configuratorSet repository.
     *
     * @return ModelRepository<Set>
     */
    protected function getConfiguratorSetRepository()
    {
        if ($this->configuratorSetRepository === null) {
            $this->configuratorSetRepository = $this->get('models')->getRepository(Set::class);
        }

        return $this->configuratorSetRepository;
    }

    /**
     * Helper function to get access to the Property Value repository.
     *
     * @return ModelRepository<Value>
     */
    protected function getPropertyValueRepository()
    {
        if ($this->propertyValueRepository === null) {
            $this->propertyValueRepository = $this->get('models')->getRepository(Value::class);
        }

        return $this->propertyValueRepository;
    }

    /**
     * Internal helper function to which returns all product variants which are not the main detail
     * or the backend variant.
     *
     * @param int            $articleId
     * @param ProductVariant $mainDetail
     * @param array          $mapping
     *
     * @return array
     */
    protected function getVariantsForMapping($articleId, $mainDetail, $mapping)
    {
        $builder = $this->get('models')->createQueryBuilder();
        $builder->select(['details'])
            ->from(ProductVariant::class, 'details')
            ->where('details.id != ?1')
            ->andWhere('details.articleId = ?2')
            ->setParameter(1, $mainDetail->getId())
            ->setParameter(2, $articleId);

        if (!empty($mapping['variants'])) {
            $ids = [];
            foreach ($mapping['variants'] as $variant) {
                $ids[] = $variant['id'];
            }
            if (!empty($ids)) {
                $builder->andWhere('details.id IN (?3)')
                    ->setParameter(3, $ids);
            }
        }

        return $builder->getQuery()->getResult();
    }

    /**
     * Returns the main detail data for the variant mapping action.
     *
     * @param ProductVariant $mainDetail
     * @param array          $mapping
     *
     * @return array
     */
    protected function getMappingData($mainDetail, $mapping)
    {
        $mainData = [];
        if ($mapping['settings']) {
            $mainData['supplierNumber'] = $mainDetail->getSupplierNumber();
            $mainData['weight'] = $mainDetail->getWeight();
            $mainData['stockMin'] = $mainDetail->getStockMin();
            $mainData['ean'] = $mainDetail->getEan();
            $mainData['minPurchase'] = $mainDetail->getMinPurchase();
            $mainData['purchaseSteps'] = $mainDetail->getPurchaseSteps();
            $mainData['maxPurchase'] = $mainDetail->getMaxPurchase();
            $mainData['releaseDate'] = $mainDetail->getReleaseDate();
            $mainData['shippingTime'] = $mainDetail->getShippingTime();
            $mainData['shippingFree'] = $mainDetail->getShippingFree();
            $mainData['width'] = $mainDetail->getWidth();
            $mainData['height'] = $mainDetail->getHeight();
            $mainData['len'] = $mainDetail->getLen();
            $mainData['lastStock'] = $mainDetail->getLastStock();
        }
        if ($mapping['stock']) {
            $mainData['inStock'] = $mainDetail->getInStock();
        }
        if ($mapping['attributes']) {
            $builder = $this->get('models')->createQueryBuilder();
            $mainData['attribute'] = $builder->select(['attributes'])
                    ->from(ProductAttribute::class, 'attributes')
                    ->where('attributes.articleDetailId = :detailId')
                    ->setParameter('detailId', $mainDetail->getId())
                    ->setFirstResult(0)
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);
            unset($mainData['attribute']['id'], $mainData['attribute']['articleDetailId']);
        }
        if ($mapping['prices']) {
            $builder = $this->get('models')->createQueryBuilder();
            $prices = $builder->select(['prices', 'attribute', 'customerGroup'])
                              ->from(Price::class, 'prices')
                              ->innerJoin('prices.customerGroup', 'customerGroup')
                              ->leftJoin('prices.attribute', 'attribute')
                              ->where('prices.articleDetailsId = ?1')
                              ->setParameter(1, $mainDetail->getId())
                              ->getQuery()
                              ->getArrayResult();

            foreach ($prices as $key => $price) {
                unset($price['id']);
                $price['customerGroup'] = $this->getCustomerGroupRepository()->find($price['customerGroup']['id']);
                $price['article'] = $mainDetail->getArticle();
                $prices[$key] = $price;
            }
            $mainData['prices'] = $prices;
        }
        if ($mapping['basePrice']) {
            $mainData['unit'] = $mainDetail->getUnit();
            $mainData['purchaseUnit'] = $mainDetail->getPurchaseUnit();
            $mainData['referenceUnit'] = $mainDetail->getReferenceUnit();
            $mainData['packUnit'] = $mainDetail->getPackUnit();
        }
        if ($mapping['purchasePrice']) {
            $mainData['purchasePrice'] = $mainDetail->getPurchasePrice();
        }

        return $mainData;
    }

    /**
     * Replaces the variant's translations with the product's.
     *
     * @param int   $articleId
     * @param array $variants
     */
    protected function overrideVariantTranslations($articleId, $variants)
    {
        $coreTranslations = $this->getTranslationComponent()->readBatch(null, 'article', $articleId);

        foreach ($variants as $variant) {
            $this->getTranslationComponent()->delete(null, 'variant', $variant->getId());

            foreach ($coreTranslations as &$coreTranslation) {
                unset($coreTranslation['objectdata']['metaTitle']);
                unset($coreTranslation['objectdata']['name']);
                unset($coreTranslation['objectdata']['description']);
                unset($coreTranslation['objectdata']['descriptionLong']);
                unset($coreTranslation['objectdata']['keywords']);
                $coreTranslation['objectkey'] = $variant->getId();
                $coreTranslation['objecttype'] = 'variant';
            }

            $this->getTranslationComponent()->writeBatch($coreTranslations);
        }
    }

    /**
     * Copies translations from a configurator template into a variant
     *
     * @param array          $template The configurator template
     * @param ProductVariant $detail
     */
    protected function copyConfigurationTemplateTranslations($template, $detail)
    {
        $templateTranslations = $this->getTranslationComponent()->readBatch(null, 'configuratorTemplate', $template['id']);

        foreach ($templateTranslations as &$templateTranslation) {
            $templateTranslation['objectkey'] = $detail->getId();
            $templateTranslation['objecttype'] = 'variant';
        }

        $this->getTranslationComponent()->writeBatch($templateTranslations);
    }

    /**
     * Internal helper function which duplicates the product data of the s_articles.
     *
     * @param int $articleId
     */
    protected function duplicateArticleData($articleId)
    {
        $sql = "INSERT INTO s_articles
               SELECT NULL,
                   supplierID, CONCAT(name, '-', 'Copy'), description, description_long, shippingtime, datum, active, taxID, pseudosales, topseller, metaTitle, keywords, changetime, pricegroupID, pricegroupActive, filtergroupID, laststock, crossbundlelook, notification, template, mode, NULL, available_from, available_to, NULL
               FROM s_articles as source
               WHERE source.id = ?";
        Shopware()->Db()->query($sql, [$articleId]);
    }

    /**
     * Internal helper function which duplicates the assigned categories of the product to the new product.
     *
     * @param int $articleId
     * @param int $newArticleId
     */
    protected function duplicateArticleCategories($articleId, $newArticleId)
    {
        $sql = 'INSERT INTO s_articles_categories
               SELECT NULL, ?, categoryID
               FROM s_articles_categories as source
               WHERE source.articleID = ?
        ';
        Shopware()->Db()->query($sql, [$newArticleId, $articleId]);

        $sql = 'INSERT INTO s_articles_categories_ro
               SELECT NULL, ?, categoryID, parentCategoryID
               FROM s_articles_categories_ro as source
               WHERE source.articleID = ?
        ';
        Shopware()->Db()->query($sql, [$newArticleId, $articleId]);
    }

    /**
     * Internal helper function to duplicate the avoid customer group configuration from the passed product
     * id to the new product.
     *
     * @param int $articleId
     * @param int $newArticleId
     */
    protected function duplicateArticleCustomerGroups($articleId, $newArticleId)
    {
        $sql = 'INSERT INTO s_articles_avoid_customergroups
               SELECT ?, customergroupID
               FROM s_articles_avoid_customergroups as source
               WHERE source.articleID = ?
        ';
        Shopware()->Db()->query($sql, [$newArticleId, $articleId]);
    }

    /**
     * Internal helper function to duplicate the related product configuration from the passed product
     * to the new product.
     *
     * @param int $articleId
     * @param int $newArticleId
     */
    protected function duplicateArticleRelated($articleId, $newArticleId)
    {
        $sql = 'INSERT INTO s_articles_relationships
               SELECT NULL, ?, relatedarticle
               FROM s_articles_relationships as source
               WHERE source.articleID = ?
        ';
        Shopware()->Db()->query($sql, [$newArticleId, $articleId]);
    }

    /**
     * Internal helper function to duplicate the similar product configuration from the passed product
     * to the new product.
     *
     * @param int $articleId
     * @param int $newArticleId
     */
    protected function duplicateArticleSimilar($articleId, $newArticleId)
    {
        $sql = 'INSERT INTO s_articles_similar
               SELECT NULL, ?, relatedarticle
               FROM s_articles_similar as source
               WHERE source.articleID = ?
        ';
        Shopware()->Db()->query($sql, [$newArticleId, $articleId]);
    }

    /**
     * Internal helper function to duplicate the product link configuration from the passed product
     * to the new product.
     *
     * @param int $articleId
     * @param int $newArticleId
     */
    protected function duplicateArticleLinks($articleId, $newArticleId)
    {
        $product = $this->get('models')->find(Product::class, $newArticleId);

        $builder = $this->get('models')->createQueryBuilder();
        $links = $builder->select(['links', 'attribute'])
            ->from(Link::class, 'links')
            ->leftJoin('links.attribute', 'attribute')
            ->where('links.articleId = ?1')
            ->setParameter(1, $articleId)
            ->getQuery()
            ->getArrayResult();

        foreach ($links as $data) {
            $link = new Link();
            $link->fromArray($data);
            $link->setArticle($product);
            $this->get('models')->persist($link);
        }
        $this->get('models')->flush();
    }

    /**
     * Internal helper function to duplicate the product translations from the passed product
     * to the new product.
     *
     * @param int $articleId
     * @param int $newArticleId
     */
    protected function duplicateArticleTranslations($articleId, $newArticleId)
    {
        $coreTranslations = $this->getTranslationComponent()->readBatch(null, 'article', $articleId);

        foreach ($coreTranslations as &$coreTranslation) {
            $coreTranslation['objectkey'] = $newArticleId;
        }

        $this->getTranslationComponent()->writeBatch($coreTranslations);
    }

    /**
     * Internal helper function to duplicate the download configuration from the passed product
     * to the new product.
     *
     * @param int $articleId
     * @param int $newArticleId
     */
    protected function duplicateArticleDownloads($articleId, $newArticleId)
    {
        $product = $this->get('models')->find(Product::class, $newArticleId);

        $builder = $this->get('models')->createQueryBuilder();
        $downloads = $builder->select(['downloads', 'attribute'])
            ->from(Download::class, 'downloads')
            ->leftJoin('downloads.attribute', 'attribute')
            ->where('downloads.articleId = ?1')
            ->setParameter(1, $articleId)
            ->getQuery()
            ->getArrayResult();

        foreach ($downloads as $data) {
            $download = new Download();
            $download->fromArray($data);
            $download->setArticle($product);
            $this->get('models')->persist($download);
        }
        $this->get('models')->flush();
    }

    /**
     * Internal helper function to duplicate the image configuration from the passed product
     * to the new product.
     *
     * @param int $articleId
     * @param int $newArticleId
     */
    protected function duplicateArticleImages($articleId, $newArticleId)
    {
        $product = $this->get('models')->find(Product::class, $newArticleId);

        $builder = $this->get('models')->createQueryBuilder();
        $images = $builder->select(['images', 'media', 'attribute', 'mappings', 'rules', 'option'])
            ->from(Image::class, 'images')
            ->leftJoin('images.attribute', 'attribute')
            ->leftJoin('images.mappings', 'mappings')
            ->leftJoin('images.media', 'media')
            ->leftJoin('mappings.rules', 'rules')
            ->leftJoin('rules.option', 'option')
            ->where('images.articleId = ?1')
            ->andWhere('images.parentId IS NULL')
            ->setParameter(1, $articleId)
            ->getQuery()
            ->getArrayResult();

        foreach ($images as &$data) {
            if (!empty($data['mappings'])) {
                foreach ($data['mappings'] as $mappingKey => $mapping) {
                    foreach ($mapping['rules'] as $ruleKey => $rule) {
                        $option = $this->get('models')->find(Option::class, $rule['optionId']);
                        if ($option) {
                            $rule['option'] = $option;
                            $data['mappings'][$mappingKey]['rules'][$ruleKey]['option'] = $option;
                        }
                    }
                }
            }

            if (!empty($data['mediaId'])) {
                $data['media'] = $this->get('models')->find(Media::class, $data['mediaId']);
                if (!$data['media']) {
                    unset($data['media']);
                }
            }

            $image = new Image();
            $image->fromArray($data);
            $image->setArticle($product);
            $image->setArticleDetail(null);

            $this->get('models')->persist($image);
        }

        $this->get('models')->flush();
    }

    /**
     * Internal helper function to duplicate the property configuration from the passed product
     * to the new product.
     *
     * @param int $articleId
     * @param int $newArticleId
     */
    protected function duplicateArticleProperties($articleId, $newArticleId)
    {
        $sql = 'INSERT INTO s_filter_articles
               SELECT ?, valueID
               FROM s_filter_articles as source
               WHERE source.articleID = ?
        ';
        Shopware()->Db()->query($sql, [$newArticleId, $articleId]);
    }

    /**
     * Internal helper function to duplicate the variant configuration from the passed product
     * to the new product.
     *
     * @param int $articleId
     * @param int $newArticleId
     * @param int $mailDetailId
     */
    protected function duplicateArticleDetails($articleId, $newArticleId, $mailDetailId = null)
    {
        $product = $this->get('models')->find(Product::class, $newArticleId);
        $builder = $this->get('models')->createQueryBuilder();
        $builder->select(['details', 'prices', 'attribute', 'images'])
            ->from(ProductVariant::class, 'details')
            ->leftJoin('details.prices', 'prices')
            ->leftJoin('details.attribute', 'attribute')
            ->leftJoin('details.images', 'images')
            ->where('details.articleId = ?1');

        if ($mailDetailId !== null) {
            $builder->andWhere('details.id = ?2');
            $details = $builder->setParameter(1, $articleId)
                ->setParameter(2, $mailDetailId)
                ->getQuery()
                ->getArrayResult();
        } else {
            $details = $builder->setParameter(1, $articleId)
                ->getQuery()
                ->getArrayResult();
        }

        $newProductData = $this->getNewArticleData();
        $number = $newProductData['number'];

        foreach ($details as $data) {
            $prices = [];
            $data['number'] = $number;
            $detail = new ProductVariant();

            foreach ($data['prices'] as $priceData) {
                if (empty($priceData['customerGroupKey'])) {
                    continue;
                }
                $customerGroup = $this->getCustomerGroupRepository()->findOneBy(['key' => $priceData['customerGroupKey']]);
                if ($customerGroup instanceof CustomerGroup) {
                    $priceData['customerGroup'] = $customerGroup;
                    $priceData['article'] = $product;
                    $prices[] = $priceData;
                }
            }
            $data['prices'] = $prices;

            // unset configuratorOptions and images. These are variant specific and are going to be recreated later
            unset($data['images'], $data['configuratorOptions']);

            if (!empty($data['unitId'])) {
                $data['unit'] = $this->get('models')->find(Unit::class, $data['unitId']);
            } else {
                $data['unit'] = null;
            }

            $data['article'] = $product;

            $detail->fromArray($data);
            $this->get('models')->persist($detail);
            if ($detail->getAttribute()) {
                $this->get('models')->persist($detail->getAttribute());
            }
        }
        $this->get('models')->flush();

        $this->increaseAutoNumber($newProductData['autoNumber'], $number);
    }

    /**
     * @param int $articleId
     *
     * @return int|null
     */
    protected function duplicateArticleConfigurator($articleId)
    {
        $unique = uniqid();

        $oldProduct = $this->get('models')->find(Product::class, $articleId);
        if (!$oldProduct->getConfiguratorSet()) {
            return null;
        }

        $oldSetId = $oldProduct->getConfiguratorSet()->getId();

        $sql = "INSERT INTO s_article_configurator_sets
                SELECT NULL, CONCAT(name, '-', '" . $unique . "'), public, type
                FROM s_article_configurator_sets as source
                WHERE source.id = ?";

        Shopware()->Db()->query($sql, [$oldSetId]);
        $newSetId = (int) Shopware()->Db()->lastInsertId('s_article_configurator_sets');

        $sql = 'INSERT INTO s_article_configurator_set_group_relations
                SELECT ?, group_id
                FROM s_article_configurator_set_group_relations as source
                WHERE source.set_id = ?';
        Shopware()->Db()->query($sql, [$newSetId, $oldSetId]);

        $sql = 'INSERT INTO s_article_configurator_set_option_relations
                SELECT ?, option_id
                FROM s_article_configurator_set_option_relations as source
                WHERE source.set_id = ?';
        Shopware()->Db()->query($sql, [$newSetId, $oldSetId]);

        $sql = 'INSERT INTO s_article_configurator_dependencies
                SELECT NULL, ?, parent_id, child_id
                FROM s_article_configurator_dependencies as source
                WHERE source.configurator_set_id = ?';
        Shopware()->Db()->query($sql, [$newSetId, $oldSetId]);

        $sql = 'INSERT INTO s_article_configurator_price_variations
                SELECT NULL, ?, variation, options, is_gross
                FROM s_article_configurator_price_variations as source
                WHERE source.configurator_set_id = ?';
        Shopware()->Db()->query($sql, [$newSetId, $oldSetId]);

        return $newSetId;
    }

    /**
     * Internal helper function to remove all product variants.
     *
     * @param int $articleId
     */
    protected function removeAllConfiguratorVariants($articleId)
    {
        $builder = $this->get('models')->createQueryBuilder();
        $details = $builder->select(['details', 'configuratorOptions'])
            ->from(ProductVariant::class, 'details')
            ->innerJoin('details.configuratorOptions', 'configuratorOptions')
            ->where('details.articleId = ?1')
            ->setParameter(1, $articleId)
            ->getQuery()
            ->getArrayResult();

        $product = $this->getRepository()->find($articleId);
        if (!$product instanceof Product) {
            throw new ModelNotFoundException(Product::class, $articleId);
        }
        $mainDetailId = $product->getMainDetail()->getId();

        if (empty($details)) {
            return;
        }

        $detailIds = [];
        foreach ($details as $detail) {
            if (empty($detail['configuratorOptions'])) {
                continue;
            }
            if ($mainDetailId == $detail['id']) {
                continue;
            }
            $detailIds[] = $detail['id'];
        }

        if (\count($detailIds) === 0) {
            return;
        }

        $builder = $this->get('models')->createQueryBuilder();
        $builder->delete(ProductAttribute::class, 'details')
            ->andWhere('details.articleDetailId IN (?1)')
            ->setParameter(1, $detailIds)
            ->getQuery()
            ->execute();

        $builder = $this->get('models')->createQueryBuilder();
        $builder->delete(ProductVariant::class, 'details')
            ->andWhere('details.id IN (?1)')
            ->setParameter(1, $detailIds)
            ->getQuery()
            ->execute();

        $this->get(Connection::class)->createQueryBuilder()
            ->delete('s_article_configurator_option_relations')
            ->where('article_id IN (:detailIds)')
            ->setParameter('detailIds', $detailIds, Connection::PARAM_INT_ARRAY)
            ->execute();

        $builder = $this->get('models')->createQueryBuilder();
        $builder->delete(Price::class, 'prices')
            ->andWhere('prices.articleDetailsId IN (?1)')
            ->setParameter(1, $detailIds)
            ->getQuery()
            ->execute();
    }

    /**
     * @param array $options
     * @param array $imageData
     * @param Image $parent
     */
    protected function createImagesForOptions($options, $imageData, $parent)
    {
        $productId = $parent->getArticle()->getId();
        $imageData['path'] = null;
        $imageData['parent'] = $parent;

        $join = '';
        foreach ($options as $option) {
            $alias = 'alias' . $option->getId();
            $join = $join . ' INNER JOIN s_article_configurator_option_relations alias' . $option->getId() .
                ' ON ' . $alias . '.option_id = ' . $option->getId() .
                ' AND ' . $alias . '.article_id = d.id ';
        }
        $sql = 'SELECT d.id
                FROM s_articles_details d
        ' . $join . '
        WHERE d.articleID = ' . (int) $productId;

        $details = Shopware()->Db()->fetchCol($sql);

        foreach ($details as $detailId) {
            $detail = $this->get('models')->getReference(ProductVariant::class, $detailId);
            $image = new Image();
            $image->fromArray($imageData);
            $image->setArticleDetail($detail);
            $this->get('models')->persist($image);
        }
        $this->get('models')->flush();
    }

    /**
     * @param array          $data
     * @param ProductVariant $detail
     *
     * @return ProductVariant
     */
    protected function saveDetail($data, $detail)
    {
        $product = $detail->getArticle();
        $data['prices'] = $this->preparePricesAssociatedData($data['prices'], $product, $product->getTax());
        $data['article'] = $product;
        unset($data['images']);
        if (!empty($data['unitId'])) {
            $data['unit'] = $this->get('models')->find(Unit::class, $data['unitId']);
        } else {
            $data['unit'] = null;
        }

        unset($data['configuratorOptions']);
        $detail->fromArray($data);
        $this->get('models')->persist($detail);
        $this->get('models')->flush();
        $this->get('models')->clear();

        $variant = $this->getArticleDetailRepository()->find($detail->getId());
        if (!$variant instanceof ProductVariant) {
            throw new ModelNotFoundException(ProductVariant::class, $detail->getId());
        }

        if ($data['standard']) {
            $product = $variant->getArticle();
            $mainDetail = $product->getMainDetail();
            if (!$mainDetail instanceof ProductVariant) {
                throw new ModelNotFoundException(ProductVariant::class, $detail->getId());
            }

            $mainDetail->setKind(2);
            $product->setMainDetail($variant);
            $this->get('models')->persist($mainDetail);
            $this->get('models')->persist($product);
            $this->get('models')->flush();

            // If main variant changed, swap translations
            if ($mainDetail->getId() !== $variant->getId()) {
                $this->container->get(SwapProductVariantTranslationService::class)->swapProductVariantTranslation($variant, $mainDetail);
            }
        }

        return $variant;
    }

    /**
     * Internal helper function to save the product data.
     *
     * @param array   $data
     * @param Product $article
     */
    protected function saveArticle($data, $article)
    {
        $data = $this->prepareAssociatedData($data, $article);

        $article->fromArray($data);

        $this->get('models')->persist($article);
        $this->get('models')->flush();
        if (empty($data['id']) && !empty($data['autoNumber'])) {
            $this->increaseAutoNumber($data['autoNumber'], $article->getMainDetail()->getNumber());
        }

        $savedProduct = $this->getArticle($article->getId());
        $this->View()->assign([
            'success' => true,
            'data' => $savedProduct,
        ]);
    }

    /**
     * Used for the product backend module to load the product data into
     * the module. This function selects only some fragments for the whole product
     * data. The full product data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     *
     * @param int $articleId
     *
     * @return array
     */
    protected function getArticleData($articleId)
    {
        return $this->getRepository()
            ->getArticleBaseDataQuery($articleId)
            ->getArrayResult();
    }

    /**
     * @param int $articleId
     *
     * @return array
     */
    protected function getArticleSeoCategories($articleId)
    {
        $builder = $this->getManager()->createQueryBuilder();
        $builder->select(['seoCategories', 'category', 'shop'])
            ->from(SeoCategory::class, 'seoCategories')
            ->innerJoin('seoCategories.shop', 'shop')
            ->innerJoin('seoCategories.category', 'category')
            ->where('seoCategories.articleId = :articleId')
            ->setParameter('articleId', $articleId);

        return $builder->getQuery()->getArrayResult();
    }

    /**
     * Internal helper function to get the product data of the passed id.
     *
     * @param int $id
     *
     * @return array
     */
    protected function getArticle($id)
    {
        $data = $this->getArticleData($id);

        $tax = $data[0]['tax'];

        $data[0]['categories'] = $this->getArticleCategories($id);
        $data[0]['seoCategories'] = $this->getArticleSeoCategories($id);

        $data[0]['similar'] = $this->getArticleSimilars($id);
        $data[0]['streams'] = $this->getArticleRelatedProductStreams($id);
        $data[0]['related'] = $this->getArticleRelated($id);
        $data[0]['images'] = $this->getArticleImages($id);

        $data[0]['links'] = $this->getArticleLinks($id);
        $data[0]['downloads'] = $this->getArticleDownloads($id);
        $data[0]['customerGroups'] = $this->getArticleCustomerGroups($id);
        $data[0]['mainPrices'] = $this->getPrices($data[0]['mainDetail']['id'], $tax);
        $data[0]['configuratorSet'] = $this->getArticleConfiguratorSet($id);

        $data[0]['dependencies'] = [];

        if (!empty($data[0]['configuratorSetId'])) {
            $data[0]['dependencies'] = $this->getArticleDependencies($data[0]['configuratorSetId']);
        }

        $data[0]['configuratorTemplate'] = $this->getArticleConfiguratorTemplate($id, $tax);

        if ($data[0]['added'] && $data[0]['added'] instanceof DateTime) {
            $added = $data[0]['added'];
            $data[0]['added'] = $added->format('d.m.Y');
        }

        return $data;
    }

    /**
     * Internal helper function to convert gross prices to net prices.
     *
     * @param array $prices
     * @param array $tax
     *
     * @return array
     */
    protected function formatPricesFromNetToGross($prices, $tax)
    {
        foreach ($prices as $key => $price) {
            $customerGroup = $price['customerGroup'];
            if ($customerGroup['taxInput']) {
                $price['price'] = $price['price'] / 100 * (100 + $tax['tax']);
                $price['pseudoPrice'] = $price['pseudoPrice'] / 100 * (100 + $tax['tax']);
                $price['regulationPrice'] = $price['regulationPrice'] / 100 * (100 + $tax['tax']);
            }
            $prices[$key] = $price;
        }

        return $prices;
    }

    /**
     * Internal helper function to load the product main detail prices into the backend module.
     *
     * @param int   $id
     * @param array $tax
     *
     * @return array
     */
    protected function getPrices($id, $tax)
    {
        $prices = $this->getRepository()
            ->getPricesQuery($id)
            ->getArrayResult();

        return $this->formatPricesFromNetToGross($prices, $tax);
    }

    /**
     * Helper function which creates for the passed configurator groups
     * the cross join sql for all possible variants.
     * Returns an array with the sql and all used group ids
     *
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    protected function prepareGeneratorData(array $groups, $offset, $limit)
    {
        // We have to iterate all passed groups to check the activated options.
        $activeGroups = [];
        // We need a second array with all group ids to iterate them easily in the sql generation
        $originals = [];
        $allOptions = [];

        $groupPositions = [];

        foreach ($groups as $group) {
            if (!$group['active']) {
                continue;
            }

            $options = [];
            // We iterate the options to get the option ids in a one dimensional array.
            foreach ($group['options'] as $option) {
                if ($option['active']) {
                    $options[] = (int) $option['id'];
                    $allOptions[$option['id']] = (int) $option['id'];
                }
            }

            // If some options active, we save the group and the options in an internal array
            if (!empty($options)) {
                $group['id'] = (int) $group['id'];
                $activeGroups[] = ['id' => $group['id'], 'options' => $options];
                $groupPositions[$group['id']] = (int) $group['position'];
                $originals[] = $group['id'];
            }
        }

        if (empty($activeGroups)) {
            return [];
        }

        // The first groups serves as the sql from path, so we have to remove the first id from the array
        $first = array_shift($activeGroups);
        $firstId = $first['id'];

        // Now we create plain sql templates to parse the ids over the sprintf function
        $selectTemplate = 'o%s.id as o%sId, o%s.name as o%sName, g%s.id as g%sId, g%s.name as g%sName, o%s.position as o%sPosition, g%s.position as g%sPosition ';

        $fromTemplate = 'FROM s_article_configurator_options o%s
                            LEFT JOIN s_article_configurator_groups g%s ON g%s.id = o%s.group_id';

        $joinTemplate = 'CROSS JOIN s_article_configurator_options o%s ON o%s.group_id = %s AND o%s.id IN (%s)
                            LEFT JOIN s_article_configurator_groups g%s ON g%s.id = o%s.group_id';

        $whereTemplate = 'WHERE o%s.group_id = %s
                          AND o%s.id IN (%s)';

        asort($groupPositions);
        $orders = [];
        foreach ($groupPositions as $id => $position) {
            $orders[] = 'g' . $id . 'Position, o' . $id . 'Position';
        }
        $orderBy = ' ORDER BY ' . implode(' , ', $orders);

        $groupSql = [];
        $selectSql = [];

        // We have remove the first group id, but we need the first id in the select, from and where path.
        $selectSql[] = sprintf($selectTemplate, $firstId, $firstId, $firstId, $firstId, $firstId, $firstId, $firstId, $firstId, $firstId, $firstId, $firstId, $firstId);
        $groupSql[] = sprintf($fromTemplate, $firstId, $firstId, $firstId, $firstId);
        $whereSql = sprintf($whereTemplate, $firstId, $firstId, $firstId, implode(',', $first['options']));

        // Now we iterate all other groups, and create a select sql path and a cross join sql path.
        foreach ($activeGroups as $group) {
            $groupId = (int) $group['id'];
            $selectSql[] = sprintf($selectTemplate, $groupId, $groupId, $groupId, $groupId, $groupId, $groupId, $groupId, $groupId, $groupId, $groupId, $groupId, $groupId);
            $groupSql[] = sprintf($joinTemplate, $groupId, $groupId, $groupId, $groupId, implode(',', $group['options']), $groupId, $groupId, $groupId);
        }

        // Concat the sql statement
        $sql = 'SELECT ' . implode(",\n", $selectSql) . ' ' . implode("\n", $groupSql) . ' ' . $whereSql . $orderBy . ' LIMIT ' . $offset . ',' . $limit;

        return [
            'sql' => $sql,
            'originals' => $originals,
            'allOptions' => $allOptions,
        ];
    }

    /**
     * Internal helper function to remove all product variants for the deselected options.
     *
     * @param Product $article
     * @param array   $selectedOptions
     */
    protected function deleteVariantsForAllDeactivatedOptions($article, $selectedOptions)
    {
        $configuratorSet = $article->getConfiguratorSet();
        if (!$configuratorSet instanceof Set) {
            return;
        }
        $oldOptions = $configuratorSet->getOptions();
        $ids = [];
        foreach ($oldOptions as $oldOption) {
            if (!\array_key_exists($oldOption->getId(), $selectedOptions)) {
                $details = $this->getRepository()
                    ->getArticleDetailByConfiguratorOptionIdQuery($article->getId(), $oldOption->getId())
                    ->setHydrationMode(AbstractQuery::HYDRATE_OBJECT)
                    ->getResult();

                if (!empty($details)) {
                    foreach ($details as $detail) {
                        if ($detail->getKind() === 1) {
                            $article->setMainDetail(null);
                        }
                        $ids[] = $detail->getId();
                        $this->get('models')->remove($detail);
                    }
                    $this->get('models')->flush();
                }
            }
        }

        if (!empty($ids)) {
            $builder = $this->get('models')->createQueryBuilder();
            $builder->delete(ProductAttribute::class, 'attribute')
                ->where('attribute.articleDetailId IN (:articleDetailIds)')
                ->setParameter('articleDetailIds', $ids)
                ->getQuery()
                ->execute();
        }
    }

    /**
     * Helper function to prepare the variant data for a new product detail.
     * Iterates all passed price variations and dependencies to check if the current variant
     * has configurator options which defined in the dependencies or in the price variations.
     * The used price variation options will be added to each variant price row.
     * If the variant has configurator options which defined as dependency row,
     * the variant won't be created. The function will return false and
     * the foreach queue in the "createConfiguratorVariantsAction" will be continue.
     *
     * @param array   $variant
     * @param array   $detailData
     * @param int     $counter
     * @param array   $dependencies
     * @param array   $priceVariations
     * @param array   $allOptions
     * @param array   $originals
     * @param Product $article
     * @param int     $mergeType
     *
     * @return array|false
     */
    protected function prepareVariantData($variant, $detailData, &$counter, $dependencies, $priceVariations, $allOptions, $originals, $article, $mergeType)
    {
        $optionsModels = [];
        $tax = $article->getTax();
        $optionIds = [];

        // Iterate the original ids to get the new variant name
        foreach ($originals as $id) {
            $optionId = $variant['o' . $id . 'Id'];

            // First we push the option ids in an one dimensional array to check
            $optionIds[] = $optionId;

            $optionsModels[] = $allOptions[$optionId];
        }

        $abortVariant = false;
        foreach ($dependencies as $dependency) {
            if (\in_array($dependency['parentId'], $optionIds) && \in_array($dependency['childId'], $optionIds)) {
                $abortVariant = true;
                break;
            }
        }

        // If the user selects the "merge variants" generation type, we have to check if the current variant already exist.
        if ($mergeType === 2 && $abortVariant === false) {
            $query = $this->getRepository()->getDetailsForOptionIdsQuery($article->getId(), $optionsModels);
            $exist = $query->getArrayResult();
            $abortVariant = !empty($exist);
        }

        if ($abortVariant) {
            return false;
        }

        // create the new variant data
        $variantData = [
            'active' => 1,
            'configuratorOptions' => $optionsModels,
            'purchasePrice' => $detailData['purchasePrice'],
            'lastStock' => $detailData['lastStock'],
        ];

        if ($mergeType == 1 && $counter == 0) {
            $variantData['number'] = $detailData['number'];
            ++$counter;
        } else {
            do {
                $variantData['kind'] = 2;
                $variantData['number'] = $detailData['number'] . '.' . $counter;
                ++$counter;
            } while ($this->orderNumberExist($variantData['number']));
        }

        $prices = $this->prepareVariantPrice($detailData, $priceVariations, $optionIds, $tax);
        if ($prices) {
            $variantData['prices'] = $prices;
        }

        return $variantData;
    }

    /**
     * Prepares variant prices according to the price variation rules
     * Returns prices array
     *
     * @param array $detailData
     * @param array $priceVariations
     * @param array $optionIds
     * @param Tax   $tax
     *
     * @return array
     */
    protected function prepareVariantPrice($detailData, $priceVariations, $optionIds, $tax)
    {
        $totalPriceVariationValue = 0;
        foreach ($priceVariations as $priceVariation) {
            $priceVariation['options'] = explode('|', trim($priceVariation['options'], '|'));

            $optionsDiff = array_diff($priceVariation['options'], $optionIds);
            if (!empty($optionsDiff)) {
                continue;
            }

            $priceVariationValue = $priceVariation['variation'];
            if ($priceVariation['isGross'] == 1) {
                $taxValue = (float) $tax->getTax();
                $priceVariationValue /= ($taxValue + 100) / 100;
            }

            $totalPriceVariationValue += $priceVariationValue;
        }

        foreach ($detailData['prices'] as $key => &$configuratorPrice) {
            $calculatedPrice = $configuratorPrice['price'] + $totalPriceVariationValue;
            $configuratorPrice['price'] = max($calculatedPrice, 0.01);
        }

        return $detailData['prices'];
    }

    /**
     * @deprecated in 5.6, will be removed in 5.7 without a replacement
     */
    protected function getDependencyByOptionId($optionId, $dependencies)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be removed with 5.7. Will be removed without replacement.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $returnValue = [];
        foreach ($dependencies as $dependency) {
            if ($dependency['parentId'] == $optionId) {
                $returnValue = $dependency;
                break;
            }
        }

        return $returnValue;
    }

    /**
     * Helper function for the variant generation. Returns the product main detail data which used as base configuration for
     * the generated product variants.
     *
     * @param Product $article
     *
     * @return array
     */
    protected function getDetailDataForVariantGeneration($article)
    {
        $detailData = $this->getRepository()
            ->getConfiguratorTemplateByArticleIdQuery($article->getId())
            ->getArrayResult();

        if (empty($detailData)) {
            $this->createConfiguratorTemplate($article);
            $detailData = $this->getRepository()
                ->getConfiguratorTemplateByArticleIdQuery($article->getId())
                ->getArrayResult();
        }

        return $detailData[0];
    }

    /**
     * @param Product $article
     */
    protected function createConfiguratorTemplate($article)
    {
        $builder = $this->get('models')->createQueryBuilder();
        $builder->select(['detail', 'prices', 'customerGroup', 'attribute', 'priceAttribute'])
            ->from(ProductVariant::class, 'detail')
            ->leftJoin('detail.prices', 'prices')
            ->leftJoin('prices.customerGroup', 'customerGroup')
            ->leftJoin('detail.attribute', 'attribute')
            ->leftJoin('prices.attribute', 'priceAttribute')
            ->where('detail.id = :id')
            ->setParameter('id', $article->getMainDetail()->getId());

        $data = $builder->getQuery()->getArrayResult();
        $data = $data[0];

        foreach ($data['prices'] as &$price) {
            $customerGroup = $this->getCustomerGroupRepository()->find($price['customerGroup']['id']);
            $price['customerGroup'] = $customerGroup;
        }

        $template = new Template();
        $template->fromArray($data);
        $template->setArticle($article);

        if ($data['unitId']) {
            $productUnit = $this->get('models')->find(Unit::class, $data['unitId']);
            if ($productUnit !== null) {
                $template->setUnit($productUnit);
            }
        }

        $this->get('models')->persist($template);
        $this->get('models')->flush();

        $this->createConfiguratorTemplateTranslations($template);
    }

    /**
     * Copies all translations from an product into the respective configurator template
     */
    protected function createConfiguratorTemplateTranslations(Template $template)
    {
        $productTranslations = $this->getTranslationComponent()->readBatch(null, 'article', $template->getArticle()->getId());

        foreach ($productTranslations as &$productTranslation) {
            unset(
                $productTranslation['objectdata']['metaTitle'],
                $productTranslation['objectdata']['name'],
                $productTranslation['objectdata']['description'],
                $productTranslation['objectdata']['descriptionLong'],
                $productTranslation['objectdata']['shippingTime'],
                $productTranslation['objectdata']['keywords']
            );
            $productTranslation['objectkey'] = $template->getId();
            $productTranslation['objecttype'] = 'configuratorTemplate';
        }

        $this->getTranslationComponent()->writeBatch($productTranslations);
    }

    /**
     * This function prepares the posted extJs data. First all ids resolved to the assigned shopware models.
     * After the ids resolved, the function removes the two dimensional arrays of oneToOne associations.
     *
     * @param array   $data
     * @param Product $article
     *
     * @return array
     */
    protected function prepareAssociatedData($data, $article)
    {
        // Format the posted extJs product data
        $data = $this->prepareArticleAssociatedData($data);

        // Format the posted extJs product main detail data
        $data = $this->prepareMainDetailAssociatedData($data);

        // Format the posted extJs product main prices data
        $data = $this->prepareMainPricesAssociatedData($data, $article);

        $data = $this->prepareAvoidCustomerGroups($data);

        // Format the posted extJs product configurator association.
        $data = $this->prepareConfiguratorAssociatedData($data, $article);

        // Format the posted extJs product categories associations
        $data = $this->prepareCategoryAssociatedData($data);

        $data = $this->prepareSeoCategoryAssociatedData($data, $article);

        // Format the posted extJs related product association
        $data = $this->prepareRelatedAssociatedData($data, $article);

        // Format the posted extJs related product streams association
        $data = $this->prepareRelatedProductStreamsData($data);

        // Format the posted extJs similar product association
        $data = $this->prepareSimilarAssociatedData($data, $article);

        // Format the posted extJs product image data
        $data = $this->prepareImageAssociatedData($data);

        // Format the posted extJs product link data
        $data = $this->prepareLinkAssociatedData($data);

        // Format the posted extJs product download data
        $data = $this->prepareDownloadAssociatedData($data);

        $data = $this->prepareConfiguratorTemplateData($data, $article);

        return $data;
    }

    /**
     * Internal helper function which resolves the passed configurator template foreign keys
     * with the associated models.
     *
     * @param array   $data
     * @param Product $article
     */
    protected function prepareConfiguratorTemplateData($data, $article)
    {
        if (empty($data['configuratorTemplate'])) {
            unset($data['configuratorTemplate']);

            return $data;
        }
        $data['configuratorTemplate'] = $data['configuratorTemplate'][0];

        if (empty($data['configuratorTemplate'])) {
            $data['configuratorTemplate'] = null;

            return $data;
        }

        if (!empty($data['configuratorTemplate']['unitId'])) {
            $data['configuratorTemplate']['unit'] = $this->get('models')->find(Unit::class, $data['configuratorTemplate']['unitId']);
        } else {
            $data['configuratorTemplate']['unit'] = null;
        }

        $data['configuratorTemplate']['prices'] = $this->preparePricesAssociatedData($data['configuratorTemplate']['prices'], $article, $data['tax']);
        $data['configuratorTemplate']['article'] = $article;

        return $data;
    }

    /**
     * Internal helper function which resolves the passed customer group ids
     * with \Shopware\Models\Customer\Group models.
     * The configured customer groups are not allowed to set the product in the store front.
     *
     * @param array $data
     */
    protected function prepareAvoidCustomerGroups($data)
    {
        if (!empty($data['customerGroups'])) {
            $customerGroups = [];
            foreach ($data['customerGroups'] as $customerGroup) {
                if (!empty($customerGroup['id'])) {
                    $customerGroups[] = $this->get('models')->find(CustomerGroup::class, $customerGroup['id']);
                }
            }
            $data['customerGroups'] = $customerGroups;
        } else {
            $data['customerGroups'] = null;
        }

        return $data;
    }

    /**
     * Internal helper function to check if the product is configured as
     * multiple dimensional product (Configurator activated).
     * The following scenarios are possible:
     * <code>
     *  - New Product
     *    --> Checkbox activated
     *    --> "isConfigurator" = true  / configuratorSetId = null
     *    --> A new configurator set will be created with the name "Set-ProductNumber"
     *
     *  - Existing Product
     *    --> Checkbox wasn't activated before, now the user activated the checkbox
     *    --> "isConfigurator" = true  / configuratorSetId = null
     *    --> A new configurator set will be created with the name "Set-ProductNumber"
     *
     *  - Existing Product
     *    --> Checkbox was activated before, now the user deactivated the checkbox
     *    --> "isConfigurator" = false / configuratorSetId = Some Numeric value
     *    --> The old configurator set will be deleted.
     *
     * </code>
     *
     * @param array   $data
     * @param Product $article
     *
     * @return array
     */
    protected function prepareConfiguratorAssociatedData($data, $article)
    {
        $modelManger = $this->get('models');
        if (!empty($data['configuratorSetId'])) {
            $data['configuratorSet'] = $modelManger->find(Set::class, $data['configuratorSetId']);
        } elseif ($data['isConfigurator']) {
            $set = new Set();
            $set->setName('Set-' . $data['mainDetail']['number']);
            $set->setPublic(false);
            $data['configuratorSet'] = $set;
        } else {
            // If the product has a configurator set, we have to remove this set if it isn't used for other products
            if ($article->getConfiguratorSet() && $article->getConfiguratorSet()->getId()) {
                $builder = $modelManger->createQueryBuilder();
                $products = $builder->select(['articles'])
                    ->from(Product::class, 'articles')
                    ->where('articles.configuratorSetId = ?1')
                    ->setParameter(1, $article->getConfiguratorSet()->getId())
                    ->getQuery()
                    ->getArrayResult();

                if (\count($products) <= 1) {
                    $set = $modelManger->find(Set::class, $article->getConfiguratorSet()->getId());
                    $modelManger->remove($set);
                }

                $variant = $article->getMainDetail();
                if ($variant instanceof ProductVariant && $variant->getConfiguratorOptions()->count() > 0) {
                    $modelManger->getConnection()->delete('s_article_configurator_option_relations', [
                        'article_id' => $variant->getId(),
                    ]);

                    $data['mainDetail']['additionalText'] = '';
                }
            }
            $data['configuratorSet'] = null;
        }

        return $data;
    }

    /**
     * This function prepares the posted extJs data of the product model.
     *
     * @param array $data
     *
     * @return array
     */
    protected function prepareArticleAssociatedData($data)
    {
        // Check if a tax id is passed and load the tax model or set the tax parameter to null.
        if (!empty($data['taxId'])) {
            $data['tax'] = $this->get('models')->find(Tax::class, $data['taxId']);
        } else {
            $data['tax'] = null;
        }

        // Check if a supplier id is passed and load the supplier model or set the supplier parameter to null.
        if (!empty($data['supplierId'])) {
            $data['supplier'] = $this->get('models')->find(Supplier::class, $data['supplierId']);
        } elseif (!empty($data['supplierName'])) {
            $supplier = $this->getManager()->getRepository(Supplier::class)->findOneBy(['name' => trim($data['supplierName'])]);
            if (!$supplier) {
                $supplier = new Supplier();
                $supplier->setName($data['supplierName']);
            }
            $data['supplier'] = $supplier;
        } else {
            $data['supplier'] = null;
        }

        // Check if a supplier id is passed and load the supplier model or set the supplier parameter to null.
        if (!empty($data['priceGroupId'])) {
            $data['priceGroup'] = $this->get('models')->find(PriceGroup::class, $data['priceGroupId']);
        } else {
            $data['priceGroup'] = null;
        }

        if (!empty($data['filterGroupId'])) {
            $data['propertyGroup'] = $this->get('models')->find(PropertyGroup::class, $data['filterGroupId']);
        } else {
            $data['propertyGroup'] = null;
        }

        // The 'changed' value (time of last change of the dataset) gets automatically updated in the doctrine model
        unset($data['changed']);

        return $data;
    }

    /**
     * Prepares the data for the product main detail object.
     *
     * @param array $data
     *
     * @return array
     */
    protected function prepareMainDetailAssociatedData($data)
    {
        $data['mainDetail'] = $data['mainDetail'][0];
        $data['mainDetail']['active'] = $data['active'];
        $data['mainDetail']['lastStock'] = (int) ($data['lastStock'] >= 0 ? $data['lastStock'] : 0);

        if (!empty($data['mainDetail']['unitId'])) {
            $data['mainDetail']['unit'] = $this->get('models')->find(Unit::class, $data['mainDetail']['unitId']);
        } else {
            $data['mainDetail']['unit'] = null;
        }
        unset($data['mainDetail']['configuratorOptions']);

        return $data;
    }

    /**
     * This function loads the category models for the passed ids in the "categories" parameter.
     *
     * @param array $data
     *
     * @return array
     */
    protected function prepareCategoryAssociatedData($data)
    {
        $categories = [];
        foreach ($data['categories'] as $categoryData) {
            if (!empty($categoryData['id'])) {
                $model = $this->get('models')->find(Category::class, $categoryData['id']);
                $categories[] = $model;
            }
        }
        $data['categories'] = $categories;

        return $data;
    }

    /**
     * Resolves the passed seo category data.
     * The functions resolves the passed foreign keys for the
     * assigned category and shop model.
     *
     * @param array   $data
     * @param Product $article
     *
     * @return array
     */
    protected function prepareSeoCategoryAssociatedData($data, $article)
    {
        if (!isset($data['seoCategories'])) {
            return $data;
        }

        $categories = [];
        foreach ($data['seoCategories'] as &$categoryData) {
            $categoryData['article'] = $article;

            if (empty($categoryData['shopId'])) {
                continue;
            }

            if (empty($categoryData['categoryId'])) {
                continue;
            }

            $categoryData['shop'] = $this->getManager()->find(
                Shop::class,
                $categoryData['shopId']
            );

            $categoryData['category'] = $this->getManager()->find(
                Category::class,
                $categoryData['categoryId']
            );

            if (!($categoryData['shop'] instanceof Shop)) {
                continue;
            }

            if (!($categoryData['category'] instanceof Category)) {
                continue;
            }

            $categories[] = $categoryData;
        }

        $data['seoCategories'] = $categories;

        return $data;
    }

    /**
     * This function loads the related product models for the passed ids in the "related" parameter.
     *
     * @param array   $data
     * @param Product $article
     *
     * @return array
     */
    protected function prepareRelatedAssociatedData($data, $article)
    {
        $related = [];
        foreach ($data['related'] as $relatedData) {
            if (empty($relatedData['id'])) {
                continue;
            }
            $relatedProduct = $this->getRepository()->find($relatedData['id']);
            if (!$relatedProduct instanceof Product) {
                throw new ModelNotFoundException(Product::class, $relatedData['id']);
            }

            // if the user select the cross
            if ($relatedData['cross'] && !$relatedProduct->getRelated()->contains($article)) {
                $relatedProduct->getRelated()->add($article);
                $this->get('models')->persist($relatedProduct);
            }
            $related[] = $relatedProduct;
        }
        $data['related'] = $related;

        return $data;
    }

    /**
     * This function loads the related product stream models for the passed ids in the "streams" parameter.
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    protected function prepareRelatedProductStreamsData($data)
    {
        $relatedStreams = [];
        foreach ($data['streams'] as $relatedProductStreamData) {
            if (empty($relatedProductStreamData['id'])) {
                continue;
            }

            $relatedProductStream = $this->get(ModelManager::class)->getRepository(ProductStream::class)
                ->find($relatedProductStreamData['id']);

            $relatedStreams[] = $relatedProductStream;
        }
        $data['relatedProductStreams'] = $relatedStreams;

        return $data;
    }

    /**
     * This function loads the similar models for the passed ids in the "similar" parameter.
     *
     * @param array   $data
     * @param Product $article
     *
     * @return array
     */
    protected function prepareSimilarAssociatedData($data, $article)
    {
        $similar = [];
        foreach ($data['similar'] as $similarData) {
            if (empty($similarData['id'])) {
                continue;
            }
            $similarProduct = $this->getRepository()->find($similarData['id']);
            if (!$similarProduct instanceof Product) {
                throw new ModelNotFoundException(Product::class, $similarData['id']);
            }

            // if the user select the cross
            if ($similarData['cross'] && !$similarProduct->getSimilar()->contains($article)) {
                $similarProduct->getSimilar()->add($article);
                $this->get('models')->persist($similarProduct);
            }
            $similar[] = $similarProduct;
        }
        $data['similar'] = $similar;

        return $data;
    }

    /**
     * This function loads the category models for the passed ids in the "categories" parameter.
     *
     * @param array $data
     *
     * @return array
     */
    protected function prepareImageAssociatedData($data)
    {
        $position = 1;
        foreach ($data['images'] as &$imageData) {
            $imageData['position'] = $position;
            if (!empty($imageData['mediaId'])) {
                $media = $this->get('models')->find(Media::class, $imageData['mediaId']);
                if ($media instanceof Media) {
                    $imageData['media'] = $media;
                } else {
                    $imageData['media'] = null;
                }
            } else {
                $imageData['media'] = null;
            }
            unset($imageData['mappings']);
            unset($imageData['children']);
            unset($imageData['parent']);
            ++$position;
        }

        return $data;
    }

    /**
     * This function prepares the prices for the product main detail object.
     *
     * @param array   $data
     * @param Product $article
     */
    protected function prepareMainPricesAssociatedData($data, $article)
    {
        $data['mainDetail']['prices'] = $this->preparePricesAssociatedData($data['mainPrices'], $article, $data['tax']);

        return $data;
    }

    /**
     * @param array   $prices
     * @param Product $article
     * @param Tax     $tax
     *
     * @return array
     */
    protected function preparePricesAssociatedData($prices, $article, $tax)
    {
        foreach ($prices as $key => &$priceData) {
            // Load the customer group of the price definition
            $customerGroup = $this->getCustomerGroupRepository()->findOneBy(['key' => $priceData['customerGroupKey']]);

            // If no customer group found, remove price and continue
            if (!$customerGroup instanceof CustomerGroup) {
                unset($prices[$key]);
                continue;
            }

            $priceData['to'] = (int) $priceData['to'];

            // If the "to" value isn't numeric, set the place holder "beliebig"
            if ($priceData['to'] <= 0) {
                $priceData['to'] = 'beliebig';
            }

            if ($customerGroup->getTaxInput()) {
                $priceData['price'] = $priceData['price'] / (100 + (float) $tax->getTax()) * 100;
                $priceData['pseudoPrice'] = $priceData['pseudoPrice'] / (100 + (float) $tax->getTax()) * 100;
                $priceData['regulationPrice'] = $priceData['regulationPrice'] / (100 + (float) $tax->getTax()) * 100;
            }

            // Resolve the oneToMany association of ExtJs to an oneToOne association for doctrine.
            $priceData['customerGroup'] = $customerGroup;
            $priceData['article'] = $article;
            $priceData['articleDetail'] = $article->getMainDetail();
        }

        return $prices;
    }

    /**
     * Prepares the link data of the product.
     *
     * @param array $data
     *
     * @return array
     */
    protected function prepareLinkAssociatedData($data)
    {
        foreach ($data['links'] as &$linkData) {
            $linkData['link'] = trim($linkData['link']);
            // Map the boolean ExtJS link target to the string format which used in the database
            $linkData['target'] = ($linkData['target'] === true) ? '_blank' : '_parent';
        }

        return $data;
    }

    /**
     * Prepares the download data of the product.
     *
     * @param array $data
     *
     * @return array
     */
    protected function prepareDownloadAssociatedData($data)
    {
        $mediaService = Shopware()->Container()->get(MediaServiceInterface::class);
        foreach ($data['downloads'] as &$downloadData) {
            $downloadData['file'] = $mediaService->normalize($downloadData['file']);
        }

        return $data;
    }

    /**
     * Returns a list of all product detail templates as array.
     *
     * @return array
     */
    protected function getTemplates()
    {
        $config = Shopware()->Config()->get('detailTemplates');
        $data = [];
        foreach (explode(';', $config) as $path) {
            list($id, $name) = explode(':', $path);
            $data[] = ['id' => $id, 'name' => $name];
        }

        return $data;
    }

    /**
     * Internal helper function which returns default data for a new product.
     *
     * @return array
     */
    protected function getNewArticleData()
    {
        $prefix = Shopware()->Config()->get('backendAutoOrderNumberPrefix');

        $sql = "SELECT number FROM s_order_number WHERE name = 'articleordernumber'";
        $number = Shopware()->Db()->fetchOne($sql);

        if (!empty($number)) {
            do {
                ++$number;

                $sql = 'SELECT id FROM s_articles_details WHERE ordernumber LIKE ?';
                $hit = Shopware()->Db()->fetchOne($sql, $prefix . $number);
            } while ($hit);
        }

        return [
            'number' => $prefix . $number,
            'autoNumber' => $number,
        ];
    }

    /**
     * Internal helper function to remove all product prices quickly.
     *
     * @param int $articleId
     */
    protected function removePrices($articleId)
    {
        $query = $this->getRepository()->getRemovePricesQuery($articleId);
        $query->execute();
    }

    /**
     * Internal helper function to remove the product attributes quickly.
     *
     * @param int $articleDetailId
     */
    protected function removeAttributes($articleDetailId)
    {
        $query = $this->getRepository()->getRemoveAttributesQuery($articleDetailId);
        $query->execute();
    }

    /**
     * Internal helper function to remove the detail esd configuration quickly.
     *
     * @param int $articleId
     */
    protected function removeArticleEsd($articleId)
    {
        $query = $this->getRepository()->getRemoveESDQuery($articleId);
        $query->execute();
    }

    /**
     * @param Product $article
     */
    protected function removeArticleDetails($article)
    {
        $sql = 'SELECT id FROM s_articles_details WHERE articleID = ? AND kind != 1';
        $details = Shopware()->Db()->fetchAll($sql, [$article->getId()]);

        foreach ($details as $detail) {
            $this->removeAttributes($detail['id']);

            $query = $this->getRepository()->getRemoveImageQuery($detail['id']);
            $query->execute();

            $sql = 'DELETE FROM s_article_configurator_option_relations WHERE article_id = ?';
            Shopware()->Db()->query($sql, [$detail['id']]);

            $query = $this->getRepository()->getRemoveVariantTranslationsQuery($detail['id']);
            $query->execute();

            $query = $this->getRepository()->getRemoveDetailQuery($detail['id']);
            $query->execute();
        }
    }

    /**
     * @param Product $article
     */
    protected function removeArticleTranslations($article)
    {
        $query = $this->getRepository()->getRemoveArticleTranslationsQuery($article->getId());
        $query->execute();

        $sql = 'DELETE FROM s_articles_translations WHERE articleID = ?';
        $this->container->get(Connection::class)->executeQuery($sql, [$article->getId()]);
    }

    /**
     * Increase the number of the s_order_number
     *
     * @param string $autoNumber
     * @param string $number
     */
    protected function increaseAutoNumber($autoNumber, $number)
    {
        if (\strlen($number) > 2) {
            $number = substr($number, \strlen(Shopware()->Config()->get('backendAutoOrderNumberPrefix')));
        }
        if ($number == $autoNumber) {
            $sql = "UPDATE s_order_number SET number = ? WHERE name = 'articleordernumber'";
            Shopware()->Db()->query($sql, [$autoNumber]);
        }
    }

    /**
     * Helper function which creates a query builder object to select all product variants
     * with their configuration options. This builder is used for the order number
     * generation in the backend module.
     *
     * @param int      $articleId
     * @param int|null $offset
     * @param int|null $limit
     *
     * @return QueryBuilder
     */
    protected function getVariantsWithOptionsBuilder($articleId, $offset = null, $limit = null)
    {
        $builder = $this->get('models')->createQueryBuilder();
        $builder->select(['details', 'options']);
        $builder->from(ProductVariant::class, 'details')
            ->leftJoin('details.configuratorOptions', 'options')
            ->where('details.articleId = :articleId')
            ->setParameter('articleId', $articleId);

        if ($offset !== null && $limit !== null) {
            $builder->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        return $builder;
    }

    /**
     * Start function for number generation. Iterates the different commands,
     * resolves the cursor or counter and starts the recursive
     *
     * @param int $counter
     *
     * @return string
     */
    protected function interpretNumberSyntax(Product $article, ProductVariant $detail, array $commands, $counter)
    {
        $name = [];
        foreach ($commands as $command) {
            // If the command isn't equals "n" we have to execute commands
            if ($command !== 'n') {
                // First we have to resolve the cursor object which used for the first command
                // The cursor object is set over the "prepareNumberSyntax" function.
                if ($command['cursor'] === 'detail') {
                    $cursor = $detail;
                } else {
                    $cursor = $article;
                }
                // Call the recursive interpreter to resolve all commands
                $value = $this->recursiveInterpreter($cursor, 0, $command['commands']);
                $name[] = str_replace(' ', '-', $value);
            } else {
                $name[] = $counter;
            }
        }
        // Return all command results, concat with a dot
        return implode('.', $name);
    }

    /**
     * This function executes the different commands for the number regeneration function.
     * First the function executes the current command on the passed cursor object.
     * If the result is traversable
     *
     * @param Product|ProductVariant|string $cursor
     * @param int                           $index
     * @param array                         $commands
     *
     * @return string
     */
    protected function recursiveInterpreter($cursor, $index, $commands)
    {
        if (!\is_object($cursor)) {
            return '';
        }

        if (!method_exists($cursor, $commands[$index]['command'])) {
            return $commands[$index]['origin'];
        }

        // First we execute the current command on the cursor object
        $result = $cursor->{$commands[$index]['command']}();

        // Now we increment the command index
        ++$index;

        // If the result of the current command on the cursor is an array
        if ($result instanceof Traversable) {
            // We have to execute the following command on each array element.
            $results = [];
            foreach ($result as $object) {
                $results[] = $this->recursiveInterpreter($object, $index, $commands);
            }

            return implode('.', $results);

            // If the result of the current command on the cursor is an object
        } elseif ($result instanceof Product || $result instanceof ProductVariant) {
            // We have to execute the next command on the result
            return $this->recursiveInterpreter($result, $index, $commands);

            // Otherwise we can return directly.
        }

        return $result;
    }

    /**
     * Prepares the passed number syntax. Executes a regular expression
     * to get all syntax commands and maps this commands to the rout
     * object.
     *
     * @param string $syntax
     *
     * @return array
     */
    protected function prepareNumberSyntax($syntax)
    {
        preg_match_all('#\{(.*?)\}#ms', $syntax, $result);
        $syntax = $result[1];

        $properties = [];
        foreach ($syntax as $path) {
            if ($path !== 'n') {
                $properties[] = $this->getCommandMapping($path);
            } else {
                $properties[] = $path;
            }
        }

        return $properties;
    }

    /**
     * Internal helper function which helps the get the cursor object for the passed syntax command.
     *
     * @param string $syntax
     *
     * @return array
     */
    protected function getCommandMapping($syntax)
    {
        // we have to explode the current command to resolve the multiple properties.
        $paths = explode('.', $syntax);

        // we have to map the different properties to define the start cursor object.
        switch ($paths[0]) {
            // options are only available for the different product variants
            case 'options':
                $cursor = 'detail';
                $paths[0] = 'configuratorOptions';
                break;
                // all other commands will rout to the product
            default:
                $cursor = 'article';
        }

        $commands = [];

        // now we convert the property names to the getter functions.
        foreach ($paths as $path) {
            $commands[] = ['origin' => $path, 'command' => 'get' . ucfirst($path)];
        }

        return [
            'cursor' => $cursor,
            'commands' => $commands,
        ];
    }

    /**
     * @deprecated in 5.6, will be removed in 5.7 without a replacement
     *
     * Internal helper function to get the field names of the passed violation array.
     *
     * @param ConstraintViolationList $violations
     *
     * @return array
     */
    protected function getViolationFields($violations)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be removed with 5.7. Will be removed without replacement.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $fields = [];
        foreach ($violations as $violation) {
            $fields[] = $violation->getPropertyPath();
        }

        return $fields;
    }

    /**
     * @param array        $detailData
     * @param Product|null $article
     */
    private function setDetailDataReferences($detailData, $article)
    {
        foreach ($detailData['prices'] as &$price) {
            $price['article'] = $article;
            unset($price['id']);
            $price['customerGroup'] = $this->get('models')->find(CustomerGroup::class, $price['customerGroup']['id']);
        }
        if ($detailData['unitId']) {
            $detailData['unit'] = $this->get('models')->find(Unit::class, $detailData['unitId']);
        }

        return $detailData;
    }

    /**
     * @param string $number
     *
     * @return bool
     */
    private function orderNumberExist($number)
    {
        $detail = $this->getArticleDetailRepository()->findOneBy([
            'number' => $number,
        ]);

        return !empty($detail);
    }

    /**
     * Helper method which selects esd attributes and maps them into the esd listing array
     *
     * @param array $esdAttributesList
     *
     * @return array
     */
    private function getEsdListingAttributes($esdAttributesList)
    {
        $products = $this->buildListProducts($esdAttributesList);
        $products = $this->getAdditionalTexts($products);

        return $this->assignAdditionalText($esdAttributesList, $products);
    }

    /**
     * @param ListProduct[] $products
     *
     * @return ListProduct[]
     */
    private function getAdditionalTexts(array $products): array
    {
        $shopRepo = $this->get(ModelManager::class)->getRepository(Shop::class);

        $shop = $shopRepo->getActiveDefault();

        $contextService = $this->get(ContextServiceInterface::class);

        $context = $contextService->createShopContext(
            $shop->getId(),
            $shop->getCurrency()->getId(),
            ContextService::FALLBACK_CUSTOMER_GROUP
        );

        $service = $this->get(AdditionalTextServiceInterface::class);

        return $service->buildAdditionalTextLists($products, $context);
    }

    /**
     * @return ListProduct[]
     */
    private function buildListProducts(array $result)
    {
        $products = [];
        foreach ($result as $item) {
            $number = $item['number'];

            $product = new ListProduct(
                $item['articleId'],
                $item['articleDetailId'],
                $item['number']
            );
            if ($item['additionalText']) {
                $product->setAdditional($item['additionalText']);
            }
            $products[$number] = $product;
        }

        return $products;
    }

    /**
     * @param ListProduct[] $products
     *
     * @return array
     */
    private function assignAdditionalText(array $data, array $products)
    {
        foreach ($data as &$item) {
            $number = $item['number'];
            if (!isset($products[$number])) {
                continue;
            }
            $product = $products[$number];
            $item['additionalText'] = $product->getAdditional();
        }

        return $data;
    }
}
