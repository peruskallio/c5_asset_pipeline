<?php

namespace Concrete\Package\AssetPipeline\Src\Asset\Less;

use Assetic\Filter\FilterInterface;
use Concrete\Core\Foundation\Environment;
use Concrete\Core\Page\Theme\Theme;
use Concrete\Package\AssetPipeline\Src\Asset\AbstractFunctionProvider;

class FunctionProvider extends AbstractFunctionProvider
{

    public function registerFor(FilterInterface $filter)
    {
        $functions = array(
            'asset-url'             => 'assetUrl',
            'asset-path'            => 'assetPath',
            'core-asset-url'        => 'coreAssetUrl',
            'core-asset-path'       => 'coreAssetPath',
            'theme-asset-url'       => 'themeAssetUrl',
            'theme-asset-path'      => 'themeAssetPath',
            'package-asset-url'     => 'packageAssetUrl',
            'package-asset-path'    => 'packageAssetPath',
        );
        foreach ($functions as $sassFunc => $func) {
            $filter->registerFunction($sassFunc, array($this, $func));
        }
    }

    public function assetUrl($urlTree)
    {
        $urlTree = $this->assetPath($urlTree);
        $urlTree->escaped = true;
        $urlTree->value = 'url(' . $urlTree->value . ')';
        return $urlTree;
    }

    public function assetPath($urlTree)
    {
        $url = $urlTree->value;
        if ($url[0] != '/') {
            $url = '/' . $url;
        }
        $urlTree->value = DIR_REL . '/' . DIRNAME_APPLICATION . $url;
        return $urlTree;
    }

    public function coreAssetUrl($urlTree)
    {
        $urlTree = $this->coreAssetPath($urlTree);
        $urlTree->escaped = true;
        $urlTree->value = 'url(' . $urlTree->value . ')';
        return $urlTree;
    }

    public function coreAssetPath($urlTree)
    {
        $url = $urlTree->value;
        if ($url[0] != '/') {
            $url = '/' . $url;
        }
        $urlTree->value = DIR_REL . '/' . DIRNAME_CORE . $url;
        return $urlTree;
    }

    public function themeAssetUrl($urlTree, $themeTree = null)
    {
        $urlTree = $this->themeAssetPath($urlTree, $themeTree);
        $urlTree->escaped = true;
        $urlTree->value = 'url(' . $urlTree->value . ')';
        return $urlTree;
    }

    public function themeAssetPath($urlTree, $themeTree = null)
    {
        $url = $urlTree->value;
        $theme = is_object($themeTree) ? $themeTree->value : null;
        if ($url[0] != '/') {
            $url = '/' . $url;
        }

        if (empty($theme)) {
            $theme = $this->getCurrentTheme();
        } else {
            $to = Theme::getByHandle($theme);
            if (!is_object($to)) {
                throw new \Exception(t("Invalid theme-asset-url: %s, %s. A theme does not exist with handle: %s.", $url, $theme, $theme));
            }
            $theme = $to;
        }
        // Default to theme URL
        $env = Environment::get();
        $themeUrl = $env->getURL(
            DIRNAME_THEMES . '/' . $theme->getThemeHandle(),
            $theme->getPackageHandle()
        );
        $urlTree->value = $themeUrl . $url;
        return $urlTree;
    }

    public function packageAssetUrl($urlTree, $packageTree)
    {
        $urlTree = $this->packageAssetPath($urlTree, $packageTree);
        $urlTree->escaped = true;
        $urlTree->value = 'url(' . $urlTree->value . ')';
        return $urlTree;
    }

    public function packageAssetPath($urlTree, $packageTree)
    {
        $url = $urlTree->value;
        $package = $packageTree->value;
        if ($url[0] != '/') {
            $url = '/' . $url;
        }
        $urlTree->value = DIR_REL . '/' . DIRNAME_PACKAGES . '/' . $package . $url;
        return $urlTree;
    }

}
