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

namespace Shopware\Bundle\ContentTypeBundle\Field\Shopware;

use Doctrine\DBAL\Types\Type;
use Shopware\Bundle\ContentTypeBundle\Field\FieldInterface;
use Shopware\Bundle\ContentTypeBundle\Field\ResolveableFieldInterface;
use Shopware\Bundle\ContentTypeBundle\Structs\Field;

abstract class AbstractSingleSelectionField implements FieldInterface, ResolveableFieldInterface
{
    /**
     * @var string
     */
    protected static $model;

    /**
     * @var string
     */
    protected static $valueField = 'id';

    /**
     * @var string
     */
    protected static $displayField = 'name';

    public static function getDbalType(): string
    {
        return Type::STRING;
    }

    public static function getExtjsField(): string
    {
        return 'content-types-single-selection';
    }

    public static function getExtjsType(): string
    {
        return 'string';
    }

    public static function getExtjsOptions(Field $field): array
    {
        return [
            'model' => static::$model,
            'valueField' => static::$valueField,
            'displayField' => static::$displayField,
        ];
    }

    public static function isMultiple(): bool
    {
        return false;
    }
}
