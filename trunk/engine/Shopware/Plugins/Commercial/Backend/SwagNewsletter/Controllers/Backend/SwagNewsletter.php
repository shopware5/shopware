<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 *
 * @category   Shopware
 * @package    Shopware_Controllers
 * @subpackage NewsletterManager
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Daniel Nögel
 * @author     $Author$
 */

/**
 * Shopware Backend Controller
 * Backend for various ajax queries
 */
class Shopware_Controllers_Backend_SwagNewsletter extends Shopware_Controllers_Backend_ExtJs
{

    // Used to store a reference to the newsletter repository
    protected $campaignsRepository = null;

    // used to map betweern the new emotion-style containers and the old newsletter containers
    protected $componentMapping = array();

    /**
     * Helper function to get a component by the corresponding newsletter container
     * @param $containerType
     * @throws \Exception
     * @return array
     */
    public function getComponentByContainerType($containerType) {

        $containerTypeMapping = array(
            'ctText' => 'newsletter-html-text-element',
            'ctBanner' => 'newsletter-banner-element',
            'ctArticles' => 'newsletter-article-element',
            'ctLinks' => 'newsletter-link-element',
            'ctVoucher' => 'newsletter-voucher-element',
            'ctSuggest' => 'newsletter-suggest-element',
        );

        if(!array_key_exists($containerType, $containerTypeMapping)) {
            throw new \Exception("Container type {$containerType} is not valid");
        }

        $cls = $containerTypeMapping[$containerType];

        if(!array_key_exists($containerType, $this->componentMapping)) {
            $component = $this->getComponentsByClassQuery($cls)->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
            $component['componentFields'] = $component['fields'];
            unset($component['fields']);
            $this->componentMapping[$containerType] = $component;
            return $component;
        }
        return $this->componentMapping[$containerType];
    }

    /**
     * Helper Method to get access to the campagins repository.
     *
     * @return Shopware\Models\Newsletter\Repository
     */
    public function getCampaignsRepository() {
        if ($this->campaignsRepository === null) {
                 $this->campaignsRepository = Shopware()->Models()->getRepository('Shopware\Models\Newsletter\Newsletter');
             }

        return $this->campaignsRepository;
    }

    /**
     * Method to define acl dependencies in backend controllers
     * <code>
     * $this->addAclPermission("name_of_action_with_action_prefix","name_of_assigned_privilege","optionally error message");
     * // $this->addAclPermission("indexAction","read","Ops. You have no permission to view that...");
     * </code>
     */
    protected function initAcl()
    {
        $this->setAclResourceName('newsletter_manager');
        // read
        $this->addAclPermission('getPreviewNewsletters', 'read', 'Insufficient Permissions');
        $this->addAclPermission('listNewsletters', 'read', 'Insufficient Permissions');

        //write
        $this->addAclPermission('createNewsletter', 'write', 'Insufficient Permissions');

    }

    /**
     * Get all newsletters with status -1
     */
    function getPreviewNewslettersQuery() {
        $builder = Shopware()->Models()->createQueryBuilder();

        $builder->select(array(
            'mailing',
            'container',
            'text'))
                ->from('Shopware\Models\Newsletter\Newsletter', 'mailing')
                ->leftJoin('mailing.containers', 'container')
                ->leftJoin('container.text', 'text')
                ->where('mailing.status = -1');

        return $builder->getQuery();
    }

    /**
     * Get a list of existing newslettes
     */
    public function listNewslettersAction() {
        $filter = $this->Request()->getParam('filter', null);
        $sort = $this->Request()->getParam('sort', array(array('property' => 'mailing.date', 'direction' => 'DESC')));
        $limit = $this->Request()->getParam('limit', 10);
        $offset = $this->Request()->getParam('start', 0);

        // Delete old previews
        $results = $this->getPreviewNewslettersQuery()->getResult();
        foreach($results as $model) {
            Shopware()->Models()->remove($model);
        }
        Shopware()->Models()->flush();

        // Get the revenue for the newsletters
        $sql = "SELECT
                partnerID, COUNT(partnerID) as orders, ROUND(SUM((o.invoice_amount_net-o.invoice_shipping_net)/currencyFactor),2) AS `revenue`
            FROM
                `s_order` as o
            WHERE
                o.status != 4
            AND
                o.status != -1
            AND
                o.partnerID <> ''
            GROUP BY o.partnerID";
        $revenues = Shopware()->Db()->fetchAssoc($sql);


        //get newsletters
        $query = $this->getCampaignsRepository()->getListNewslettersQuery($filter, $sort, $limit, $offset);

        $query->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query);
        //returns the total count of the query
        $totalResult = $paginator->count();
        //returns the customer data
        $result = $paginator->getIterator()->getArrayCopy();

        // Get address count via plain sql in order to improve the speed
        $ids = array();
        foreach($result as $key => $newsletter) {
            $ids[] = $newsletter['id'];
            $newNewsletter = $this->convertContainersToElements($newsletter);
            $result[$key] = $newNewsletter;
        }
        $ids = implode(', ', $ids);

        $addresses = array();

        if($ids !== '') {
            $sql = "SELECT lastmailing, COUNT(lastmailing) as addressCount
            FROM `s_campaigns_mailaddresses`
            WHERE lastmailing
            IN ( $ids )";
            $addresses = Shopware()->Db()->fetchAssoc($sql);
        }

        // join newsletters and corrsponding revenues
        foreach($result as $key => $value){
            // Groups are stored serialized in the database.
            // Here they will be unserialized and flattened in order to match the ExJS RecipientGroup store
            $result[$key]['groups'] = $this->unserializeGroup($result[$key]['groups']);

            if(!isset($addresses[$value['id']])) {
                $result[$key]['addresses'] = 0;
            }else{
                $result[$key]['addresses'] = (int) $addresses[$value['id']]['addressCount'];
            }
            $revenue = $revenues['sCampaign'. $value['id']]['revenue'];
            $orders = $revenues['sCampaign'. $value['id']]['orders'];
            if($revenue !== null) {
                $result[$key]['revenue'] = $revenue;
                $result[$key]['orders'] = $orders;
            }

        }

        $this->View()->assign(array(
            'success' => true,
            'data' => $result,
            'total' => $totalResult,
        ));

    }

    /**
     * Update an existing newsletter model from passed data
     */
    public function updateNewsletterAction() {

        $id = $this->Request()->getParam('id', null);
        if($id === null) {
            $this->View()->assign(array('success' => false, 'message' => 'no id passed'));
            return;
        }

        $data = $this->Request()->getParams();
        if($data === null) {
            $this->View()->assign(array('success' => false, 'message' => 'no data passed'));
            return;
        }

        Shopware()->Models()->getConnection()->beginTransaction(); // suspend auto-commit
        try{

            // first of all get rid of the old containers and text fields
            $model= Shopware()->Models()->find('Shopware\Models\Newsletter\Newsletter', $id);
            if (!$model instanceof \Shopware\Models\Newsletter\Newsletter) {
                $this->View()->assign(array('success' => false, 'message' => 'newsletter not found'));
                return;
            }

            $containers = $model->getContainers();
            foreach($containers as $container){
                Shopware()->Models()->remove($container);
            }
            Shopware()->Models()->flush();


            //don't touch the date
            unset($data['date']);
            unset($data['locked']);
            $elements = $data['elements'];
            unset($data['elements']);
            $data['groups'] = $this->serializeGroup($data['groups']);

            $model= Shopware()->Models()->find('Shopware\Models\Newsletter\Newsletter', $id);

            if (!$model instanceof \Shopware\Models\Newsletter\Newsletter) {
                $this->View()->assign(array('success' => false, 'message' => 'newsletter not found'));
                return;
            }

            $model->fromArray($data);
            Shopware()->Models()->persist($model);

            $this->createContent($model, $elements);

            Shopware()->Models()->flush();

            Shopware()->Models()->getConnection()->commit();
            Shopware()->Models()->clear();
        }catch(\Exception $e){
            Shopware()->Models()->getConnection()->rollback();
            Shopware()->Models()->close();
            $this->View()->assign(array('success' => false, 'data' => $e->getMessage()));
            return;
        }

        $this->View()->assign(array('success' => true, 'data' => $model->toArray));


    }

    /**
     * Get available vouchers
     * */
    public function getVoucherAction() {

        $sql = "SELECT s_emarketing_vouchers.id, s_emarketing_vouchers.description, s_emarketing_vouchers.value, s_emarketing_vouchers.numberofunits, IF(s_emarketing_vouchers.percental = 1, '%', '€') as type_sign
            FROM s_emarketing_vouchers
            WHERE  s_emarketing_vouchers.modus = 1 AND (s_emarketing_vouchers.valid_to >= now() OR s_emarketing_vouchers.valid_to is NULL)
            AND (s_emarketing_vouchers.valid_from <= now() OR s_emarketing_vouchers.valid_from is NULL)
            AND (
                SELECT s_emarketing_voucher_codes.id
                FROM s_emarketing_voucher_codes
                WHERE s_emarketing_voucher_codes.voucherID = s_emarketing_vouchers.id
                AND s_emarketing_voucher_codes.userID is NULL
                AND s_emarketing_voucher_codes.cashed = 0
                LIMIT 1
            )";

        $data = Shopware()->Db()->fetchAll($sql);

        $this->View()->assign(array(
            'success' => true,
            'data' => $data,
            'total' => count($data),
        ));

    }

    /**
     * Helper function which will convert a given list of campaign-containers to emotion-style-elements
     * @param $newsletter
     * @return $elements
     */
    private function convertContainersToElements($newsletter) {
        $elements = array();
        foreach($newsletter['containers'] as $container) {
            $element = array(
                'id' => $container['id'],
                'startRow' => $container['position'],
                'endRow' => $container['position'],
                'startCol' => 1,
                'endCol' => 1,
                'newsletterId' => $newsletter['id'],
                'data' => array()
            );
            $component = $this->getComponentByContainerType($container['type']);
            switch($container['type']) {
                case 'ctArticles';
                    $headline = array( 'key' => 'headline', 'value' => $container['description'] );
                    $articleData = array();
                    foreach($container['articles'] as $article) {
                        $articleData[] = array('name' => $article['name'], 'ordernumber' => $article['number'], 'position' => $article['position'], 'type' => $article['type']);
                    }
                    $element['data'] = array($headline, array('key' => 'article_data', 'type' => 'json', 'value' => $articleData));
                    break;
                case 'ctLinks';
                    $headline = array( 'key' => 'description', 'value' => $container['description'] );
                    $linkData = array();
                    foreach($container['links'] as $link) {
                        $linkData[] = array('description' => $link['description'], 'link' => $link['link'], 'target' => $link['target'], 'position' => $link['position']);
                    }
                    $element['data'] = array($headline, array('key' => 'link_data', 'type' => 'json', 'value' => $linkData));
                    break;
                case 'ctBanner';
                    $description = array(
                        'key' => 'description',
                        'value' => $container['description']
                    );
                    $file = array(
                        'key' => 'file',
                        'value' => $container['banner']['image']
                    );
                    $link = array(
                        'key' => 'link',
                        'value' => $container['banner']['link']
                    );
                    $target_selection = array(
                        'key' => 'target_selection',
                        'value' => $container['banner']['target']
                    );
                    $element['data'] = array($description, $file, $link, $target_selection);
                    break;
                case 'ctSuggest';
                     $headline = array(
                         'key' => 'headline',
                         'value' => $container['description']
                     );
                     $number = array(
                         'key' => 'number',
                         'value' => $container['value']
                     );
                     $element['data'] = array($headline, $number);
                     break;
                case 'ctVoucher';
                case 'ctText';
                    $description = array(
                        'key' => 'headline',
                        'value' => $container['description']
                    );
                    $text = array(
                        'key' => 'text',
                        'value' => $container['text']['content']
                    );
                    $image = array(
                        'key' => 'image',
                        'value' => $container['text']['image']
                    );
                    $url = array(
                        'key' => 'url',
                        'value' => $container['text']['link']
                    );
                    $voucher_selection = array(
                        'key' => 'voucher_selection',
                        'value' => (int) $container['value']
                    );

                    // if container's value is set, the corresponding container type is ctVoucher
                    if($container['type'] === 'ctVoucher') { //isset($container['value']) && !empty($container['value'])) {
                        $component = $this->getComponentByContainerType('ctVoucher');

                        $element['data'] = array($description, $text, $image, $url, $voucher_selection);
                    }else{
                        $element['data'] = array($description, $text, $image, $url);
                    }
                    break;

            }

            $element['component'] = array($component);
            $element['componentId'] = $component['id'];

            $elements[] = $element;
        }

        unset($newsletter['containers']);
        $newsletter['elements'] = $elements;

        return $newsletter;
    }

    /**
     * Helper function to convert the emotion-style element/table structure to the old newsletter structure
     * @param $model
     * @param $elements
     * @throws Exception
     */
    private function createContent($model, $elements) {
        $articleDetailRepository = Shopware()->Models()->getRepository('Shopware\Models\Article\Detail');

        foreach($elements as $elementKey => $element) {
            $component = $element['component'][0];
            $position = $element['startRow'];
            $data = $element['data'];

            $container = new Shopware\Models\Newsletter\Container();
            $container->setNewsletter($model);
            $container->setPosition($position);
            $container->setValue('');
            Shopware()->Models()->persist($container);

            switch($component['cls']) {
                // voucher and text elements are basically the same. The voucher-code for voucher-elements
                // is stored in the value property of the parent container
                case 'newsletter-voucher-element':
                case 'newsletter-html-text-element':
                    $text = new \Shopware\Models\Newsletter\ContainerType\Text();
                    $text->setAlignment('left');
                    $text->setContainer($container);
                    foreach($data as $dateKey => $datum) {
                        switch($datum['key']) {
                            case 'headline':
                                $container->setDescription($datum['value']);
                                $text->setHeadline($datum['value']);
                                break;
                            case 'text':
                                $text->setContent($datum['value']);
                                break;
                            case 'image':
                                $datum['value'] = $this->assureAbsolutePath($datum['value']);
                                $text->setImage($datum['value']);
                                break;
                            case 'url':
                                $text->setLink($datum['value']);
                                break;
                            case 'voucher_selection':
                                $container->setValue($datum['value']);
                                $container->setType('ctVoucher');
                                break;
                        }
                    }
                    Shopware()->Models()->persist($text);
                    break;
                case 'newsletter-banner-element':
                    $banner = new \Shopware\Models\Newsletter\ContainerType\Banner();
                    $banner->setContainer($container);
                    foreach($data as $dateKey => $datum) {
                        switch($datum['key']) {
                            case 'description':
                                $container->setDescription($datum['value']);
                                $banner->setDescription($datum['value']);
                                break;
                            case 'file':
                                $datum['value'] = $this->assureAbsolutePath($datum['value']);
                                $banner->setImage($datum['value']);
                                break;
                            case 'link':
                                $banner->setLink($datum['value']);
                                break;
                            case 'target_selection':
                                $banner->setTarget($datum['value']);
                                break;
                        }
                    }
                    Shopware()->Models()->persist($banner);
                    break;
                // Articles and links differ from other containers: Each article/link container can have
                // multiple children
                case 'newsletter-article-element':
                    if(count($data) === 0) {
                        throw new \Exception("No articles set for the article element");
                    }
                    foreach($data as $dateKey => $datum) {
                        switch($datum['key']) {
                            case 'article_data':
                                foreach($datum['value'] as $articleKey => $article){
                                    switch($article['type']) {
                                        case 'fix':
                                            $articleDetail = $articleDetailRepository->findOneBy(array('number' => $article['ordernumber']));
                                            if($articleDetail === null) {
                                                throw new \Exception("Article by ordernumber '{$article['ordernumber']}' not found");
                                            }
                                            $article['articleDetail'] = $articleDetail;
                                            break;
                                        case 'random':
                                            $article['name'] = 'Zufall';
                                            break;
                                        case 'top':
                                            $article['name'] = 'Topseller';
                                            break;
                                        case 'new':
                                            $article['name'] = 'Neuheit';
                                            break;
                                    }
                                    $articleModel = new \Shopware\Models\Newsletter\ContainerType\Article();
                                    $articleModel->fromArray($article);
                                    $articleModel->setContainer($container);
                                    Shopware()->Models()->persist($articleModel);
                                }
                                break;
                            case 'headline':
                                $container->setDescription($datum['value']);
                                break;
                        }
                    }
                    break;
                case 'newsletter-link-element':
                    foreach($data as $dateKey => $datum) {
                        if(count($data) === 0) {
                            throw new \Exception("No links set for the link element");
                        }
                        switch($datum['key']) {
                            case 'link_data':
                                foreach($datum['value'] as $linkKey => $link){
                                    $linkModel = new \Shopware\Models\Newsletter\ContainerType\Link();
                                    $linkModel->fromArray($link);
                                    $linkModel->setContainer($container);
                                    Shopware()->Models()->persist($linkModel);
                                }
                                break;
                            case 'description':
                                $container->setDescription($datum['value']);
                                break;
                        }
                    }
                    break;
                case 'newsletter-suggest-element':
                    foreach($data as $dateKey => $datum) {
                        switch($datum['key']) {
                            case 'number':
                                $container->setValue($datum['value']);
                                break;
                            case 'headline':
                                $container->setDescription($datum['value']);
                                break;
                        }
                    }
                    $container->setType('ctSuggest');
                    break;

            }
        }
    }

    /**
     * Helper function to make sure that image paths are stored as absolute paths
     * @param $path
     * @return string
     */
    private function assureAbsolutePath($path) {
        return $path;
//        if(strpos($path, 'http') !== 0) {
//            $shop = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop')->getActiveDefault();
//            $shop->registerResources(Shopware()->Bootstrap());
//            return 'http://'. $shop->getHost() . $shop->getBasePath()  . $path;
//        }
//
//        return $path;
    }

    /**
     * Create a new newsletter model from passed data
     */
    public function createNewsletterAction() {

        $data = $this->Request()->getParams();

        if($data === null) {
            $this->View()->assign(array('success' => false, 'message' => 'no data passed'));
            return;
        }


        Shopware()->Models()->getConnection()->beginTransaction(); // suspend auto-commit
        try{
            $elements = $data['elements'];
            unset($data['elements']);

            $data['groups'] = $this->serializeGroup($data['groups']);
            $data['date'] = new \DateTime();

            $model = new \Shopware\Models\Newsletter\Newsletter();
            $model->fromArray($data);
            Shopware()->Models()->persist($model);

            $this->createContent($model, $elements);

            Shopware()->Models()->flush();


            Shopware()->Models()->getConnection()->commit();
            Shopware()->Models()->clear();
        }catch(\Exception $e){
            Shopware()->Models()->getConnection()->rollback();
            Shopware()->Models()->close();
            $this->View()->assign(array('success' => false, 'data' => $e->getMessage()));
            return;
        }



        $data = array(
            'id' => $model->getId()
        );

        $this->View()->assign(array('success' => true, 'data' => $data));

    }

    /**
     * Little helper function to get a component by its cls.
     * @param $cls
     * @return Doctrine\ORM\Query
     */
    private function getComponentsByClassQuery($cls) {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('components', 'fields'))
                ->from('Shopware\CustomModels\SwagNewsletter\Component', 'components')
                ->leftJoin('components.fields', 'fields')
                ->where('components.cls = ?1')
                ->setParameter(1, $cls);

        return $builder->getQuery();
    }

    /**
     * Helper function to get a component list
     * @return Doctrine\ORM\Query
     */
    private function getComponentsQuery() {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('components', 'fields'))
                ->from('Shopware\CustomModels\SwagNewsletter\Component', 'components')
                ->leftJoin('components.fields', 'fields');

        return $builder->getQuery();
    }

    /**
     * Lists orders which are related to a newsletter
     */
    public function orderAction() {

        $filter = $this->Request()->getParam('filter', null);
        $sort = $this->Request()->getParam('sort', null);
        $limit = (int) $this->Request()->getParam('limit', 100);
        $offset = (int) $this->Request()->getParam('start', 0);

        $params = array();

        // Escape and prepare params for the sql query
        if(is_array($filter) && isset($filter[0]['value'])) {
            $params['filter'] = '%' . $filter[0]['value'] . '%';
            $params['value'] = 'sCampaign'.$filter[0]['value'];
            $filter = 'AND (m.subject LIKE :filter OR o.partnerID = :value OR ub.firstname LIKE :filter OR ub.lastname LIKE :filter)';
         }else{
            $filter = '';
        }

        if($sort !== null && isset($sort[1]['property'])) {
            if (isset($sort['1']['direction']) && $sort['1']['direction'] === 'DESC') {
                $direction = 'DESC';
            } else {
                $direction = 'ASC';
            }
            switch($sort[1]['property']){
                case 'orderTime':
                    $sort = 'o.ordertime';
                    break;
                case 'newsletterDate':
                    $sort = 'm.datum';
                    break;
                case 'subject':
                    $sort = 'm.subject';
                    break;
                case 'customer':
                    $sort = 'customer';
                    break;
                case 'invoiceAmountEuro':
                    $sort = 'invoiceAmount';
                    break;
                default:
                    $sort = 'm.datum';
                    $direction = 'DESC';
                    break;
            }

            $sort = "ORDER BY $sort $direction";
        }else{
            $sort = 'ORDER BY m.datum DESC';
        }


        // Get orders
        $sql = "
        SELECT o.id as id, m.id as partnerId, m.subject, m.id as newsletterId, m.datum as newsletterDate, CONCAT(ub.lastname, ', ', ub.firstname) as customer, ub.userId as customerId, o.id as orderId, o.invoice_amount as invoiceAmount, o.currencyFactor, subshopID as shopId, o.status, o.cleared, o.ordertime as orderTime

        FROM s_campaigns_mailings m

        LEFT JOIN s_order o ON o.partnerID = CONCAT('sCampaign', m.id)
        LEFT JOIN s_user_billingaddress ub ON ub.userID = o.userID
        WHERE o.status > -1 $filter
        $sort
        LIMIT $offset,$limit
        ";

        $results = Shopware()->Db()->fetchAll($sql, $params);

        $this->View()->assign(array(
            'success' => true,
            'data' => $results
        ));


    }

    /**
     * Event listener function of the library store.
     * @return array
     */
    public function libraryAction()
    {

        $components = $this->getComponentsQuery()->getArrayResult();

        foreach($components as &$component) {
            $component['componentFields'] = $component['fields'];
            unset($component['fields']);
        }

        $this->View()->assign(array(
            'success' => true,
            'data' => $components
        ));
    }

    /**
     * Little helper function, that puts the array in the form found in the database originally and serializes it
     * @param $groups
     * @return string
     */
    private function serializeGroup($groups) {
        $newGroup = array(array(), array());

        foreach($groups as $key => $values) {
            if($values['isCustomerGroup'] === true){
                array_push($newGroup[0][$values['groupkey']], $values['number']);
            }else{
                array_push($newGroup[1][$values['internalId']], $values['number']);
            }
        }

        return serialize($newGroup);
    }

    /**
     * Helper function which takes a serializes group string from the databse and puts it in a flattened form
     * @param $group
     * @return array
     */
    private function unserializeGroup($group) {
        $groups = unserialize($group);

        $flattenedGroup = array();
        foreach($groups as $group => $item) {
            foreach($item as $id => $number){
                $groupKey = ($group === 0) ? $id : false;
                $isCustomerGroup = ($group === 0) ? true : false;

                $flattenedGroup[] = array(
                    'internalId' => ($group === 0) ? null : $id,
                    'number' => $number,
                    'name' => '',
                    'groupkey' => $groupKey,
                    'isCustomerGroup' => $isCustomerGroup
                );
            }
        }

        return $flattenedGroup;
    }

}

