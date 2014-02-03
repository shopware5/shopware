<?php
/**
 * Shopware 4.0
 * Copyright © 2013 shopware AG
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

include_once(Shopware()->OldPath() . "engine/Library/Mpdf/mpdf.php");

/**
 * Shopware document generator
 *
 * @category  Shopware
 * @package   Shopware\Components\Document
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class Shopware_Components_Document extends Enlight_Class implements Enlight_Hook
{
    /**
     * Object from Type Model\Order
     *
     * @var object Model\Order
     */
    public $_order;

    /**
     * Shopware Template Object (Smarty)
     *
     * @var object
     */
    public $_template;

    /**
     * Shopware View Object (Smarty)
     *
     * @var object
     */
    public $_view;

    /**
     * Configuration
     * @var array
     */
    public $_config;

    /**
     * compatibilityMode = true means that html2ps will be used instead of mpdf.
     * Additionally old templatebase will be used (For pre 3.5 versions)
     *
     * Unsupported till shopware 4.0.0
     * @var bool
     * @deprecated
     */
    protected $_compatibilityMode = false;

    /**
     * Define output
     *
     * @var string html,pdf,return
     */
    protected $_renderer = "html";

    /**
     * Are properties already assigned to smarty?
     *
     * @var bool
     */
    protected $_valuesAssigend = false;

    /**
     * Subshop-Configuration
     *
     * @var array
     */
    public $_subshop;

    /**
     * Path to load templates from
     *
     * @var string
     */
    public $_defaultPath = "templates/_default";

    /**
     * Generate preview only
     *
     * @var bool
     */
    protected $_preview = false;

    /**
     * Typ/ID of document [0,1,2,3] - s_core_documents
     *
     * @var int
     */
    protected $_typID;

    /**
     * Document-Metadata / Properties
     *
     * @var array
     */
    protected $_document;

    /**
     * Invoice / Document number
     *
     * @var int
     */
    protected $_documentID;

    /**
     * Primary key of the created document row (s_order_documents)
     *
     * @var int
     */
    protected $_documentRowID;

    /**
     * Hash of the created document row (s_order_documents.hash), will be used as filename when preview is false
     *
     * @var string
     */
    protected $_documentHash;

    /**
     * Invoice ID for reference in shipping documents etc.
     *
     * @var string
     */
    protected $_documentBid;

    /**
     * Ref to the translation component
     *
     * @var \Shopware_Components_Translation
     */
    protected $translationComponent;

    /**
     * Static function to initiate document class
     * @param  int                           $orderID    s_order.id
     * @param  int                           $documentID s_core_documents.id
     * @param  array                         $config     - configuration array, for possible values see backend\document controller
     * @throws Enlight_Exception
     * @return \Shopware_Components_Document
     */
    public static function initDocument($orderID, $documentID, array $config = array())
    {
        if (empty($orderID)) {
            $config["_preview"] = true;
        }

        /** @var $d Shopware_Components_Document */
        $d = Enlight_Class::Instance('Shopware_Components_Document');//new Shopware_Components_Document();

        //$d->setOrder(new Shopware_Models_Document_Order($orderID,$config));
        $d->setOrder(Enlight_Class::Instance('Shopware_Models_Document_Order', array($orderID,$config)));

        $d->setConfig($config);

        $d->setDocumentId($documentID);
        $d->_compatibilityMode = false;
        if (!empty($orderID)) {
            $d->_subshop = Shopware()->Db()->fetchRow("
            SELECT s_core_multilanguage.id,doc_template, template, isocode,locale FROM s_order,s_core_multilanguage WHERE s_order.language = s_core_multilanguage.id AND s_order.id = ?
            ",array($orderID));

            if (empty($d->_subshop["doc_template"])) $d->setTemplate($d->_defaultPath);

            if (empty($d->_subshop["id"])) {
                throw new Enlight_Exception("Could not load template path for order $orderID");
            }

        } else {

            $d->_subshop = Shopware()->Db()->fetchRow("
            SELECT s_core_multilanguage.id,doc_template, template, isocode,locale FROM s_core_multilanguage WHERE s_core_multilanguage.default = 1
            ");

            $d->setTemplate($d->_defaultPath);
            $d->_subshop["doc_template"] = $d->_defaultPath;

        }

        $d->setTranslationComponent();
        $d->initTemplateEngine();

        return $d;
    }

    /**
     * Start renderer / pdf-generation
     * @param string optional define renderer (pdf,html,return)
     */
    public function render($_renderer = "")
    {
        if (!empty($_renderer)) $this->_renderer = $_renderer;
        if ($this->_valuesAssigend == false) {
            $this->assignValues();
        }

        $data = $this->_template->fetch("documents/".$this->_document["template"],$this->_view);

        if ($this->_renderer == "html" || !$this->_renderer) {
            echo $data;
        } elseif ($this->_renderer == "pdf") {
            if ($this->_preview == true || !$this->_documentHash) {
                $mpdf = new mPDF("utf-8","A4","","",$this->_document["left"],$this->_document["right"],$this->_document["top"],$this->_document["bottom"]);
                $mpdf->WriteHTML($data);
                $mpdf->Output();
                exit;
            } else {
                $path = Shopware()->OldPath()."files/documents"."/".$this->_documentHash.".pdf";
                $mpdf = new mPDF("utf-8","A4","","",$this->_document["left"],$this->_document["right"],$this->_document["top"],$this->_document["bottom"]);
                $mpdf->WriteHTML($data);
                $mpdf->Output($path,"F");
            }
        }

    }

    /**
     * Assign configuration / data to template
     */
    public function assignValues()
    {
        $this->loadConfiguration4x();

        if (!$this->_preview) {
            $this->saveDocument();
        }

        return $this->assignValues4x();

    }

    /**
     * Assign configuration / data to template, new templatebase
     */
    protected function assignValues4x()
    {
        if ($this->_preview == true) {
            $id = 12345;
        } else {
            $id = $this->_documentID;
        }

        $Document = $this->_document->getArrayCopy();
        if (empty($this->_config["date"])) {
            $this->_config["date"] = date("d.m.Y");
        }
        $Document = array_merge($Document,array("comment"=>$this->_config["docComment"],"id"=>$id,"bid"=>$this->_documentBid,"date"=>$this->_config["date"],"deliveryDate"=>$this->_config["delivery_date"],"netto"=>$this->_order->order->taxfree ? true : $this->_config["netto"],"nettoPositions"=>$this->_order->order->net));
        $Document["voucher"] = $this->getVoucher($this->_config["voucher"]);
        $this->_view->assign('Document',$Document);

        // Translate payment and dispatch depending on the order's language
        // and replace the default payment/dispatch text
        $dispatchId = $this->_order->order->dispatchID;
        $paymentId  = $this->_order->order->paymentID;
        $translationPayment = $this->translationComponent->read($this->_order->order->language, 'config_payment', 1);
        $translationDispatch = $this->translationComponent->read($this->_order->order->language, 'config_dispatch', 1);

        if (isset($translationPayment[$paymentId])) {
            if (isset($translationPayment[$paymentId]['description'])) {
                $this->_order->payment->description = $translationPayment[$paymentId]['description'];
            }
            if (isset($translationPayment[$paymentId]['additionalDescription'])) {
                $this->_order->payment->additionaldescription = $translationPayment[$paymentId]['additionalDescription'];
            }
        }

        if (isset($translationDispatch[$dispatchId])) {
            if (isset($translationDispatch[$dispatchId]['dispatch_name'])) {
                $this->_order->dispatch->name = $translationDispatch[$dispatchId]['dispatch_name'];
            }
            if (isset($translationDispatch[$dispatchId]['dispatch_description'])) {
                $this->_order->dispatch->description= $translationDispatch[$dispatchId]['dispatch_description'];
            }
        }

        $this->_view->assign('Order',$this->_order->__toArray());
        $this->_view->assign('Containers',$this->_document->containers->getArrayCopy());

        $order = clone $this->_order;

        $positions = $order->positions->getArrayCopy();

        $articleModule = Shopware()->Modules()->Articles();
        foreach ($positions as &$position) {
            $position['meta'] = $articleModule->sGetPromotionById('fix', 0, (int) $position['articleID']);
        }

        if ($this->_config["_previewForcePagebreak"]) {
            $positions = array_merge($positions,$positions);
        }

        $positions = array_chunk($positions,$this->_document["pagebreak"],true);
        $this->_view->assign('Pages',$positions);

        $user = array(
            "shipping"=>$order->shipping,
            "billing"=>$order->billing,
            "additional"=>array("countryShipping"=>$order->shipping->country,"country"=>$order->billing->country)
        );
        $this->_view->assign('User',$user);
    }

    /**
     * Load template / document configuration (s_core_documents / s_core_documents_box)
     */
    protected function loadConfiguration4x()
    {
        $id = $this->_typID;

        $this->_document = new ArrayObject(Shopware()->Db()->fetchRow("
        SELECT * FROM s_core_documents WHERE id = ?
        ",array($id),ArrayObject::ARRAY_AS_PROPS));


        // Load Containers
        $this->_document->containers = new ArrayObject(Shopware()->Db()->fetchAll("
        SELECT * FROM s_core_documents_box WHERE documentID = ?
        ",array($id),ArrayObject::ARRAY_AS_PROPS));

        $translation = $this->translationComponent->read($this->_order->order->language, 'documents', 1);

        foreach ($this->_document->containers as $key => $container) {

            if (!is_numeric($key)) continue;
            if (!empty($translation[$id][$container["name"]."_Value"])) {
                $this->_document->containers[$key]["value"] = $translation[$id][$container["name"]."_Value"];
            }
            if (!empty($translation[$id][$container["name"]."_Style"])) {
                $this->_document->containers[$key]["style"] = $translation[$id][$container["name"]."_Style"];
            }
            $this->_document->containers[$container["name"]] = $this->_document->containers[$key];
            unset($this->_document->containers[$key]);
        }

    }

    /**
     * Set template path
     */
    public function setTemplate($path)
    {
        if (!empty($path)) {
            $this->_subshop["doc_template"] = $path;
        }
    }

    /**
     * Set renderer
     */
    public function setRenderer($renderer)
    {
        $this->_renderer = $renderer;
    }

    /**
     * Set type of document (0,1,2,3) > s_core_documents
     */
    public function setDocumentId($id)
    {
        $this->_typID = $id;
    }

    /**
     * Get voucher (s_vouchers.id)
     */
    public function getVoucher($id)
    {
        if (empty($id)) return false;

        // Check if voucher is available
        $sqlVoucher = "SELECT s_emarketing_voucher_codes.id AS id, code, description, value, percental FROM s_emarketing_vouchers, s_emarketing_voucher_codes
         WHERE  modus = 1 AND (valid_to >= now() OR valid_to IS NULL)
         AND s_emarketing_voucher_codes.voucherID = s_emarketing_vouchers.id
         AND s_emarketing_voucher_codes.userID IS NULL
         AND s_emarketing_voucher_codes.cashed = 0
         AND s_emarketing_vouchers.id=?
         GROUP BY s_emarketing_voucher_codes.voucherID
         ";

        $getVoucher = Shopware()->Db()->fetchRow($sqlVoucher,array($id));
        if ($getVoucher["id"]) {
            // Update Voucher and pass-information to template
            $updateVoucher = Shopware()->Db()->query("
            UPDATE s_emarketing_voucher_codes
            SET
                userID = ?
            WHERE
                id = ?
            ",array($this->_order->userID,$getVoucher["id"]));
            if ($this->_order->currency->factor!=1) {
                $getVoucher["value"]*=$this->_order->currency->factor;
            }
            $getVoucher["value"] = $getVoucher["value"];
            if (!empty($getVoucher["percental"])) {
                $getVoucher["prefix"] = "%";
            } else {
                $getVoucher["prefix"] = $this->_order->currency->char;
            }
        }

        return $getVoucher;
    }

    /**
     * Initiate smarty template engine
     */
    protected function initTemplateEngine()
    {
        $this->_template = clone Shopware()->Template();
        $this->_view = $this->_template->createData();

        $path = basename($this->_subshop["doc_template"]);

        $this->_template->setTemplateDir(array(
                'custom' => $path,
                'local' => '_local',
                'emotion' => '_default',
            ));

        $this->_template->setCompileId(str_replace('/', '_', $path).'_'.$this->_subshop['id']);
    }

    /**
     * Sets the translation component
     */
    protected function setTranslationComponent()
    {
        $this->translationComponent = new Shopware_Components_Translation();
    }

    /**
     * Set order
     */
    protected function setOrder(Shopware_Models_Document_Order $order)
    {
        $this->_order = $order;

        $repository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');
        // "language" actually refers to a language-shop and not to a locale
        $shop = $repository->getActiveById($this->_order->order->language);
        if(!empty($this->_order->order->currencyID)) {
            $repository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Currency');
            $shop->setCurrency($repository->find($this->_order->order->currencyID));
        }
        $shop->registerResources(Shopware()->Bootstrap());
    }

    /**
     * Set object configuration from array
     */
    protected function setConfig (array $config)
    {
        $this->_config = $config;
        foreach ($config as $key => $v) {
            if (property_exists($this,$key)) {
                $this->$key = $v;
            }
        }
    }

    /**
     * Save document in database / generate number
     */
    protected function saveDocument()
    {
        if ($this->_preview==true) return;

        $bid = $this->_config["bid"];
        if (!empty($bid)) {
            $this->_documentBid = $bid;
        }
        if (empty($bid)) $bid = 0;

        // Check if this kind of document already exists
        $typID = $this->_typID;

        $checkForExistingDocument = Shopware()->Db()->fetchRow("
        SELECT ID,docID,hash FROM s_order_documents WHERE userID = ? AND orderID = ? AND type = ?
        ",array($this->_order->userID,$this->_order->id,$typID));

        if (!empty($checkForExistingDocument["ID"])) {
            // Document already exist. Update date and amount!
            $update = "
            UPDATE `s_order_documents` SET `date` = now(),`amount` = ?
            WHERE `type` = ? AND userID = ? AND orderID = ? LIMIT 1
            ";
            $amount = $this->_config["netto"] == true ? round($this->_order->amountNetto,2) : round($this->_order->amount,2);
            if ($typID == 4) {
                $amount *= -1;
            }
            $update = Shopware()->Db()->query($update,array(
                    $amount,
                    $typID,
                    $this->_order->userID,
                    $this->_order->id
                ));

            if (!empty($this->_config["attributes"])) {
                // Get the updated document
                $updatedDocument = Shopware()->Models()->getRepository("\Shopware\Models\Order\Document\Document")->findOneBy(array(
                    "type" => $typID,
                    "customerId" => $this->_order->userID,
                    "orderId" => $this->_order->id
                ));
                // Check its attributes
                if ($updatedDocument->getAttribute() === null) {
                    // Create a new attributes entity for the document
                    $documentAttributes = new \Shopware\Models\Attribute\Document();
                    $updatedDocument->setAttribute($documentAttributes);
                    // Persist the document
                    Shopware()->Models()->flush($updatedDocument);
                }
                // Update all attributes found in '_config'
                if (!empty($this->_config["attributes"])) {
                    // Save all given attributes
                    foreach ($this->_config["attributes"] as $key => $value) {
                        $setter = "set" . ucfirst($key);
                        if (method_exists($updatedDocument->getAttribute(), $setter)) {
                            $updatedDocument->getAttribute()->$setter($value);
                        }
                    }
                }
                // Persist the attributes
                Shopware()->Models()->flush($updatedDocument->getAttribute());
            }

            $rowID = $checkForExistingDocument["ID"];
            $bid = $checkForExistingDocument["docID"];
            $hash = $checkForExistingDocument["hash"];
        } else {
            // Create new document

            $hash = md5(uniqid(rand()));

            $amount = $this->_config["netto"] == true ? round($this->_order->amountNetto,2) : round($this->_order->amount,2);
            if ($typID == 4) {
                $amount *= -1;
            }
            $sql = "
            INSERT INTO s_order_documents (`date`, `type`, `userID`, `orderID`, `amount`, `docID`,`hash`)
            VALUES ( NOW() , ? , ? , ?, ?, ?,?)
            ";
            $insert = Shopware()->Db()->query($sql,array(
                    $typID,
                    $this->_order->userID,
                    $this->_order->id,
                    $amount,
                    $bid,
                    $hash
                ));
            $rowID = Shopware()->Db()->lastInsertId();

            // Add an entry in s_order_documents_attributes for the created document
            // containing all values found in the 'attributes' element of '_config'
            $createdDocument = Shopware()->Models()->getRepository('\Shopware\Models\Order\Document\Document')->findOneById($rowID);
            // Create a new attributes entity for the document
            $documentAttributes = new \Shopware\Models\Attribute\Document();
            $createdDocument->setAttribute($documentAttributes);
            if (!empty($this->_config['attributes'])) {
                // Save all given attributes
                foreach ($this->_config['attributes'] as $key => $value) {
                    $setter = "set" . ucfirst($key);
                    if (method_exists($createdDocument->getAttribute(), $setter)) {
                        $createdDocument->getAttribute()->$setter($value);
                    }
                }
            }
            // Persist the document
            Shopware()->Models()->flush($createdDocument);

            // Update numberrange, except for cancellations
            if ($typID!=4) {
                if (!empty($this->_document['numbers'])) {
                    $numberrange = $this->_document['numbers'];
                } else {

                    // The typID is indexed with base 0, so we need increase the typID
                    if (!in_array($typID, array('1', '2', '3'))) {
                        $typID = $typID +1;
                    }
                    $numberrange = "doc_".$typID;
                }

                $checkForSeparateNumbers = Shopware()->Db()->fetchRow("
                    SELECT id, separate_numbers
                    FROM `s_core_multilanguage`
                    WHERE `id` = ?
                ",array($this->_subshop["id"]));

                if (!empty($checkForSeparateNumbers['separate_numbers'])) {
                    $numberrange.= "_".$checkForSeparateNumbers['id'];
                }
                $getNumber = Shopware()->Db()->fetchRow("
                    SELECT `number`+1 as next FROM `s_order_number` WHERE `name` = ?"
                    ,array($numberrange));

                Shopware()->Db()->query("
                    UPDATE `s_order_documents` SET `docID` = ? WHERE `ID` = ? LIMIT 1 ;
                ",array($getNumber['next'],$rowID));

                Shopware()->Db()->query("
                    UPDATE `s_order_number` SET `number` = ? WHERE `name` = ? LIMIT 1 ;
                ",array($getNumber['next'],$numberrange));

                $bid = $getNumber["next"];

            }
        }
        $this->_documentID = $bid;
        $this->_documentRowID = $rowID;
        $this->_documentHash = $hash;
    }
}
