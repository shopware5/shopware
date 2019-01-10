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

class Smarty_Resource_Parent extends Smarty_Resource
{
    /**
     * populate Source Object with meta data from Resource
     *
     * @param  Smarty_Template_Source $source source object
     * @param  Smarty_Internal_Template $_template template object
     *
     * @see \Smarty_Resource_Extendsall
     * @return void
     */
    public function populate(Smarty_Template_Source $source, Smarty_Internal_Template $_template = null)
    {
        $uid = $source->name;
        $sources = array();
        $exists = false;
        foreach ($source->smarty->getTemplateDir() as $key => $directory) {
            try {
                $s = Smarty_Template_Source::load(null, $source->smarty, 'file:[' . $key . ']' . $source->name);
                if (!$s->exists) {
                    continue;
                }
                if (!$exists) {
                    if ($s->filepath == $_template->parent->source->filepath) {
                        $exists = true;
                    }
                    continue;
                }
                $sources[] = $s;
                $uid .= $s->filepath;
                break;
            } catch (SmartyException $e) {
            }
        }

        if (!$exists || empty($sources)) {
            $source->exists = false;
            return;
        }
        $source->components = $sources;
        $source->filepath = $source->uid = sha1($uid);
        $source->exists = $exists;
        if ($_template && $_template->smarty->compile_check) {
            $source->timestamp = $s->getTimeStamp();
        }
    }

    public function getContent(Smarty_Template_Source $source)
    {
        return $source->components[0]->getContent();
    }
}
