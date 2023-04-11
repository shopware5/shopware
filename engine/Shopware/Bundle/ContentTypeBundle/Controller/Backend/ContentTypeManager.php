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

namespace Shopware\Bundle\ContentTypeBundle\Controller\Backend;

use Shopware\Bundle\ContentTypeBundle\Field\FieldInterface;
use Shopware\Bundle\ContentTypeBundle\Field\ResolveableFieldInterface;
use Shopware\Bundle\ContentTypeBundle\Field\TypeField;
use Shopware\Bundle\ContentTypeBundle\Field\TypeGrid;
use Shopware\Bundle\ContentTypeBundle\Services\ContentTypeCleanupServiceInterface;
use Shopware\Bundle\ContentTypeBundle\Services\SynchronizerServiceInterface;
use Shopware\Bundle\ContentTypeBundle\Services\TypeBuilder;
use Shopware\Bundle\ContentTypeBundle\Services\TypeProvider;
use Shopware\Bundle\ContentTypeBundle\Structs\Type;
use Shopware\Components\CacheManager;
use Shopware\Components\Slug\SlugInterface;
use Shopware\Models\Shop\Shop;
use Shopware_Components_Snippet_Manager as Snippets;
use Shopware_Controllers_Backend_ExtJs;
use Symfony\Component\HttpFoundation\Request;

class ContentTypeManager extends Shopware_Controllers_Backend_ExtJs
{
    private TypeProvider $typeProvider;

    /**
     * @var array<string, class-string<FieldInterface>>
     */
    private array $fieldAlias;

    private Snippets $snippetManager;

    private SlugInterface $slug;

    private TypeBuilder $typeBuilder;

    private CacheManager $cacheManager;

    private SynchronizerServiceInterface $synchronizerService;

    private ContentTypeCleanupServiceInterface $cleanupService;

    /**
     * @param array<string, class-string<FieldInterface>> $fieldAlias
     */
    public function __construct(
        array $fieldAlias,
        TypeProvider $typeProvider,
        Snippets $snippetManager,
        SlugInterface $slug,
        TypeBuilder $typeBuilder,
        CacheManager $cacheManager,
        SynchronizerServiceInterface $synchronizerService,
        ContentTypeCleanupServiceInterface $cleanupService
    ) {
        $this->fieldAlias = $fieldAlias;
        $this->typeProvider = $typeProvider;
        $this->snippetManager = $snippetManager;
        $this->slug = $slug;
        $this->typeBuilder = $typeBuilder;
        $this->cacheManager = $cacheManager;
        $this->synchronizerService = $synchronizerService;
        $this->cleanupService = $cleanupService;
    }

    public function listAction(): void
    {
        $types = array_map(static function (Type $item) {
            return $item->jsonSerialize() + ['id' => $item->getInternalName()];
        }, $this->typeProvider->getTypes());

        $this->View()->assign('success', true);
        $this->View()->assign('data', array_values($types));
        $this->View()->assign('count', \count($types));
    }

    public function fieldsAction(): void
    {
        $query = strtolower((string) $this->Request()->getParam('query'));
        $data = [];
        $namespace = $this->snippetManager->getNamespace('backend/content_type_manager/fields');

        foreach ($this->typeProvider->getTypes() as $type) {
            $this->fieldAlias[$type->getInternalName() . '-field'] = TypeField::class;
            $this->fieldAlias[$type->getInternalName() . '-grid'] = TypeGrid::class;
        }

        foreach ($this->fieldAlias as $id => $name) {
            $classImplements = class_implements($name);
            $hasResolver = \is_array($classImplements) && \array_key_exists(ResolveableFieldInterface::class, $classImplements);
            $label = $namespace->get($id, $id, true);

            if ($name === TypeField::class || $name === TypeGrid::class) {
                $snippetName = 'type-' . explode('-', $id)[1];
                $label = sprintf($namespace->get($snippetName, $snippetName, true), ucfirst(explode('-', $id)[0]));
            }

            if (($query !== '') && !str_contains(strtolower($name), $query) && !str_contains(strtolower($label), $query)) {
                continue;
            }

            $data[] = [
                'id' => $id,
                'name' => $name,
                'label' => $label,
                'hasResolver' => $hasResolver,
            ];
        }

        $this->View()->assign('data', $data);
    }

    public function createAction(Request $request): void
    {
        $type = $this->convertExtJsToStruct($request->request->all());

        $this->getModelManager()->getConnection()->insert('s_content_types', [
            'internalName' => $type->getInternalName(),
            'name' => $type->getName(),
            'source' => $type->getSource(),
            'config' => json_encode($type, JSON_THROW_ON_ERROR),
        ]);

        $this->typeProvider->addType($type->getInternalName(), $type);
        $this->clearCacheAndSync();

        $this->View()->assign([
            'success' => true,
            'data' => $this->getDetail($type->getInternalName()),
        ]);
    }

    public function updateAction(Request $request): void
    {
        $type = $this->convertExtJsToStruct($request->request->all());

        $this->getModelManager()->getConnection()->update('s_content_types', [
            'name' => $type->getName(),
            'source' => $type->getSource(),
            'config' => json_encode($type, JSON_THROW_ON_ERROR),
        ], [
            'internalName' => $type->getInternalName(),
        ]);

        $this->typeProvider->addType($type->getInternalName(), $type);
        $this->clearCacheAndSync();
        $this->createUrls($type);

        $this->View()->assign([
            'success' => true,
            'data' => $this->getDetail($type->getInternalName()),
        ]);
    }

    public function deleteAction(string $id): void
    {
        $this->typeProvider->removeType($id);

        $this->getModelManager()->getConnection()->delete('s_content_types', [
            'internalName' => $id,
        ]);

        $this->cleanupService->deleteContentType($id);

        $this->clearCacheAndSync();

        $this->View()->assign('success', true);
    }

    public function detailAction(string $id): void
    {
        $this->View()->assign('success', true);
        $this->View()->assign('data', $this->getDetail($id));
    }

    protected function initAcl(): void
    {
        $this->addAclPermission('index', 'read', 'Insufficient permissions');
        $this->addAclPermission('load', 'read', 'Insufficient permissions');
        $this->addAclPermission('list', 'read', 'Insufficient permissions');
        $this->addAclPermission('detail', 'read', 'Insufficient permissions');
        $this->addAclPermission('create', 'edit', 'Insufficient permissions');
        $this->addAclPermission('update', 'edit', 'Insufficient permissions');
        $this->addAclPermission('delete', 'delete', 'Insufficient permissions');
    }

    /**
     * @return array<string, mixed>
     */
    private function getDetail(string $name): array
    {
        $typeObj = $this->typeProvider->getType($name);

        $type = json_decode(json_encode($typeObj, JSON_THROW_ON_ERROR), true);
        $type['id'] = $type['internalName'];
        $type['controllerName'] = $typeObj->getControllerName();
        $type['urls'] = $this->getUrls($typeObj);

        return $type;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function convertExtJsToStruct(array $data): Type
    {
        unset($data['id'], $data['source'], $data['urls'], $data['controllerName']);

        if (empty($data['internalName'])) {
            $data['internalName'] = strtolower($this->slug->slugify($data['name'], '_'));
        }

        $data['id'] = $data['internalName'];

        $data['fieldSets'] = [
            [
                'fields' => $data['fields'],
            ],
        ];

        return $this->typeBuilder->createType($data['internalName'], $data);
    }

    private function clearCacheAndSync(): void
    {
        $this->synchronizerService->sync(true);
        $this->cacheManager->clearConfigCache();
        $this->cacheManager->clearProxyCache();
    }

    private function createUrls(Type $type): void
    {
        $shops = $this->getModelManager()->getRepository(Shop::class)->getActiveShopsFixed();
        $seoIndexer = $this->get('seoindex');

        $rewriteTable = $this->get('modules')->RewriteTable();

        foreach ($shops as $shop) {
            $seoIndexer->registerShop($shop->getId());

            $rewriteTable->baseSetup();
            $rewriteTable->createSingleContentTypeUrl($type);
        }
    }

    /**
     * @return list<array{name: string, url: string}>
     */
    private function getUrls(Type $type): array
    {
        $shops = $this->getModelManager()->getRepository(Shop::class)->getActiveShopsFixed();

        $urls = [];

        foreach ($shops as $shop) {
            $shop->registerResources();

            $urls[] = [
                'name' => $shop->getName(),
                'url' => $this->Front()->ensureRouter()->assemble([
                    'controller' => $type->getControllerName(),
                    'module' => 'frontend',
                    'action' => 'index',
                    'fullPath' => true,
                ]),
            ];
        }

        return $urls;
    }
}
