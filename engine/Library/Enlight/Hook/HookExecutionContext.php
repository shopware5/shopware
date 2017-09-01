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

/**
 * The Enlight_Hook_HookExecutionContext represents a single execution of a hook.
 *
 * In order to execute a proxy method and all its 'before', 'replace' and 'after' hooks, a new context must be created
 * and executed.
 *
 * @category   Enlight
 *
 * @copyright  Copyright (c) 2017, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Hook_HookExecutionContext
{
    /**
     * @var Enlight_Hook_HookManager
     */
    protected $hookManager;

    /**
     * @var Enlight_Hook_HookArgs
     */
    protected $args;

    /**
     * @var int
     */
    protected $parentExecutionLevel = 0;

    /**
     * @param Enlight_Hook_HookManager $hookManager
     * @param Enlight_Hook_Proxy       $class
     * @param string                   $method
     * @param array                    $args
     */
    public function __construct(
        Enlight_Hook_HookManager $hookManager,
        Enlight_Hook_Proxy $class,
        $method,
        array $args
    ) {
        $this->hookManager = $hookManager;
        $this->args = new Enlight_Hook_HookArgs(array_merge(
            [
                'class' => $class,
                'method' => $method,
            ],
            $args
        ));
    }

    /**
     * @param string $className
     * @param string $method
     * @param string $hookType
     *
     * @return string
     */
    public static function createHookEventName($className, $method, $hookType)
    {
        return $className . '::' . $method . '::' . $hookType;
    }

    /*+
     * @return Enlight_Hook_HookManager
     */
    public function getHookManager()
    {
        return $this->hookManager;
    }

    /**
     * Executes this context by calling the  'before' hooks, 'replace' hooks and 'after' hooks in that order and
     * returning the args' return value.
     *
     * @return mixed
     */
    public function execute()
    {
        // Save this context in the proxy
        $proxy = $this->args->getSubject();
        $proxy->pushHookExecutionContext($this->args->getMethod(), $this);

        // Before hooks
        $this->hookManager->getEventManager()->notify(
            $this->getHookEventName(Enlight_Hook_HookHandler::TypeBefore),
            $this->args
        );

        // Replace hooks and/or original method
        $this->args->setProcessed(false);
        $returnValue = $this->executeReplaceChain($this->args->getArgs());
        $this->args->setReturn($returnValue);
        $this->args->setProcessed(true);

        // After hooks
        $returnValue = $this->hookManager->getEventManager()->filter(
            $this->getHookEventName(Enlight_Hook_HookHandler::TypeAfter),
            $this->args->getReturn(),
            $this->args
        );

        // Remove this context from the proxy
        $proxy->popHookExecutionContext($this->args->getMethod());

        return $returnValue;
    }

    /**
     * Checks the event manager for any replace hooks on the 'replace' event of this context's method and, if found,
     * executes the listener corresponding to the current 'parentExecutionLevel'. If no listeners exist of the end of
     * the replace chain is reached (i.e. all levels have been executed), the original method is called. Finally the
     * respective return value of the listener or original method is returned.
     *
     * @param array $args
     *
     * @return mixed
     */
    public function executeReplaceChain(array $args = [])
    {
        // Check for 'replace' hooks
        $replaceEventName = $this->getHookEventName(Enlight_Hook_HookHandler::TypeReplace);
        $listeners = $this->hookManager->getEventManager()->getListeners($replaceEventName);
        if (count($listeners) === 0 || $this->parentExecutionLevel >= count($listeners)) {
            // No 'replace' listeners ot reached the end of the execution chain, hence call the original method
            $returnValue = call_user_func_array(
                [
                    $this->args->getSubject(),
                    ('parent::' . $this->args->getMethod()),
                ],
                $args
            );
            $this->args->setReturn($returnValue);

            return $returnValue;
        }

        // Execute the current level of the chain. We increase the level before executing the listener, to allow
        // recursive calls of this method to execute the next listeners in the cain. Finally, we reduce the level
        // again, to allow repeated calls of 'executeParent()' in the same listener to call the whole chain again.
        $currentLevel = $this->parentExecutionLevel;
        ++$this->parentExecutionLevel;
        $listeners[$currentLevel]->execute($this->args);
        --$this->parentExecutionLevel;

        return $this->args->getReturn();
    }

    /**
     * @param string $hookType
     *
     * @return string
     */
    protected function getHookEventName($hookType)
    {
        $originalClassName = get_parent_class($this->args->getSubject());

        return self::createHookEventName(
            ($this->hookManager->getAlias($originalClassName)) ?: $originalClassName,
            $this->args->getMethod(),
            $hookType
        );
    }
}
