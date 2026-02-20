<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 1/10/2018
 * Time: 17:48
 */

namespace ExprAs\Asset\Filter;

use Assetic\Asset\AssetInterface;
use Assetic\Exception\FilterException;
use Assetic\Filter\BaseProcessFilter;

class SassFilter extends BaseProcessFilter
{
    protected $_executable;

    public function __construct(protected $_path = [])
    {
    }

    protected function _findExecutable()
    {
        if ($this->_executable) {
            return $this->_executable;
        }

        foreach ($this->_path as $_path) {
            if (is_executable($_path)) {
                return $this->_executable = $_path;
            }
        }
        return 'node-sass';
    }

    public function filterLoad(AssetInterface $asset)
    {
        $sassProcessArgs = [
            $this->_findExecutable(),
            $asset->getSourceRoot() . '/' . $asset->getSourcePath()
        ];
        $pb = $this->createProcessBuilder($sassProcessArgs);
        $pb->inheritEnvironmentVariables(false);

        $proc = $pb->getProcess();
        $code = $proc->run();

        if (0 !== $code) {
            throw FilterException::fromProcess($proc)->setInput($asset->getContent());
        }

        $asset->setContent($proc->getOutput());
    }

    public function filterDump(AssetInterface $asset)
    {
    }
}
