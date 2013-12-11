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
 */
class Shopware_Models_Widgets_Panel extends Enlight_Class implements Enlight_Hook
{
    /**
     * Path to panel settings xml
     * @var string
     */
    protected $panelXML;

    protected $panel;

    /**
     * Constructor - dependly inject panel path
     * @param  $panel
     */
    public function __construct($panel)
    {
        $this->setPanel($panel);
    }

    /**
     * Setting panel path
     * @param  $panel
     * @return void
     */
    public function setPanel ($panel)
    {
        $this->panel = $panel;
        $this->panelXML = Shopware()->DocPath()."/files/config/Panels.xml";
    }
    /**
     * Helper Function to report errors to extjs
     * @param  $message
     * @return void
     */
    public function breakError($message)
    {
        echo json_encode(array("success"=>false,"message"=>$message));exit;
    }

    /**
     * Load panel configuration
     * @throws Enlight_Exception
     * @return string
     */
    public function loadSettings()
    {
        if (empty($this->panel)) {
            throw new Enlight_Exception("\$this->panel can not be null");
        }
        $XML = new Shopware_Components_Xml_SimpleXml();
        $XML->loadFile($this->panelXML);
        $xpath = '//Panel[@name="'.$this->panel.'"]';
        $PanelNode = $XML->SimpleXML->firstOf($xpath);
        return $XML->nodeToArray($PanelNode);
    }

    /**
     * Update panel meta settings
     * @param  $panel
     * @param  $fields
     * @return void
     */
    public function update($fields)
    {
        $XML = new Shopware_Components_Xml_SimpleXml();
        $XML->loadFile($this->panelXML);
        if (($XML->SimpleXML->firstOf('//Panel[@name="'.$fields["name"].'"]') !== null || empty($fields["name"])) && $fields["name"]!=$this->panel) {
            return array("success"=>false,"error"=>"Key already exists");
        }
        $xpath = '//Panel[@name="'.$this->panel.'"]';
        $PanelNode = $XML->SimpleXML->firstOf($xpath);
        $PanelNode->setAttribute('name',$fields["name"]);
        unset ($fields["key"]);
        foreach ($fields as $key => $value) {
            $PanelNode->$key = $value;
        }
        $XML->SimpleXML->replaceNodes($xpath,$PanelNode);
        $XML->setFilename($this->panelXML);
        $XML->save();

        return array("success"=>true,"key"=>$fields["name"]);
    }

    /**
     * Get all widgets from a certain panel
     * @throws Enlight_Exception
     * @return array
     */
    public function getAllWidgets()
    {
        if (empty($this->panelXML)) {
            throw new Enlight_Exception("\$this->panel can not be null");
        }
        $XML = new Shopware_Components_Xml_SimpleXml();

        if (!is_file($this->panelXML)) return array();

        $XML->loadFile($this->panelXML);
        $xpath = '//Panel[@name="'.$this->panel.'"]';
        $WidgetMainNode = $XML->SimpleXML->firstOf($xpath);
        $Widgets = $WidgetMainNode->getElementsByTagName('widget');
        $result = array();
        foreach ($Widgets as $Widget) {
            $Widget = $XML->nodeToArray($Widget);
            if (!isset($Widget["position"])) $Widget["position"] = "0";
            $result[] = $Widget;
        }

        $this->multiArraySort($result,"position");
        $result = array_values($result);
        return $result;
    }

    /**
     * Get all columns from a certain panel
     * @throws Enlight_Exception
     * @return array
     */
    public function getAllColumns()
    {
        if (empty($this->panelXML)) {
            throw new Enlight_Exception("\$this->panel can not be null");
        }
        $XML = new Shopware_Components_Xml_SimpleXml();
        $XML->loadFile($this->panelXML);
        $xpath = '//Panel[@name="'.$this->panel.'"]';
        $WidgetMainNode = $XML->SimpleXML->firstOf($xpath);
        $Widgets = $WidgetMainNode->getElementsByTagName('column');
        $result = array();
        foreach ($Widgets as $Widget) {
            $Widget = $XML->nodeToArray($Widget);
            if (!isset($Widget["position"])) $Widget["position"] = "0";
            $result[] = $Widget;
        }

        $this->multiArraySort($result,"position");
        $result = array_values($result);
        return $result;
    }

    /**
     * Update widget - set one property defined in $field to $value
     * @param  $id
     * @param  $field
     * @param  $value
     * @param  $previous
     * @return string
     */
    public function updateWidget($id,$field,$value,$previous)
    {
        $XML = new Shopware_Components_Xml_SimpleXml();
        $XML->loadFile($this->panelXML);

        $xpath = '//Panel[@name="'.$this->panel.'"]//widgets';
        $WidgetMainNode = $XML->SimpleXML->firstOf($xpath);

        if (empty($id)) {
            $id = md5(uniqid(rand()));
            // Create new widget
            $temp["widget"]["@attributes"] = array("uid"=>$id);
            $temp["widget"]["configuration"] = array();
            $temp["widget"][$field] = $value;
            $XML->set($WidgetMainNode,$temp);
        } else {
            $temp = $WidgetMainNode->firstOf('//widget[@uid="'.$id.'"]');
            $temp->$field = $value;
            $XML->SimpleXML->replaceNodes('//widget[@uid="'.$id.'"]',$temp);
        }

        $XML->setFilename($this->panelXML);
        $XML->save();

        return $id;
    }

    /**
     * Delete a widget from defined panel
     * @param  $id
     * @return void
     */
    public function deleteWidget($id)
    {
        $XML = new Shopware_Components_Xml_SimpleXml();
        $XML->loadFile($this->panelXML);
        $XML->SimpleXML->deleteNodes('//widget[@uid="'.$id.'"]');
        $XML->setFilename($this->panelXML);
        $XML->save();
    }

    /**
     * Update column configuration
     * @param  $id
     * @param  $field
     * @param  $value
     * @param  $previous
     * @return string
     */
    public function updateColumn($id,$field,$value,$previous)
    {
        $XML = new Shopware_Components_Xml_SimpleXml();
        $XML->loadFile($this->panelXML);

        $xpath = '//Panel[@name="'.$this->panel.'"]//columns';
        $WidgetMainNode = $XML->SimpleXML->firstOf($xpath);

        if (empty($id)) {
            $id = md5(uniqid(rand()));
            // Create new widget
            $temp["column"]["@attributes"] = array("uid"=>$id);
            $temp["column"]["configuration"] = array();
            $temp["column"][$field] = $value;
            $XML->set($WidgetMainNode,$temp);
        } else {
            $temp = $WidgetMainNode->firstOf('//column[@uid="'.$id.'"]');
            $temp->$field = $value;
            $XML->SimpleXML->replaceNodes('//column[@uid="'.$id.'"]',$temp);
        }

        $XML->setFilename($this->panelXML);
        $XML->save();

        return $id;
    }

    /**
     * Update widget configuration
     * @param  $id
     * @param  $fields
     * @return void
     */
    public function updateWidgetConfiguration($id,$fields)
    {
        $XML = new Shopware_Components_Xml_SimpleXml();
        $XML->loadFile($this->panelXML);

        $xpath = '//Panel[@name="'.$this->panel.'"]//widgets';
        $WidgetMainNode = $XML->SimpleXML->firstOf($xpath);
        $temp = $WidgetMainNode->firstOf('//widget[@uid="'.$id.'"]//configuration');
        foreach ($fields as $field => $value) {
            $temp->$field = $value;
        }
        $XML->SimpleXML->replaceNodes('//widget[@uid="'.$id.'"]//configuration',$temp);
        $XML->setFilename($this->panelXML);
        $XML->save();
    }

    /**
     * Load widget configuration
     * @param  $id
     * @return array|string
     */
    public function getWidgetConfiguration($id)
    {
        $XML = new Shopware_Components_Xml_SimpleXml();
        $XML->loadFile($this->panelXML);
        $xpath = '//Panel[@name="'.$this->panel.'"]//widgets';
        $WidgetMainNode = $XML->SimpleXML->firstOf($xpath);
        $temp = $WidgetMainNode->firstOf('//widget[@uid="'.$id.'"]//configuration');
        if (empty($temp)) {
            return array();
        }
        $temp = $XML->nodeToArray($temp);
        return $temp;
    }

    /**
     * Load widget template - deprecated do not work anymore
     * @param  $callback
     * @param  $config
     * @param  $panel
     * @param  $panelTemplates
     * @return string
     */
    public function loadWidget($callback,$config,$panel,$panelTemplates)
    {
        $template = clone Shopware()->Template();
        $view = $template->createData();

        $view->assign('callback',$callback);
        unset($config["items"]);
        $view->assign('config',$config);
        $view->assign('panel',$panel);
        $view->assign('configJson',Zend_Json::encode($config));
        $template->addTemplateDir(
            Shopware()->DocPath().$config["object"]["views"],
            $panelTemplates,
            Shopware()->DocPath().'templates/_local/',
            Shopware()->DocPath().'templates/_default/'
        );

        $template->setCompileId(md5(get_class($this)));
        $parsedTemplate = $template->fetch($config["object"]["template"],$view);
        return $parsedTemplate;
    }

    /**
     * Create panel directory structure and settings xml file
     * @param  $panel
     * @return bool
     */
    public function create($panel)
    {
        if (empty($panel)) {
            throw new Exception("Empty panel name given");
        }
        $a = new Shopware_Components_Xml_SimpleXml();
        if (!is_file($this->panelXML)) {
            $a->setNamespace('Panels')->create();
        } else {
            $a->loadFile($this->panelXML);
            if ($a->attributeExists('Panels','name',$panel)==true) {
                throw new Exception("Panel with name $panel already exists");
            }
        }

        $temp["Panel"]["@attributes"] = array("name"=>$panel);
        $temp["Panel"]["label"] = "New Panel";
        $temp["Panel"]["authProvider"] = array("@attributes"=>array("name"=>"test"));
        $temp["Panel"]["widgets"] = array();
        $temp["Panel"]["columns"] = array();
        $a->set($a->getXmlAtNode('Panels'),$temp);
        $a->setFilename($this->panelXML);
        $a->save();
       return true;
    }

    /**
     * Check path and permissions for a certain panel
     * @param  $panel
     * @return void
     */
    public function validatepanel()
    {
        if (empty($this->panel)) {
            $this->breakError("panel is empty");
        }
        if (!is_dir(Shopware()->DocPath()."/files/config/")) {
            $this->breakError("Directory ".Shopware()->DocPath()."/files/config does not exists");
        } elseif (!is_writable(Shopware()->DocPath()."/files/config/")) {
            $this->breakError("Path ".Shopware()->DocPath()."/files/config/ is not writeable");
        }
        return true;
    }
    /**
     * Get all panels / panel nodes
     * @param  $node
     * @return
     */
    public function getAll($node)
    {
        $config = Shopware()->getOption('host');
        if (is_file(Shopware()->DocPath()."/files/config/Panels.xml")) {
            $xml = new Shopware_Components_Xml_SimpleXml();
            $xml->loadFile(Shopware()->DocPath()."/files/config/Panels.xml");
            foreach ($xml->getXmlAtNode("Panels") as $panel) {
                $attributes =  $panel->attributes();
                $name = (array) $attributes["name"];
                $name = $name[0];
                $icon = !empty($_SERVER["HTTPS"]) ? "https://" : "http://".$config["server"]."/engine/Shopware/Plugins/Community/Backend/SwagKick/Views/backend/_resources/images/plugin.png";
                $nodes[] = array('text'=>$name, 'id'=>$name, 'parentId'=>0, 'icon'=>$icon,"leaf"=>true);
            }
        } else {
            $nodes[] = array('text'=>"Add a new panel first", 'id'=>0, 'parentId'=>0, 'icon'=>"","leaf"=>true);
        }
        return $nodes;
    }

    /**
     * Get all meta data from a certain panel
     * @return array
     */
    public function get($panel)
    {
       if (!is_file(Shopware()->DocPath()."/files/config/Panels.xml")) {
            return false;
       }

       $xml = new Shopware_Components_Xml_SimpleXml();
       $xml->loadFile(Shopware()->DocPath()."/files/config/Panels.xml");
       $xpath = '//Panel[@name="'.$panel.'"]';

       $WidgetMainNode = $xml->SimpleXML->firstOf($xpath);
       if (!$WidgetMainNode) {
           throw new Enlight_Exception("Panel $panel not found");
       }
       $result = $xml->nodeToArray($WidgetMainNode);
       return $result;
    }

    /**
     * Sort a multidimensional array
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
