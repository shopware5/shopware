<?php
/**
 * Enlight
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://enlight.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Enlight
 * @package    Enlight_Hook
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

/**
 * The Enlight_Hook_ProxyFactory is the factory for the class proxies.
 *
 * If a class is hooked, a proxy will be generated for this class.
 * The generated class extends the origin class and implements the Enlight_Hook interface.
 * Instead of the origin methods, the registered hook handler methods are executed.
 *
 * @category   Enlight
 * @package    Enlight_Hook
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Hook_ProxyFactory extends Enlight_Class
{
    /**
     * @var Enlight_Hook_HookManager
     */
    protected $hookManager;

    /**
     * @var null|string namespace of the proxy
     */
    protected $proxyNamespace;

    /**
     * @var string directory of the proxy
     */
    protected $proxyDir;

    /**
     * @var string extension of the hook files.
     */
    protected $fileExtension = '.php';

    /**
     * @var string Default proxy class template.
     */
    protected $proxyClassTemplate =
        '<?php
class <namespace>_<proxyClassName> extends <className> implements Enlight_Hook_Proxy
{
    public function executeParent($method, $args = array())
    {
        return call_user_func_array(array($this, \'parent::\' . $method), $args);
    }

    public static function getHookMethods()
    {
        return <arrayHookMethods>;
    }
    <methods>
}
';
    /**
     * @var string Default proxy method template
     */
    protected $proxyMethodTemplate =
        '
    <methodModifiers> function <methodName>(<methodParameters>)
    {
        return Shopware()->Hooks()->executeHooks(
            $this, \'<methodName>\', array(<arrayMethodParameters>)
        );
    }
';

    /**
     * Standard Constructor method.
     * If no namespace is given, the default namespace _Proxies is used.
     * If no proxy directory is given, the default directory Proxies is used.
     *
     * @param  Enlight_Hook_HookManager $hookManager
     * @param  string                   $proxyNamespace
     * @param  string                   $proxyDir
     * @throws RuntimeException
     */
    public function __construct($hookManager, $proxyNamespace, $proxyDir)
    {
        $this->hookManager = $hookManager;
        $this->proxyNamespace = $proxyNamespace;

        if (!is_dir($proxyDir)) {
            if (false === @mkdir($proxyDir, 0777, true) && !is_dir($proxyDir)) {
                throw new \RuntimeException(sprintf("Unable to create the %s directory (%s)\n", "Proxy", $proxyDir));
            }
        } elseif (!is_writable($proxyDir)) {
            throw new \RuntimeException(sprintf("Unable to write in the %s directory (%s)\n", "Proxy", $proxyDir));
        }

        $proxyDir = rtrim(Enlight_Loader::realpath($proxyDir), '\\/') . DIRECTORY_SEPARATOR;

        $this->proxyDir = $proxyDir;
    }

    /**
     * Returns the proxy of the given class. If the proxy is not already created
     * it is generated and written.
     * If the proxy is already created it is drawn by the
     * Shopware()->Hooks()->getHooks($class) method.
     *
     * @param  string    $class
     * @throws Exception
     * @return string
     */
    public function getProxy($class)
    {
        $proxyFile = $this->getProxyFileName($class);
        $proxy = $this->getProxyClassName($class);

        if (!is_readable($proxyFile)) {
            if (!is_writable($this->proxyDir)) {
                throw new \Exception(sprintf('The directory "%s" is not writable.', $this->proxyDir));
            }
            $content = $this->generateProxyClass($class);
            $this->writeProxyClass($proxyFile, $content);
        } elseif (!method_exists($proxy, 'executeParent')) {
            @unlink($proxyFile);
        }

        return $proxy;
    }

    /**
     * Returns proxy class name
     *
     * @param  string $class
     * @return string
     */
    public function getProxyClassName($class)
    {
        return $this->proxyNamespace . '_' . $this->formatClassName($class);
    }

    /**
     * Formats the given class name.
     *
     * @param  string $class
     * @return string
     */
    public function formatClassName($class)
    {
        return str_replace(array('_', '\\'), '', $class) . 'Proxy';
    }

    /**
     * Returns proxy file name for the given class.
     *
     * @param  string $class
     * @return string
     */
    public function getProxyFileName($class)
    {
        $proxyClassName = $this->formatClassName($class);

        return $this->proxyDir . $proxyClassName . $this->fileExtension;
    }

    /**
     * This function creates the proxy class for the given class name.
     * The proxy class extends the original class and implements the Enlight_Hook_Proxy.
     *
     * @param  string       $class
     * @return mixed|string
     */
    protected function generateProxyClass($class)
    {
        $methods = $this->generateMethods($class);
        $proxyClassName = $this->formatClassName($class);

        $search = array(
            '<namespace>',
            '<proxyClassName>',
            '<className>',
            '<methods>',
            '<arrayHookMethods>'
        );
        $replace = array(
            $this->proxyNamespace,
            $proxyClassName,
            $class,
            $methods['methods'],
            str_replace("\n", '', var_export($methods['array'], true))
        );

        $file = $this->proxyClassTemplate;
        $file = str_replace($search, $replace, $file);

        return $file;
    }

    /**
     * This function writes the generated proxy class to the file system.
     *
     * @param  string            $fileName
     * @param  string            $content
     * @throws Enlight_Exception
     */
    protected function writeProxyClass($fileName, $content)
    {
        $tmpFile = tempnam(dirname($fileName), basename($fileName));
        if (false !== @file_put_contents($tmpFile, $content) && @rename($tmpFile, $fileName)) {
            @chmod($fileName, 0666 & ~umask());

            return;
        }

        throw new Enlight_Exception('Unable to write file "' . $fileName . '"');
    }

    /**
     * Generate the class source code for the hooked method of the given class.
     * First all methods of the class are iterated.
     * Final, static, magic and private methods can't be hooked.
     * If the method is hooked, enlight iterates all method parameters to generate
     * the parameter definition. At last all hooked methods are implemented by the
     * $proxyMethodTemplate.
     *
     * @param  string $class
     * @return array
     */
    protected function generateMethods($class)
    {
        $rc = new ReflectionClass($class);
        $methodsArray = array();
        $methods = '';

        //iterate all class methods
        foreach ($rc->getMethods() as $rm) {

            //final, static and private methods can't be hooked.
            if ($rm->isFinal() || $rm->isStatic() || $rm->isPrivate()) {
                continue;
            }
            if (substr($rm->getName(), 0, 2) == '__') {
                continue;
            }
            //checks if the current method hooks exists.
            if (!$this->hookManager->hasHooks($class, $rm->getName())) {
                continue;
            }

            //adds the hooked method to the array.
            $methodsArray[] = $rm->getName();
            $params = '';
            $proxy_params = '';
            $array_params = '';

            //iterates all parameters to generate the parameter definition.
            foreach ($rm->getParameters() as $rp) {
                if ($params) {
                    $params .= ', ';
                    $proxy_params .= ', ';
                    $array_params .= ', ';
                }

                $array_param = '';
                if ($rp->isPassedByReference()) {
                    $params .= '&';
                    $proxy_params .= '$';
                    $array_param .= '&';
                }
                $params .= '$' . $rp->getName();
                $proxy_params .= '$' . $rp->getName();
                $array_param .= '$' . $rp->getName();
                $array_params .= '\'' . $rp->getName() . '\'=>' . $array_param;
                if ($rp->isOptional() && $rp->isDefaultValueAvailable()) {
                    $params .= ' = ' . str_replace("\n", '', var_export($rp->getDefaultValue(), true));
                }
            }
            $modifiers = Reflection::getModifierNames($rm->getModifiers());
            $modifiers = implode(' ', $modifiers);
            $search = array(
                '<methodName>',
                '<methodModifiers>',
                '<methodParameters>',
                '<proxyMethodParameters>',
                '<arrayMethodParameters>',
                '<className>'
            );
            $replace = array($rm->getName(), $modifiers, $params, $proxy_params, $array_params, $class);
            $method = $this->proxyMethodTemplate;
            $method = str_replace($search, $replace, $method);
            $methods .= $method;
        }

        return array('array' => $methodsArray, 'methods' => $methods);
    }

    /**
     * Clear proxy cache
     */
    public function clearCache()
    {
        $proxies = new GlobIterator(
            $this->proxyDir . '*Proxy.php',
            FilesystemIterator::CURRENT_AS_PATHNAME
        );

        foreach ($proxies as $proxyPath) {
            @unlink($proxyPath);
        }
    }

    /**
     * @return string
     */
    public function getProxyDir()
    {
        return $this->proxyDir;
    }
}
