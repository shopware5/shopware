<?php declare(strict_types=1);

namespace Shopware\Api\Product\Definition;

use Shopware\Api\Category\Definition\CategoryDefinition;
use Shopware\Api\Entity\EntityDefinition;
use Shopware\Api\Entity\Field\DateField;
use Shopware\Api\Entity\Field\FkField;
use Shopware\Api\Entity\Field\ManyToOneAssociationField;
use Shopware\Api\Entity\FieldCollection;
use Shopware\Api\Entity\Write\Flag\PrimaryKey;
use Shopware\Api\Entity\Write\Flag\Required;
use Shopware\Api\Product\Event\ProductCategory\ProductCategoryWrittenEvent;

class ProductCategoryDefinition extends EntityDefinition
{
    /**
     * @var FieldCollection
     */
    protected static $fields;

    /**
     * @var FieldCollection
     */
    protected static $primaryKeys;

    public static function getEntityName(): string
    {
        return 'product_category';
    }

    public static function getFields(): FieldCollection
    {
        if (self::$fields) {
            return self::$fields;
        }

        return self::$fields = new FieldCollection([
            (new FkField('product_id', 'productId', ProductDefinition::class))->setFlags(new PrimaryKey(), new Required()),
            (new FkField('category_id', 'categoryId', CategoryDefinition::class))->setFlags(new PrimaryKey(), new Required()),
            new DateField('created_at', 'createdAt'),
            new DateField('updated_at', 'updatedAt'),
            new ManyToOneAssociationField('product', 'product_id', ProductDefinition::class, false),
            new ManyToOneAssociationField('category', 'category_id', CategoryDefinition::class, false),
        ]);
    }

    public static function getRepositoryClass(): string
    {
        throw new \RuntimeException('Mapping table do not have own repositories');
    }

    public static function getBasicCollectionClass(): string
    {
        throw new \RuntimeException('Mapping table do not have own collection classes');
    }

    public static function getBasicStructClass(): string
    {
        throw new \RuntimeException('Mapping table do not have own struct classes');
    }

    public static function getWrittenEventClass(): string
    {
        return ProductCategoryWrittenEvent::class;
    }

    public static function getTranslationDefinitionClass(): ?string
    {
        return null;
    }
}