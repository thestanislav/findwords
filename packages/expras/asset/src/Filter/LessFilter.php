<?php

namespace ExprAs\Asset\Filter;

use Assetic\Asset\AssetInterface;
use Assetic\Exception\FilterException;
use Assetic\Factory\AssetFactory;
use Assetic\Filter\BaseNodeFilter;
use Assetic\Filter\DependencyExtractorInterface;
use Assetic\Util\LessUtils;

/**
 * Loads LESS files.
 *
 * @link   http://lesscss.org/
 * @author Kris Wallsmith <kris.wallsmith@gmail.com>
 */
class LessFilter extends BaseNodeFilter implements DependencyExtractorInterface
{
    /**
     * @var array
     */
    private $treeOptions;

    /**
     * @var array
     */
    private $parserOptions;

    protected $modifiedVariables = [];

    protected $globalVariables = [];



    /**
     * Load Paths
     *
     * A list of paths which less will search for includes.
     *
     * @var array
     */
    protected $loadPaths = [];

    /**
     * Constructor.
     *
     * @param string $nodeBin   The path to the node binary
     * @param array  $nodePaths An array of node paths
     */
    public function __construct(private $nodeBin = '/usr/bin/node', array $nodePaths = [])
    {
        $this->setNodePaths($nodePaths);
        $this->treeOptions = [];
        $this->parserOptions = [];
    }

    /**
     * @param bool $compress
     */
    public function setCompress($compress)
    {
        $this->addTreeOption('compress', $compress);
    }

    public function setLoadPaths(array $loadPaths)
    {
        $this->loadPaths = $loadPaths;
    }

    /**
     * @param $name
     * @param $value
     *
     * @return $this
     */
    public function modifyVariables($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $_k => $_v) {
                $this->modifyVariables($_k, $_v);
            }
            return $this;
        }
        $this->modifiedVariables[$name] = $value;
        return $this;
    }

    /**
     * @param $variables
     *
     * @return $this
     */
    public function setGlobalVariables($variables)
    {
        foreach ($variables as $_k => $_v) {
            $this->addGlobalVariable($_k, $_v);
        }
        return $this;
    }

    /**
     * @param $name
     * @param $value
     *
     * @return $this
     */
    public function addGlobalVariable($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $_k => $_v) {
                $this->addGlobalVariable($_k, $_v);
            }
            return $this;
        }
        $this->globalVariables[$name] = $value;
        return $this;
    }

    /**
     * Adds a path where less will search for includes
     *
     * @param string $path Load path (absolute)
     */
    public function addLoadPath($path)
    {
        $this->loadPaths[] = $path;
    }

    /**
     * @param string $code
     * @param string $value
     */
    public function addTreeOption($code, $value)
    {
        $this->treeOptions[$code] = $value;
    }

    /**
     * @param string $code
     * @param string $value
     */
    public function addParserOption($code, $value)
    {
        $this->parserOptions[$code] = $value;
    }

    public function filterLoad(AssetInterface $asset)
    {
        static $format = <<<'EOF'
var less = require('less');
var sys  = require(process.binding('natives').util ? 'util' : 'sys');

new(less.Parser)(%s).parse(%s, function(e, tree) {
    if (e) {
        less.writeError(e);
        process.exit(2);
    }

    try {
        sys.print(tree.toCSS(%s));
    } catch (e) {
        less.writeError(e);
        process.exit(3);
    }
}, %s);

EOF;

        $root = $asset->getSourceRoot();
        $path = $asset->getSourcePath();

        // parser options
        $parserOptions = $this->parserOptions;
        if ($root && $path) {
            $parserOptions['paths'] = [dirname($root.'/'.$path)];
            $parserOptions['filename'] = basename((string) $path);
        }

        foreach ($this->loadPaths as $loadPath) {
            $parserOptions['paths'][] = $loadPath;
        }

        $pb = $this->createProcessBuilder();

        $pb->add($this->nodeBin)->add($input = tempnam(sys_get_temp_dir(), 'assetic_less'));
        $cmd = sprintf(
            $format,
            json_encode($parserOptions, JSON_THROW_ON_ERROR),
            json_encode($asset->getContent(), JSON_THROW_ON_ERROR),
            json_encode($this->treeOptions, JSON_THROW_ON_ERROR),
            json_encode(['modifyVars' => $this->modifiedVariables, 'globalVars' => $this->globalVariables], JSON_THROW_ON_ERROR)
        );
        file_put_contents($input, $cmd);

        $proc = $pb->getProcess();
        $code = $proc->run();
        unlink($input);

        if (0 !== $code) {
            throw FilterException::fromProcess($proc)->setInput($asset->getContent());
        }

        $asset->setContent($proc->getOutput());
    }

    public function filterDump(AssetInterface $asset)
    {
    }

    /**
     * @todo support for import-once
     * @todo support for import (less) "lib.css"
     */
    public function getChildren(AssetFactory $factory, $content, $loadPath = null)
    {
        $loadPaths = $this->loadPaths;
        if (null !== $loadPath) {
            $loadPaths[] = $loadPath;
        }

        if (empty($loadPaths)) {
            return [];
        }

        $children = [];
        foreach (LessUtils::extractImports($content) as $reference) {
            if (str_ends_with((string) $reference, '.css')) {
                // skip normal css imports
                // todo: skip imports with media queries
                continue;
            }

            if (!str_ends_with((string) $reference, '.less')) {
                $reference .= '.less';
            }

            foreach ($loadPaths as $loadPath) {
                if (file_exists($file = $loadPath.'/'.$reference)) {
                    $coll = $factory->createAsset($file, [], ['root' => $loadPath]);
                    foreach ($coll as $leaf) {
                        $leaf->ensureFilter($this);
                        $children[] = $leaf;
                        goto next_reference;
                    }
                }
            }

            next_reference:
        }

        return $children;
    }
}
