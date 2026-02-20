<?php
/**
 * Created by JetBrains PhpStorm.
 * User: stas
 * Date: 27.11.12
 * Time: 13:18
 * To change this template use File | Settings | File Templates.
 */

namespace ExprAs\Core\Cache;

use ExprAs\Core\Module\Bootstrapper\AbstractBootstrapper;
use Laminas\Code\Reflection\ClassReflection;
use Laminas\Code\Scanner\FileScanner;
use Laminas\ModuleManager\ModuleManager;
use Laminas\Mvc\MvcEvent;

class LaminaslassCacher extends AbstractBootstrapper
{
    protected $knownClasses = [];

    protected $_cacheFilePath = 'data/cache/zf_class_cache';
    /**
     * Attach events
     *
     * @return void
     */
    public function init(ModuleManager $mm)
    {
        $events = $mm->getEventManager()->getSharedManager();
        $events->attach(\Laminas\Mvc\Application::class, 'finish', $this->cache(...));

        if (defined('ZF_CLASS_CACHE')) {
            $this->_cacheFilePath = ZF_CLASS_CACHE;
        }
    }

    /**
     * Cache declared interfaces and classes to a single file
     *
     * @param  \Laminas\Mvc\MvcEvent $e
     * @return void
     */
    public function cache($e)
    {
        if ($e->getRequest()->getQuery()->get('__AS_ZF_CLASS_CACHE__', null) === null) {
            return;
        }

        if (file_exists($this->_cacheFilePath)) {
            $this->reflectClassCache();
            $code = file_get_contents($this->_cacheFilePath);
        } else {
            $code = "<?php\n";
        }

        $classes = [...get_declared_interfaces(), ...get_declared_classes()];
        foreach ($classes as $class) {
            // Skip non-Zend classes
            if (!str_starts_with($class, 'Zend')) {
                continue;
            }

            // Skip the autoloader factory and this class
            if (in_array($class, [\Laminas\Loader\AutoloaderFactory::class, self::class])) {
                continue;
            }

            if ($class === \Laminas\Loader\SplAutoloader::class) {
                continue;
            }

            // Skip any classes we already know about
            if (in_array($class, $this->knownClasses)) {
                continue;
            }

            $class = new ClassReflection($class);

            // Skip ZF2-based autoloaders
            if (in_array(\Laminas\Loader\SplAutoloader::class, $class->getInterfaceNames())) {
                continue;
            }

            // Skip internal classes or classes from extensions
            // (this shouldn't happen, as we're only caching Zend classes)
            if ($class->isInternal()
                || $class->getExtensionName()
            ) {
                continue;
            }

            $code .= static::getCacheCode($class);
        }

        file_put_contents($this->_cacheFilePath, $code);
        // minify the file
        file_put_contents($this->_cacheFilePath, php_strip_whitespace($this->_cacheFilePath));
    }

    /**
     * Generate code to cache from class reflection.
     *
     * This is a total mess, I know. Just wanted to flesh out the logic.
     *
     * @todo   Refactor into a class, clean up logic, DRY it up, maybe move
     * some of this into Laminas\Code
     * @return string
     */
    protected static function getCacheCode(ClassReflection $r)
    {
        $useString = '';
        $usesNames = [];
        if (is_countable($uses = $r->getDeclaringFile()->getUses()) ? count($uses = $r->getDeclaringFile()->getUses()) : 0) {
            foreach ($uses as $use) {
                $usesNames[$use['use']] = $use['as'];

                $useString .= "use {$use['use']}";

                if ($use['as']) {
                    $useString .= " as {$use['as']}";
                }

                $useString .= ";\n";
            }
        }

        $declaration = '';

        if ($r->isAbstract() && !$r->isInterface()) {
            $declaration .= 'abstract ';
        }

        if ($r->isFinal()) {
            $declaration .= 'final ';
        }

        if ($r->isInterface()) {
            $declaration .= 'interface ';
        }

        if (!$r->isInterface()) {
            $declaration .= 'class ';
        }

        $declaration .= $r->getShortName();

        $parentName = false;
        if (($parent = $r->getParentClass()) && $r->getNamespaceName()) {
            $parentName = array_key_exists($parent->getName(), $usesNames)
                ? ($usesNames[$parent->getName()] ?: $parent->getShortName())
                : ((str_starts_with($parent->getName(), $r->getNamespaceName()))
                    ? substr($parent->getName(), strlen($r->getNamespaceName()) + 1)
                    : '\\' . $parent->getName());
        } elseif ($parent && !$r->getNamespaceName()) {
            $parentName = '\\' . $parent->getName();
        }

        if ($parentName) {
            $declaration .= " extends {$parentName}";
        }

        $interfaces = array_diff($r->getInterfaceNames(), $parent ? $parent->getInterfaceNames() : []);
        if (count($interfaces)) {
            foreach ($interfaces as $interface) {
                $iReflection = new ClassReflection($interface);
                $interfaces = array_diff($interfaces, $iReflection->getInterfaceNames());
            }
            $declaration .= $r->isInterface() ? ' extends ' : ' implements ';
            $declaration .= implode(
                ', ', array_map(
                    function ($interface) use ($usesNames, $r) {
                        $iReflection = new ClassReflection($interface);
                        return (array_key_exists($iReflection->getName(), $usesNames)
                        ? ($usesNames[$iReflection->getName()] ?: $iReflection->getShortName())
                        : ((str_starts_with($iReflection->getName(), $r->getNamespaceName()))
                            ? substr($iReflection->getName(), strlen($r->getNamespaceName()) + 1)
                            : '\\' . $iReflection->getName()));
                    }, $interfaces
                )
            );
        }

        $classContents = $r->getContents(false);
        $classFileDir = dirname($r->getFileName());
        $classContents = trim(str_replace('__DIR__', sprintf("'%s'", $classFileDir), $classContents));

        $return = "\nnamespace "
            . $r->getNamespaceName()
            . " {\n"
            . $useString
            . $declaration . "\n"
            . $classContents
            . "\n}\n";

        return $return;
    }

    /**
     * Determine what classes are present in the cache
     *
     * @return void
     */
    protected function reflectClassCache()
    {
        $scanner = new FileScanner($this->_cacheFilePath);
        $this->knownClasses = $scanner->getClassNames();
    }
}
