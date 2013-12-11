<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

/**
 * Model to handle widgets
 */
class Shopware_Models_Widgets_Widgets extends Enlight_Class implements Enlight_Hook
{
    /**
     * Path to panel settings xml
     * @var string
     */
    protected $widgetXML;

    protected $widget;

    protected $panel;

    protected $panelXML;

    /**
     * Constructor - dependly inject panel path
     * @param  $panel
     */
    public function __construct($panel,$widget)
    {
        $this->setWidget($widget);
        $this->setPanel($panel);
    }

    /**
     * Set path to widget configuration
     * @param  $panel
     * @return void
     */
    public function setWidget ($widget)
    {
        $this->widget = $widget;
        $this->widgetXML = Shopware()->DocPath()."/files/config/Widgets.xml";
    }

    /**
     * Set path to panel configuration
     * @param  $panel
     * @return void
     */
    public function setPanel ($panel)
    {
        $this->panel = $panel;
        $this->panelXML = Shopware()->DocPath()."/files/config/Panels.xml";
    }

    /**
     * Get all widgets from configuration
     * @throws Enlight_Exception
     * @return
     */
    public function getAll()
    {
        if (empty($this->widgetXML)) {
            throw new Enlight_Exception("\$this->panel can not be null");
        }
        $XML = new Shopware_Components_Xml_SimpleXml();
        $XML->loadFile($this->widgetXML);
        $Widgets = $XML->SimpleXML->getElementsByTagName('Widget');
        foreach ($Widgets as $Widget) {
            $result[] = $XML->nodeToArray($Widget);
        }

        $this->multiArraySort($result,"name");
        return $result;
    }

    /**
     * Get a certain widget from configuration
     * @throws Enlight_Exception
     * @param  $name
     * @return string
     */
    public function get($name)
    {
        if (empty($this->widgetXML)) {
            throw new Enlight_Exception("\$this->panel can not be null");
        }
        $XML = new Shopware_Components_Xml_SimpleXml();
        $XML->loadFile($this->widgetXML);
        $widget = $XML->SimpleXML->firstOf('//Widget[@name="'.$name.'"]');

        $widget = $XML->nodeToArray($widget);
        return $widget;
    }

    /**
     * Update widget permissions
     * @param  $name
     * @param  $rights
     * @return bool
     */
    public function updatePermissions($name,$rights)
    {
        $XML = new Shopware_Components_Xml_SimpleXml();
        $XML->loadFile($this->widgetXML);
        $XML->SimpleXML->removeNodes('//Widget[@name="'.$name.'"]//permissions');
        $search = '//Widget[@name="'.$name.'"]';
        $temp = $XML->SimpleXML->firstOf($search);

        $permissions = $temp->addChild("permissions");
        $permissions->addChild("aclGroup",$rights["aclGroup"]);
        $users = $permissions->addChild("Users");
        foreach ($rights["users"] as $user) {
            $users->addChild("user")->addAttribute("id",$user);
        }
        $XML->SimpleXML->replaceNodes('//Widget[@name="'.$name.'"]',$temp);

        $XML->setFilename($this->widgetXML);
        $XML->save();

        return true;
    }

    /**
     * Helper function to easily sort multidimensional arrays
     * @param  $data
     * @param  $sortby
     * @return void
     */
    protected function multiArraySort(&$data, $sortby)
    {
          static $sort_funcs = array();

          if (empty($sort_funcs[$sortby])) {
              $code = "\$c=0;";
              foreach (explode(',', $sortby) as $key) {
                $array = array_pop($data);
                array_push($data, $array);
                if(is_numeric($array[$key]))
                  $code .= "if ( \$c = ((\$a['$key'] == \$b['$key']) ? 0:((\$a['$key'] < \$b['$key']) ? -1 : 1 )) );";
                else
                  $code .= "if ( (\$c = strcasecmp(\$a['$key'],\$b['$key'])) != 0 ) return \$c;\n";
              }
              $code .= 'return $c;';
              $sort_func = $sort_funcs[$sortby] = create_function('$a, $b', $code);
          } else {
              $sort_func = $sort_funcs[$sortby];
          }

         $sort_func = $sort_funcs[$sortby];
         uasort($data, $sort_func);
     }

}
