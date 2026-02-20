<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 12.02.2015
 * Time: 14:31
 */

namespace ExprAs\Asset\Filter;

use Assetic\Asset\AssetInterface;
use Assetic\Factory\AssetFactory;
use Assetic\Filter\DependencyExtractorInterface;
use Assetic\Util\LessUtils;

class LessphpParser implements DependencyExtractorInterface
{
    private array $presets = [];
    private $formatter;
    private $preserveComments;

    /**
     * Lessphp Load Paths
     *
     * @var array
     */
    protected $loadPaths = [];

    /**
     * Adds a load path to the paths used by lessphp
     *
     * @param string $path Load Path
     */
    public function addLoadPath($path)
    {
        $this->loadPaths[] = $path;
    }

    /**
     * Sets load paths used by lessphp
     *
     * @param array $loadPaths Load paths
     */
    public function setLoadPaths(array $loadPaths)
    {
        $this->loadPaths = $loadPaths;
    }

    public function setPresets(array $presets)
    {
        $this->presets = $presets;
    }

    /**
     * @param string $formatter One of "lessjs", "compressed", or "classic".
     */
    public function setFormatter($formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * @param boolean $preserveComments
     */
    public function setPreserveComments($preserveComments)
    {
        $this->preserveComments = $preserveComments;
    }

    public function filterLoad(AssetInterface $asset)
    {
        $root = $asset->getSourceRoot();
        $path = $asset->getSourcePath();

        $importDirs = $this->loadPaths;

        $lc = new \Less_Parser();
        if ($root && $path) {
            $importDirs[] = dirname($root . '/' . $path);
        }

        $lc->SetOption('import_dirs', $importDirs);

        $lc->ModifyVars($this->presets);
        $lc->parse($asset->getContent());
        $asset->setContent($lc->getCss());
    }

    public function filterDump(AssetInterface $asset)
    {
    }

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
                if (file_exists($file = $loadPath . '/' . $reference)) {
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
