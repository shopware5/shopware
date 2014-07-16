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
 * Shopware document / pdf controller
 */
class Shopware_Controllers_Backend_Document extends Enlight_Controller_Action
{
    /**
     * Document property translations
     *
     * @var array
     */
    protected $sTranslations;
    /**
     * Available languages / shops
     *
     * @var array
     */
    protected $sLanguages;
    /**
     * Path to load resources
     *
     * @var string
     */
    protected $path;

    /**
     * Setting correct url / path to access resources
     * @return void
     */
    public function init()
    {
        if ($_SERVER["HTTPS"]) {
            $this->path = "https://";
        } else {
            $this->path = "http://";
        }
        $this->path .= Shopware()->Config()->BasePath."/";

    }

    /**
     * Generate pdf invoice
     * @param $this->Request()->id - Order-ID
     * @param $this->Request()->ust_free - Generate taxfree invoice 1/0
     * @param $this->Request()->typ - Document type [0,1,2,3]
     * @param $this->Request()->voucher - Print voucher on invoice (voucher.id)
     * @param $this->Request()->date - Invoice date
     * @param $this->Request()->delivery_date - Shipping date
     * @param $this->Request()->bid - Previous invoice id to use
     * @param $this->Request()->preview - Preview of document 1/0
     * @param $this->Request()->pagebreak - Force pagebreak in preview
     * @param $this->Request()->sampledate - Use predefined sampledata for preview
     * @param $this->Request()->compatibilityMode - Use html2ps instead mpdf (Deprecated < 3.5 method / old templates)
     * @param $this->Request()->docComment - Temporary comment to print  in document
     * @access public
     */
    public function indexAction()
    {
        $id = $this->Request()->id;
        $netto = $this->Request()->ust_free;
        if ($netto=="false") $netto = false;
        $typ = $this->Request()->typ;
        $voucher = $this->Request()->voucher;
        $date = $this->Request()->date;
        $delivery_date = $this->Request()->delivery_date;
        $bid = $this->Request()->bid; // Beleg-ID
        $this->View()->setTemplate();
        $document = Shopware_Components_Document::initDocument($id,$typ,
            array(
                "netto"=>$netto,
                "bid"=>$bid,
                "voucher"=>$voucher,
                "date"=>$date,
                "delivery_date"=>$delivery_date,
                "shippingCostsAsPosition"=>true,
                "_renderer"=>"pdf",
                "_preview"=>$this->Request()->preview,
                "_previewForcePagebreak"=>$this->Request()->pagebreak,
                "_previewSample"=>$this->Request()->sampleData,
                "_compatibilityMode"=>$this->Request()->compatibilityMode,
                "docComment"=>utf8_decode($this->Request()->docComment),
                "forceTaxCheck"=>$this->Request()->forceTaxCheck
            )
        );
        $document->render();
    }

    /**
     * Empty / Deprecated
     * @return void
     */
    public function settingsAction()
    {
    }

    /**
     * Add a new document type
     * @return void
     */
    public function addDocumentAction()
    {
        $this->View()->setTemplate();
        $insert = Shopware()->Db()->query("
        INSERT INTO s_core_documents (name,template,numbers,`left`,`right`,`top`,`bottom`,pagebreak)
        VALUES (
        'Neuer Beleg','index.tpl','doc','25','10','20','20','10'
        )
        ");
        $id = Shopware()->Db()->lastInsertId();
        $update = Shopware()->Db()->query("
        UPDATE s_core_documents SET numbers = ? WHERE id = ?
        ",array('doc_'.$id,$id));
        $sqlDuplicate = "INSERT IGNORE INTO s_core_documents_box
        SELECT
         NULL AS id,
         ? AS documentID ,
         `name`,
         `style`,
         `value`
        FROM s_core_documents_box WHERE `documentID` = ?;
        ";
        Shopware()->Db()->query($sqlDuplicate,array($id,1));

        $sql = "
        INSERT INTO s_order_number (number,name,`desc`)
        VALUES (
        '1000','doc_".$id."','Neuer Beleg'
        )
        ";

        $insert = Shopware()->Db()->query($sql);

    }

    /**
     * Delete an own document type
     * @return void
     */
    public function deleteDocumentAction()
    {
        $this->View()->setTemplate();
        $id = $this->request->id;
        $delete = Shopware()->Db()->query("
        DELETE FROM s_core_documents WHERE id = ?
        ",array($id));
        $deleteBoxes = Shopware()->Db()->query("
        DELETE FROM s_core_documents_box WHERE documentID = ?
        ",array($id));
        $deleteBoxes = Shopware()->Db()->query("
        DELETE FROM s_order_number WHERE name = ?
        ",array('own_'.$id));
    }
    /**
     * Update document properties
     * @access public
     */
    public function saveDocumentAction()
    {
        if (!empty($this->Request()->id)) {
            Shopware()->Db()->query("
            UPDATE s_core_documents SET
            name = ?,
            template = ?,
            numbers = ?,
            `left` = ?,
            `right` = ?,
            top = ?,
            bottom = ?,
            pagebreak = ?
            WHERE id = ?
            ",array(
            $this->Request()->name,
            $this->Request()->template,
            $this->Request()->numbers,
            $this->Request()->left,
            $this->Request()->right,
            $this->Request()->top,
            $this->Request()->bottom,
            $this->Request()->pagebreak,
            $this->Request()->id
            ));
        }
        return $this->forward('detail');
    }

    /**
     * Update properties of element box
     * @access public
     */
    public function saveDetailAction()
    {
        if ((!empty($this->Request()->ElementStyle) || !empty($this->Request()->ElementValue)) && !empty($this->Request()->id) && !empty($this->Request()->selectElement)) {
            //print_r(array($this->Request()->ElementStyle,$this->Request()->ElementValue,$this->Request()->selectElement,$this->Request()->id));exit;
            $update = Shopware()->Db()->query("
            UPDATE s_core_documents_box SET style = ?, value = ? WHERE id = ? AND documentID = ?
            ",array($this->Request()->ElementStyle,$this->Request()->ElementValue,$this->Request()->selectElement,$this->Request()->id));
        }
        return $this->forward('detail');
    }

    /**
     * Load properties of element box / document
     * @access public
     */
    public function detailAction()
    {
        $this->View()->assign('Config',Shopware()->Config());
        $this->View()->assign('Path',$this->path);
        $id = $this->Request()->id;

        if (empty($id)) {
            $this->View()->setTemplate();
        } else {
            $getDetail = Shopware()->Db()->fetchRow("
            SELECT * FROM s_core_documents WHERE id = ?
            ",array($id));
            if (empty($getDetail["id"])) {
                throw new Enlight_Exception("Document not found");
            }
            $this->View()->assign('Document',$getDetail);
            // Load Elements
            $getElements = Shopware()->Db()->fetchAll("
            SELECT * FROM s_core_documents_box WHERE documentID = ? ORDER BY name ASC
            ",array($getDetail["id"]));
            $this->View()->assign('Elements',$getElements);
            $this->View()->assign('id',$id);
            if (!empty($this->Request()->selectElement)) {
                $box = Shopware()->Db()->fetchRow("
                SELECT * FROM s_core_documents_box WHERE id = ?
                ",array($this->Request()->selectElement));
                $this->View()->assign('selectedElement',$box);
                $this->sInitTranslations(1,"documents",true);
                //$this->View()->assign('Translations',$this->sTranslations);
                $this->View()->assign('TranslationStyle',$this->sBuildTranslation("ElementStyle",$box["name"]."_Style",1,"documents",$id));
                $this->View()->assign('TranslationValue',$this->sBuildTranslation("ElementValue",$box["name"]."_Value",1,"documents",$id));
            }
        }
    }

    /**
     * Load available documents
     * @access public
     */
    public function getDocumentsAction()
    {
        $this->View()->setTemplate();
        $getDocuments = Shopware()->Db()->fetchAll("
        SELECT id, name AS text, template, numbers, 1 AS leaf FROM s_core_documents ORDER BY id ASC
        ");

        echo json_encode($getDocuments);

    }

    /**
     * Duplicate document properties
     * @return void
     */
    public function duplicatePropertiesAction()
    {
        $this->View()->setTemplate();
        $id = $this->Request()->id;

        // Update statement
        $getDocumentTypes = Shopware()->Db()->fetchAll("
        SELECT DISTINCT id FROM s_core_documents WHERE id != ?
        ",array($id));
        foreach ($getDocumentTypes as $targetID) {
            $deleteOldRows = Shopware()->Db()->query("
            DELETE FROM s_core_documents_box WHERE documentID = ?
            ",array($targetID["id"]));
            $sqlDuplicate = "
            INSERT IGNORE INTO s_core_documents_box
            SELECT
             NULL AS id,
             ? AS documentID ,
             `name`,
             `style`,
             `value`
            FROM s_core_documents_box WHERE `documentID` = ?;
            ";
            Shopware()->Db()->query($sqlDuplicate,array($targetID["id"],$id));
        }

//		return $this->forward("detail");
    }

    /**
     * Deprecated method to load translations, will be replaced in Shopware 4.
     * @access public
     */
    protected function sInitTranslations($key,$object,$addAdditionalKey="")
    {
        $queryLanguages = Shopware()->Db()->fetchAll("
        SELECT * FROM s_core_multilanguage
        WHERE
        skipbackend != 1
        ORDER BY id ASC
        ");
        $array = array();
        $this->sLanguages = $queryLanguages;
        foreach ($queryLanguages as $language) {
            $queryTranslation = Shopware()->Db()->fetchRow("
            SELECT * FROM s_core_translations
            WHERE
                objecttype = ?
            AND
                objectkey = ?
            AND
                objectlanguage = ?
            ",array($object,$key,$language["isocode"]));

            if ($addAdditionalKey) {
                $this->sTranslations[$object][$language["isocode"]] = unserialize($queryTranslation["objectdata"]);
            } else {
                $this->sTranslations[$language["isocode"]] = unserialize($queryTranslation["objectdata"]);
            }

        }

    }

    /**
     * Deprecated method to load translations, will be replaced in Shopware 4.
     * @access public
     */
    protected function sBuildTranslation($field,$key,$id,$object,$secondkey=0)
    {
        if (!$this->sLanguages || !@count($this->sLanguages)) return;
        foreach ($this->sLanguages as $language) {
            if ($secondkey) {

                if ($this->sTranslations[$object][$language["isocode"]][$secondkey][$key]) {
                    $opacity = "opacity:1";
                } else {
                    $opacity = "opacity:0.5";
                }
            } else {
                if ($this->sTranslations[$language["isocode"]][$key]) {
                    $opacity = "opacity:1";
                } else {
                    $opacity = "opacity:0.5";
                }
            }

            $style = "style=\"margin-left:10px;$opacity;cursor:pointer\"";
            $onclick = "onclick=\"sTranslations('$field','$key','$id','$object','{$language["isocode"]}','$secondkey')\"";
            $element .= "<img src=\"".$this->path."engine/backend/img/default/icons/flags/{$language["flagbackend"]}\" $style $onclick>";
        }
        return $element;
    }
}
