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

require_once 'Smarty/Smarty.class.php';

/**
 * Enlight dummy snippet resource
 *
 * @deprecated Will be removed in 5.8
 */
class Enlight_Components_Snippet_DummyResource extends Smarty_Internal_Resource_Extends
{
    public function populate(Smarty_Template_Source $source, Smarty_Internal_Template $_template = null)
    {
        trigger_error('Snippet resource is deprecated and will be removed in 5.8. Please remove the usage.', E_USER_DEPRECATED);
        parent::populate($source, $_template);
    }
}
