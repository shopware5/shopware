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

namespace Shopware\Components\Theme;

/**
 * The config set class is used to add theme configuration sets within the
 * Theme.php of a single theme.
 * Each theme can contains multiple configuration sets.
 * A ConfigSet requires a set name and a configured
 * values array.
 *
 * example:
 * <code>
 *
 *   public function createConfigSets(ArrayCollection $collection)
 *   {
 *      $set = new ConfigSet();
 *      $set->setName('Set name');
 *      $set->setDescription('Set description');
 *      $set->setValues(array(
 *          'field1' => 'field1_value',
 *          'field2' => 'field2_value'
 *      ));
 *
 *      $collection->add($set);
 *   }
 *
 * </code>
 */
class ConfigSet
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var array
     */
    protected $values;

    /**
     * @param string $description
     * @param string $name
     */
    public function __construct($name = '', array $values = [], $description = '')
    {
        $this->description = $description;
        $this->name = $name;
        $this->values = $values;
    }

    /**
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @param array $values
     *
     * @return $this
     */
    public function setValues($values)
    {
        $this->values = $values;

        return $this;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Validates the ConfigSet component.
     * If no name or values configured the component throws an exception.
     *
     * @throws \Exception
     */
    public function validate()
    {
        if (!$this->name) {
            throw new \Exception('Each config set requires a configured name!');
        }
        if (!$this->values || !is_array($this->values)) {
            throw new \Exception(sprintf(
                'Config set %s defined without values array.',
                $this->name
            ));
        }
    }
}
