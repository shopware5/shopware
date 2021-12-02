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

use Shopware\Models\Payment\RuleSet;

/**
 * This controller handles all actions made by the user in the premium module.
 * It reads all premium-articles, creates new ones, edits and deletes them.
 * Additionally it also validates the form.
 */
class Shopware_Controllers_Backend_RiskManagement extends Shopware_Controllers_Backend_ExtJs
{
    public function initAcl()
    {
        $this->addAclPermission('getPayments', 'read', "You're not allowed to see the rules.");
        $this->addAclPermission('createRule', 'save', "You're not allowed to save a rule.");
        $this->addAclPermission('editRule', 'save', "You're not allowed to save a rule.");
        $this->addAclPermission('deleteRule', 'delete', "You're not allowed to delete a rule.");
    }

    /**
     * Disable template engine for all actions
     */
    public function preDispatch()
    {
        if (!\in_array($this->Request()->getActionName(), ['index', 'load'])) {
            $this->Front()->Plugins()->Json()->setRenderer(true);
        }
    }

    /**
     * Function to get all active payment-means and the ruleSets
     */
    public function getPaymentsAction()
    {
        try {
            $builder = $this->get('models')->createQueryBuilder();
            $builder->select(['payment', 'ruleSets'])
                    ->from('Shopware\Models\Payment\Payment', 'payment');
            $builder->leftJoin('payment.ruleSets', 'ruleSets');
            $builder->orderBy('payment.active', 'desc');
            $builder->addOrderBy('payment.id');

            $result = $builder->getQuery()->getArrayResult();
            $total = $this->get('models')->getQueryCount($builder->getQuery());

            // Translate the payment methods
            $translationComponent = $this->get(Shopware_Components_Translation::class);
            $result = $translationComponent->translatePaymentMethods($result);

            $this->View()->assign(['success' => true, 'data' => $result, 'total' => $total]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'errorMsg' => $e->getMessage()]);
        }
    }

    /**
     * Function to delete a single rule.
     * It is called, when the user clicks on the delete button of a rule.
     */
    public function deleteRuleAction()
    {
        try {
            $params = $this->Request()->getParams();

            $ruleModel = $this->get('models')->find(RuleSet::class, $params['id']);

            $this->get('models')->remove($ruleModel);
            $this->get('models')->flush();

            $this->View()->assign(['success' => true, 'data' => $params]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'errorMsg' => $e->getMessage()]);
        }
    }

    /**
     * Function to edit existing ruleSets.
     * It is called, when the user presses the save-button and he edited at least one ruleSet.
     *
     * It works with both simple arrays and 2-dimensional arrays.
     */
    public function editRuleAction()
    {
        try {
            $params = $this->Request()->getParams();
            unset($params['module']);
            unset($params['controller']);
            unset($params['action']);
            unset($params['_dc']);

            //2-dimensional array
            if ($params[0]) {
                $data = [];
                foreach ($params as $values) {
                    $ruleModel = $this->get('models')->find(RuleSet::class, $values['id']);

                    $ruleModel->fromArray($values);

                    $this->get('models')->persist($ruleModel);
                    $this->get('models')->flush();
                    $data[] = $this->get('models')->toArray($ruleModel);
                }
                $this->View()->assign(['success' => true, 'data' => $data]);
            } else {
                $ruleModel = $this->get('models')->find(RuleSet::class, $params['id']);

                $ruleModel->fromArray($params);

                $this->get('models')->persist($ruleModel);
                $this->get('models')->flush();

                $data = $this->get('models')->toArray($ruleModel);

                $this->View()->assign(['success' => true, 'data' => $data]);
            }
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'errorMsg' => $e->getMessage()]);
        }
    }

    /**
     * Function to create a new ruleSet.
     * It is called when the user presses the save-button and at least one rule is new.
     */
    public function createRuleAction()
    {
        try {
            $params = $this->Request()->getParams();

            $ruleModel = new RuleSet();
            $ruleModel->fromArray($params);

            $this->get('models')->persist($ruleModel);
            $this->get('models')->flush();

            $this->View()->assign(['success' => true, 'data' => $this->get('models')->toArray($ruleModel)]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'errorMsg' => $e->getMessage()]);
        }
    }
}
