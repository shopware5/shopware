<?php

namespace Shopware\Storefront\Twig;

use Doctrine\DBAL\Connection;
use Shopware\Framework\Config\ConfigServiceInterface;
use Shopware\Storefront\Theme\ThemeConfigReader;
use Shopware\Storefront\Component\SitePageMenu;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface;

class TemplateDataExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var ConfigServiceInterface
     */
    private $configService;

    /**
     * @var SitePageMenu
     */
    private $sitePageMenu;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ThemeConfigReader
     */
    private $themeConfigReader;

    public function __construct(
        TranslatorInterface $translator,
        RequestStack $requestStack,
        ConfigServiceInterface $configService,
        SitePageMenu $sitePageMenu,
        Connection $connection,
        ThemeConfigReader $themeConfigReader
    ) {
        $this->translator = $translator;
        $this->requestStack = $requestStack;
        $this->configService = $configService;
        $this->sitePageMenu = $sitePageMenu;
        $this->connection = $connection;
        $this->themeConfigReader = $themeConfigReader;
    }

    public function getFunctions(): array
    {
        return [
            new \Twig_Function('snippet', function ($snippet, $namespace = null) { return $this->translator->trans($snippet, [], $namespace); })
        ];
    }

    public function getGlobals(): array
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            return [];
        }

        $shop = $request->attributes->get('_shop');

        if (empty($shop)) {
            return [];
        }

        $themeConfig = $this->themeConfigReader->get();

        $themeConfig = array_merge(
            $themeConfig,
            [
                'desktopLogo' => 'bundles/storefront/src/img/logos/logo--tablet.png',
                'tabletLandscapeLogo' => 'bundles/storefront/src/img/logos/logo--tablet.png',
                'tabletLogo' => 'bundles/storefront/src/img/logos/logo--tablet.png',
                'mobileLogo' => 'bundles/storefront/src/img/logos/logo--mobile.png',
                'favicon' => 'bundles/storefront/src/img/favicon.ico',
            ]
        );

        return [
            'shopware' => [
                'config' => $this->configService->getByShop($shop),
                'theme' => $themeConfig,
                'menu' => $this->sitePageMenu->getTree($shop['id']),
                'mainCategories' => $this->getCategories(3)
            ]
        ];
    }

    public function getCategories($id)
    {
        $pathIds = $this->getCategoryPath($id);
        $grouped = $this->getCategoryIdsWithParent($pathIds);

        $ids = array_merge($pathIds, array_keys($grouped));

        $cats = [];
        foreach ($grouped as $name => $cat) {
            $cats[] = [
                'id' => 1,
                'description' => $name
            ];
        }

        array_shift($cats);

        return $cats;

        $context = $this->contextService->getShopContext();
        $categories = $this->categoryService->getList($ids, $context);

        unset($grouped[$this->baseId]);

        $tree = $this->buildTree($grouped, $this->baseId);

        $result = $this->assignCategoriesToTree(
            $categories,
            $tree,
            $pathIds,
            $this->getChildrenCountOfCategories($ids)
        );

        return $result;
    }

    private function getCategoryPath($id)
    {
        $query = $this->connection->createQueryBuilder();
        $path = $query->select(['category.path'])
            ->from('category', 'category')
            ->where('category.id = :id')
            ->setParameter(':id', $id)
            ->execute()
            ->fetchColumn();

        $ids = [$id];

        if (!$path) {
            return $ids;
        }

        $pathIds = explode('|', $path);

        return array_filter(array_merge($ids, $pathIds));
    }

    private function getCategoryIdsWithParent($ids)
    {
        $query = $this->connection->createQueryBuilder();

        return $query->select(['category.description', 'category.parent'])
            ->from('category', 'category')
            ->where('(category.parent IN( :parentId ) OR category.id IN ( :parentId ))')
            ->andWhere('category.active = 1')
            ->orderBy('category.position', 'ASC')
            ->addOrderBy('category.id')
            ->setParameter(':parentId', $ids, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_KEY_PAIR);
    }
}