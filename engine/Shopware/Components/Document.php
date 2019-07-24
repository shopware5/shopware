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

use Shopware\Components\NumberRangeIncrementerInterface;

/**
 * Shopware document generator
 */
class Shopware_Components_Document extends Enlight_Class implements Enlight_Hook
{
    /**
     * Object from Type Model\Order
     *
     * @var \Shopware_Models_Document_Order
     */
    public $_order;

    /**
     * Shopware Template Object (Smarty)
     *
     * @var \Enlight_Template_Manager
     */
    public $_template;

    /**
     * Shopware View Object (Smarty)
     *
     * @var \Smarty_Data
     */
    public $_view;

    /**
     * Configuration
     *
     * @var array
     */
    public $_config;

    /**
     * Define output
     *
     * @var string html,pdf,return
     */
    public $_renderer = 'html';

    /**
     * Are properties already assigned to smarty?
     *
     * @var bool
     */
    public $_valuesAssigend = false;

    /**
     * Does this document support the creation of multiple documents of the same type instead of overwriting
     * existing ones?
     *
     * @var bool
     */
    public $_allowMultipleDocuments = false;

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
    public $_defaultPath = 'templates/Bare';

    /**
     * Generate preview only
     *
     * @var bool
     */
    public $_preview = false;

    /**
     * Typ/ID of document [0,1,2,3] - s_core_documents
     *
     * @var int
     */
    public $_typID;

    /**
     * Document-Metadata / Properties
     *
     * @var ArrayObject
     */
    public $_document;

    /**
     * Invoice / Document number
     *
     * @var int
     */
    public $_documentID;

    /**
     * Primary key of the created document row (s_order_documents)
     *
     * @var int
     */
    public $_documentRowID;

    /**
     * Hash of the created document row (s_order_documents.hash), will be used as filename when preview is false
     *
     * @var string
     */
    public $_documentHash;

    /**
     * Invoice ID for reference in shipping documents etc.
     *
     * @var string
     */
    public $_documentBid;

    /**
     * Ref to the translation component
     *
     * @var \Shopware_Components_Translation
     */
    public $translationComponent;

    /**
     * Static function to initiate document class
     *
     * @param int   $orderID    s_order.id
     * @param int   $documentID s_core_documents.id
     * @param array $config     - configuration array, for possible values see backend\document controller
     *
     * @throws Enlight_Exception
     *
     * @return \Shopware_Components_Document
     */
    public static function initDocument($orderID, $documentID, array $config = [])
    {
        if (empty($orderID)) {
            $config['_preview'] = true;
        }

        /** @var Shopware_Components_Document $document */
        $document = Enlight_Class::Instance('Shopware_Components_Document');

        $config = Shopware()->Container()->get('events')->filter(
            'Shopware_Models_Order_Document_Filter_Config',
            $config,
            [
                'subject' => $document,
                'orderID' => $orderID,
                'documentID' => $documentID,
            ]
        );

        /** @var Shopware_Models_Document_Order $documentOrder */
        $documentOrder = Enlight_Class::Instance('Shopware_Models_Document_Order', [$orderID, $config]);

        $document->setOrder($documentOrder);

        $document->setConfig($config);

        $document->setDocumentId($documentID);
        if (!empty($orderID)) {
            $document->_subshop = Shopware()->Db()->fetchRow("
                SELECT
                    s.id,
                    m.document_template_id as doc_template_id,
                    m.template_id as template_id,
                    (SELECT CONCAT('templates/', template) FROM s_core_templates WHERE id = m.document_template_id) as doc_template,
                    (SELECT CONCAT('templates/', template) FROM s_core_templates WHERE id = m.template_id) as template,
                    s.id as isocode,
                    s.locale_id as locale
                FROM s_order, s_core_shops s
                LEFT JOIN s_core_shops m
                    ON m.id=s.main_id
                    OR (s.main_id IS NULL AND m.id=s.id)
                WHERE s_order.language = s.id
                AND s_order.id = ?
                ",
                [$orderID]
            );

            if (empty($document->_subshop['doc_template'])) {
                $document->setTemplate($document->_defaultPath);
            }

            if (empty($document->_subshop['id'])) {
                throw new Enlight_Exception(sprintf('Could not load template path for order "%s"', $orderID));
            }
            if (!empty($config['_allowMultipleDocuments'])) {
                $document->_allowMultipleDocuments = $config['_allowMultipleDocuments'];
            }
        } else {
            $document->_subshop = Shopware()->Db()->fetchRow("
            SELECT
                s.id,
                s.document_template_id as doc_template_id,
                s.template_id,
                (SELECT CONCAT('templates/', template) FROM s_core_templates WHERE id = s.document_template_id) as doc_template,
                (SELECT CONCAT('templates/', template) FROM s_core_templates WHERE id = s.template_id) as template,
                s.id as isocode,
                s.locale_id as locale
            FROM s_core_shops s
            WHERE s.default = 1
            ");

            if (empty($document->_subshop['doc_template'])) {
                $document->setTemplate($document->_defaultPath);
                $document->_subshop['doc_template'] = $document->_defaultPath;
            }
        }

        $document->setTranslationComponent();
        $document->initTemplateEngine();

        return $document;
    }

    /**
     * Start renderer / pdf-generation
     *
     * @param string $_renderer optional define renderer (pdf,html,return)
     *
     * @throws \Enlight_Event_Exception
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     */
    public function render($_renderer = '')
    {
        if (!empty($_renderer)) {
            $this->_renderer = $_renderer;
        }
        if ($this->_valuesAssigend == false) {
            $this->assignValues();
        }

        /* @var \Shopware\Models\Shop\Template $template */
        if (!empty($this->_subshop['doc_template_id'])) {
            $template = Shopware()->Container()->get('models')->find(\Shopware\Models\Shop\Template::class, $this->_subshop['doc_template_id']);

            $inheritance = Shopware()->Container()->get('theme_inheritance')->getTemplateDirectories($template);
            $this->_template->setTemplateDir($inheritance);
        }

        $html = $this->_template->fetch('documents/' . $this->_document['template'], $this->_view);

        /** @var \Enlight_Event_EventManager $eventManager */
        $eventManager = Shopware()->Container()->get('events');
        $html = $eventManager->filter('Shopware_Components_Document_Render_FilterHtml', $html, [
            'subject' => $this,
        ]);

        if ($this->_renderer === 'html' || !$this->_renderer) {
            echo $html;
        } elseif ($this->_renderer === 'pdf') {
            $defaultConfig = Shopware()->Container()->getParameter('shopware.mpdf.defaultConfig');
            $defaultConfig = $eventManager->filter(
                'Shopware_Components_Document_Render_FilterMpdfConfig',
                $defaultConfig,
                [
                    'template' => $this->_document['template'],
                    'document' => $this->_document,
                ]
            );
            $mpdfConfig = array_replace_recursive(
                $defaultConfig,
                [
                    'margin_left' => $this->_document['left'],
                    'margin_right' => $this->_document['right'],
                    'margin_top' => $this->_document['top'],
                    'margin_bottom' => $this->_document['bottom'],
                ]
            );
            if ($this->_preview == true || !$this->_documentHash) {
                $mpdf = new \Mpdf\Mpdf($mpdfConfig);
                $mpdf->WriteHTML($html);
                $mpdf->Output();
                exit;
            }

            $tmpFile = tempnam(sys_get_temp_dir(), 'document');
            $mpdf = new \Mpdf\Mpdf($mpdfConfig);
            $mpdf->WriteHTML($html);
            $mpdf->Output($tmpFile, 'F');

            $stream = fopen($tmpFile, 'rb');
            $path = sprintf('documents/%s.pdf', $this->_documentHash);

            $filesystem = Shopware()->Container()->get('shopware.filesystem.private');
            $filesystem->putStream($path, $stream);
            unlink($tmpFile);
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
     * Set template path
     *
     * @param string $path
     */
    public function setTemplate($path)
    {
        if (!empty($path)) {
            $this->_subshop['doc_template'] = $path;
        }
    }

    /**
     * @param string $renderer
     */
    public function setRenderer($renderer)
    {
        $this->_renderer = $renderer;
    }

    /**
     * Set type of document (0,1,2,3) > s_core_documents
     *
     * @param int $id
     */
    public function setDocumentId($id)
    {
        $this->_typID = $id;
    }

    /**
     * Get voucher (s_vouchers.id)
     *
     * @param int $id
     *
     * @return bool|mixed
     */
    public function getVoucher($id)
    {
        if (empty($id)) {
            return false;
        }

        // Check if voucher is available
        $sqlVoucher = 'SELECT s_emarketing_voucher_codes.id AS id, code, description, value, percental FROM s_emarketing_vouchers, s_emarketing_voucher_codes
         WHERE  modus = 1 AND (valid_to >= CURDATE() OR valid_to IS NULL)
         AND s_emarketing_voucher_codes.voucherID = s_emarketing_vouchers.id
         AND s_emarketing_voucher_codes.userID IS NULL
         AND s_emarketing_voucher_codes.cashed = 0
         AND s_emarketing_vouchers.id=?
         Limit 1
         ';

        $getVoucher = Shopware()->Db()->fetchRow($sqlVoucher, [$id]);
        if ($getVoucher['id']) {
            // Update Voucher and pass-information to template
            Shopware()->Db()->query('
            UPDATE s_emarketing_voucher_codes
            SET
                userID = ?
            WHERE
                id = ?
            ', [$this->_order->userID, $getVoucher['id']]);
            if ($this->_order->currency->factor != 1) {
                $getVoucher['value'] *= $this->_order->currency->factor;
            }
            if (!empty($getVoucher['percental'])) {
                $getVoucher['prefix'] = '%';
            } else {
                $getVoucher['prefix'] = $this->_order->currency->char;
            }
        }

        return $getVoucher;
    }

    /**
     * Get user_attributes (s_user_attributes)
     *
     * @param int $userID
     *
     * @throws \Exception
     *
     * @return array
     */
    public function getUserAttributes($userID)
    {
        if (empty($userID)) {
            return [];
        }

        $service = Shopware()->Container()->get('shopware_attribute.data_loader');

        return $service->load('s_user_attributes', $userID);
    }

    /**
     * Assign configuration / data to template, new template base
     */
    protected function assignValues4x()
    {
        if ($this->_preview == true) {
            $id = 12345;
        } else {
            $id = $this->_documentID;
        }

        $Document = $this->_document->getArrayCopy();
        if (empty($this->_config['date'])) {
            $this->_config['date'] = date('d.m.Y');
        }
        $Document = array_merge(
            $Document,
            [
                'comment' => $this->_config['docComment'],
                'id' => $id,
                'bid' => $this->_documentBid,
                'date' => $this->_config['date'],
                'deliveryDate' => $this->_config['delivery_date'],
                // The "netto" config flag, if set to true, allows creating
                // netto documents for brutto orders. Setting it to false,
                // does not however create brutto documents for netto orders.
                'netto' => $this->_order->order->taxfree ? true : $this->_config['netto'],
                'nettoPositions' => $this->_order->order->net,
            ]
        );
        $Document['voucher'] = $this->getVoucher($this->_config['voucher']);
        $this->_view->assign('Document', $Document);

        // Translate payment and dispatch depending on the order's language
        // and replace the default payment/dispatch text
        $dispatchId = $this->_order->order->dispatchID;
        $paymentId = $this->_order->order->paymentID;
        $translationPayment = $this->readTranslationWithFallback($this->_order->order->language, 'config_payment');
        $translationDispatch = $this->readTranslationWithFallback($this->_order->order->language, 'config_dispatch');

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
                $this->_order->dispatch->description = $translationDispatch[$dispatchId]['dispatch_description'];
            }
        }

        $this->_view->assign('Order', $this->_order->__toArray());
        $this->_view->assign('Containers', $this->_document->containers->getArrayCopy());

        $order = clone $this->_order;

        $positions = $order->positions->getArrayCopy();

        $articleModule = Shopware()->Modules()->Articles();
        foreach ($positions as &$position) {
            if ($position['modus'] == 0) {
                $position['meta'] = $articleModule->sGetPromotionById('fix', 0, $position['articleordernumber']);
            }
        }

        if ($this->_config['_previewForcePagebreak']) {
            $positions = array_merge($positions, $positions);
        }

        $positions = array_chunk($positions, $this->_document['pagebreak'], true);
        $this->_view->assign('Pages', $positions);

        $user = [
            'shipping' => $order->shipping,
            'billing' => $order->billing,
            'additional' => [
                'countryShipping' => $order->shipping->country,
                'country' => $order->billing->country,
            ],
            'attributes' => $this->getUserAttributes($order->userID),
        ];
        $this->_view->assign('User', $user);
    }

    /**
     * Loads translations including fallbacks
     *
     * @param string $languageId
     * @param string $type
     *
     * @return array
     */
    protected function readTranslationWithFallback($languageId, $type)
    {
        $fallbackLanguageId = Shopware()->Db()->fetchOne(
            'SELECT fallback_id FROM s_core_shops WHERE id = ?',
            [$languageId]
        );

        return $this->translationComponent->readBatchWithFallback($languageId, $fallbackLanguageId, $type);
    }

    /**
     * Load template / document configuration (s_core_documents / s_core_documents_box)
     */
    protected function loadConfiguration4x()
    {
        $id = $this->_typID;

        $this->_document = new ArrayObject(
            Shopware()->Db()->fetchRow(
                'SELECT * FROM s_core_documents WHERE id = ?',
                [$id],
                \PDO::FETCH_ASSOC
            )
        );

        // Load Containers
        $containers = Shopware()->Db()->fetchAll(
            'SELECT * FROM s_core_documents_box WHERE documentID = ?',
            [$id],
            \PDO::FETCH_ASSOC
        );

        $translation = $this->translationComponent->read($this->_order->order->language, 'documents');
        $this->_document->containers = new ArrayObject();
        foreach ($containers as $key => $container) {
            if (!is_numeric($key)) {
                continue;
            }
            if (!empty($translation[$id][$container['name'] . '_Value'])) {
                $containers[$key]['value'] = $translation[$id][$container['name'] . '_Value'];
            }
            if (!empty($translation[$id][$container['name'] . '_Style'])) {
                $containers[$key]['style'] = $translation[$id][$container['name'] . '_Style'];
            }

            // Parse smarty tags
            $containers[$key]['value'] = $this->_template->fetch('string:' . $containers[$key]['value']);

            $this->_document->containers->offsetSet($container['name'], $containers[$key]);
        }
    }

    /**
     * Initiate smarty template engine
     *
     * @throws \Exception
     */
    protected function initTemplateEngine()
    {
        $frontendThemeDirectory = Shopware()->Container()->get('theme_path_resolver')->getFrontendThemeDirectory();

        $this->_template = clone Shopware()->Template();
        $this->_view = $this->_template->createData();

        $path = basename($this->_subshop['doc_template']);

        if ($this->_template->security_policy) {
            $this->_template->security_policy->secure_dir[] = $frontendThemeDirectory . DIRECTORY_SEPARATOR . $path;
        }
        $this->_template->setTemplateDir(['custom' => $path]);
        $this->_template->setCompileId(str_replace('/', '_', $path) . '_' . $this->_subshop['id']);
    }

    /**
     * Sets the translation component
     *
     * @throws \Exception
     */
    protected function setTranslationComponent()
    {
        $this->translationComponent = Shopware()->Container()->get('translation');
    }

    /**
     * @throws \Exception
     */
    protected function setOrder(Shopware_Models_Document_Order $order)
    {
        $this->_order = $order;

        $repository = Shopware()->Models()->getRepository(\Shopware\Models\Shop\Shop::class);
        // "language" actually refers to a language-shop and not to a locale
        $shop = $repository->getById($this->_order->order->language);

        if (!empty($this->_order->order->currencyID)) {
            $repository = Shopware()->Models()->getRepository(\Shopware\Models\Shop\Currency::class);
            $shop->setCurrency($repository->find($this->_order->order->currencyID));
        }

        Shopware()->Container()->get('shopware.components.shop_registration_service')->registerResources($shop);
    }

    /**
     * Set object configuration from array
     */
    protected function setConfig(array $config)
    {
        $this->_config = $config;
        foreach ($config as $key => $v) {
            if (property_exists($this, $key)) {
                $this->$key = $v;
            }
        }
    }

    /**
     * Save document in database / generate number
     *
     * @throws \Exception
     * @throws \RuntimeException
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function saveDocument()
    {
        if ($this->_preview == true) {
            return;
        }

        $bid = $this->_config['bid'];
        if (!empty($bid)) {
            $this->_documentBid = $bid;
        }
        if (empty($bid)) {
            $bid = 0;
        }

        // Check if this kind of document already exists
        $typID = $this->_typID;

        $checkForExistingDocument = Shopware()->Db()->fetchRow('
        SELECT id , docID , hash FROM s_order_documents WHERE userID = ? AND orderID = ? AND `type` = ?
        ', [$this->_order->userID, $this->_order->id, $typID]);

        if (!$this->_allowMultipleDocuments && !empty($checkForExistingDocument['id'])) {
            // Document already exist, and multiple documents are not allowed. Update date and amount!
            $update = '
            UPDATE `s_order_documents` SET `date` = now(),`amount` = ?
            WHERE `type` = ? AND userID = ? AND orderID = ? LIMIT 1
            ';
            $amount = ($this->_order->order->taxfree ? true : $this->_config['netto']) ? round($this->_order->amountNetto, 2) : round($this->_order->amount, 2);
            if ($typID == 4) {
                $amount *= -1;
            }
            Shopware()->Db()->query($update, [
                    $amount,
                    $typID,
                    $this->_order->userID,
                    $this->_order->id,
                ]);

            if (!empty($this->_config['attributes'])) {
                // Get the updated document
                $updatedDocument = Shopware()->Models()->getRepository(\Shopware\Models\Order\Document\Document::class)->findOneBy([
                    'type' => $typID,
                    'customerId' => $this->_order->userID,
                    'orderId' => $this->_order->id,
                ]);
                // Check its attributes
                if ($updatedDocument->getAttribute() === null) {
                    // Create a new attributes entity for the document
                    $documentAttributes = new \Shopware\Models\Attribute\Document();
                    $updatedDocument->setAttribute($documentAttributes);
                    // Persist the document
                    Shopware()->Models()->flush($updatedDocument);
                }
                // Save all given attributes
                $updatedDocument->getAttribute()->fromArray($this->_config['attributes']);
                // Persist the attributes
                Shopware()->Models()->flush($updatedDocument->getAttribute());
            }

            $rowID = $checkForExistingDocument['id'];
            $bid = $checkForExistingDocument['docID'];
            $hash = $checkForExistingDocument['hash'];
        } else {
            // Create new document

            $hash = md5(uniqid((string) rand()));

            $amount = ($this->_order->order->taxfree ? true : $this->_config['netto']) ? round($this->_order->amountNetto, 2) : round($this->_order->amount, 2);
            if ($typID == 4) {
                $amount *= -1;
            }
            $sql = '
            INSERT INTO s_order_documents (`date`, `type`, `userID`, `orderID`, `amount`, `docID`,`hash`)
            VALUES ( NOW() , ? , ? , ?, ?, ?,?)
            ';
            Shopware()->Db()->query($sql, [
                $typID,
                $this->_order->userID,
                $this->_order->id,
                $amount,
                $bid,
                $hash,
            ]);
            $rowID = Shopware()->Db()->lastInsertId();

            // Add an entry in s_order_documents_attributes for the created document
            // containing all values found in the 'attributes' element of '_config'
            $createdDocument = Shopware()->Models()->getRepository('\Shopware\Models\Order\Document\Document')->findOneById($rowID);
            // Create a new attributes entity for the document
            $documentAttributes = new \Shopware\Models\Attribute\Document();
            $createdDocument->setAttribute($documentAttributes);
            if (!empty($this->_config['attributes'])) {
                // Save all given attributes
                $createdDocument->getAttribute()->fromArray($this->_config['attributes']);
            }
            // Persist the document
            Shopware()->Models()->flush($createdDocument);

            // Update numberrange, except for cancellations
            if ($typID != 4) {
                if (!empty($this->_document['numbers'])) {
                    $numberrange = $this->_document['numbers'];
                } else {
                    // The typID is indexed with base 0, so we need increase the typID
                    if (!in_array($typID, ['1', '2', '3'])) {
                        $typID = $typID + 1;
                    }
                    $numberrange = 'doc_' . $typID;
                }

                /** @var NumberRangeIncrementerInterface $incrementer */
                $incrementer = Shopware()->Container()->get('shopware.number_range_incrementer');

                // Get the next number and save it in the document
                $nextNumber = $incrementer->increment($numberrange);

                Shopware()->Db()->query('
                    UPDATE `s_order_documents` SET `docID` = ? WHERE `id` = ? LIMIT 1 ;
                ', [$nextNumber, $rowID]);

                $bid = $nextNumber;
            }
        }
        $this->_documentID = $bid;
        $this->_documentRowID = $rowID;
        $this->_documentHash = $hash;
    }
}
