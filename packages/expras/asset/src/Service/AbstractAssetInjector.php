<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 06.04.2014
 * Time: 15:09
 */

namespace ExprAs\Asset\Service;

use Mezzio\ZendView\ZendViewRenderer;
use Laminas\View\Exception\UnexpectedValueException;
use Laminas\View\Helper\HeadLink;
use Laminas\View\Helper\HeadScript;
use Laminas\View\Helper\InlineScript;
use Laminas\View\HelperPluginManager;

abstract class AbstractAssetInjector
{
    protected $_assets = [];

    protected $_configured = false;

    public function setAssets($assets)
    {
        foreach ($assets as $_k => $_asset) {
            if (is_string($_asset)) {
                $this->addAsset($_asset, null, $_k);
            } else {
                $this->addAsset(
                    $_asset['name'],
                    $_asset['helper'] ?? null,
                    $_asset['priority'] ?? $_k
                );
            }
        }
        return $this;
    }


    /**
     * @param $src
     *
     * @return string
     */
    protected function _discoverHelper($src)
    {
        $ext = pathinfo(parse_url((string) $src, PHP_URL_PATH), PATHINFO_EXTENSION);
        $helper = match ($ext) {
            'js' => 'headScript',
            'css' => 'headLink',
            default => throw new UnexpectedValueException('Could not determine helper for asset ' . $src),
        };

        return $helper;

    }

    /**
     * @param $src
     * @param null $helper
     * @param int  $priority
     *
     * @return $this
     */
    public function addAsset($src, $helper = null, $priority = 1)
    {
        if (is_array($src)) {
            $priority = $src['priority'] ?? $priority;
            $helper = $src['helper'] ?? $helper;
            $src = $src['name'];
        }

        if (!$helper) {
            $helper = $this->_discoverHelper($src);
        }

        $this->_assets[] = ['priority' => $priority, 'name' => $src, 'helper' => $helper];
        return $this;

    }

    /**
     * @param $src
     * @param int $priority
     *
     * @return AbstractAssetInjector
     */
    public function addStyleSheet($src, $priority = 1)
    {

        return $this->addAsset($src, 'headLink', $priority);
    }

    /**
     * @param $src
     * @param int $priority
     *
     * @return AbstractAssetInjector
     */
    public function addScript($src, $priority = 1)
    {
        return $this->addAsset($src, 'headScript', $priority);
    }

    /**
     * @param $src
     * @param int $priority
     *
     * @return AbstractAssetInjector
     */
    public function addInlineScript($src, $priority = 1)
    {
        return $this->addAsset($src, 'inlineScript', $priority);
    }

    public function injectAssets(HelperPluginManager $helperPluginManager)
    {

        $renderer = $helperPluginManager->getRenderer();

        foreach ($this->_assets as $_k => $_asset) {

            if (!str_starts_with((string) $_asset['name'], '//') && !str_starts_with((string) $_asset['name'], 'http')) {
                if (class_exists('Blast\BaseUrl\BaseUrlMiddleware')) {
                    $_asset['name'] = $renderer->basePath('/' . ltrim((string) $_asset['name'], '/'));
                } else {
                    $_asset['name'] = '/' . ltrim((string) $_asset['name'], '/');
                }
            }

            $plugin = $helperPluginManager->get($_asset['helper']);
            if ($plugin instanceof HeadScript || $plugin instanceof InlineScript) {
                $plugin->appendFile($_asset['name']);
            } elseif ($plugin instanceof HeadLink) {
                $plugin->appendStylesheet($_asset['name'], 'all');
            } else {
                $plugin($_asset['name']);
            }
        }
    }
}
