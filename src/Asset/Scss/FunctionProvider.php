<?php

namespace Concrete\Package\AssetPipeline\Src\Asset\Scss;

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

    public function assetUrl($args)
    {
        return 'url(' . $this->assetPath($args) . ')';
    }

    public function assetPath($args)
    {
        list($url) = $args;
        $url = $this->extractStringFromArgument($url);
        if ($url[0] != '/') {
            $url = '/' . $url;
        }
        return DIR_REL . '/' . DIRNAME_APPLICATION . $url;
    }

    public function coreAssetUrl($args)
    {
        return 'url(' . $this->coreAssetPath($args) . ')';
    }

    public function coreAssetPath($args)
    {
        list($url) = $args;
        $url = $this->extractStringFromArgument($url);
        if ($url[0] != '/') {
            $url = '/' . $url;
        }
        return DIR_REL . '/' . DIRNAME_CORE . $url;
    }

    public function themeAssetUrl($args)
    {
        return 'url(' . $this->themeAssetPath($args) . ')';
    }

    public function themeAssetPath($args)
    {
        list($url, $theme) = $args;
        $url = $this->extractStringFromArgument($url);
        $theme = $this->extractStringFromArgument($theme);
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
        return $themeUrl . $url;
    }

    public function packageAssetUrl($args)
    {
        return 'url(' . $this->packageAssetPath($args) . ')';
    }

    public function packageAssetPath($args)
    {
        list($url, $package) = $args;
        $url = $this->extractStringFromArgument($url);
        $package = $this->extractStringFromArgument($package);
        if ($url[0] != '/') {
            $url = '/' . $url;
        }
        return DIR_REL . '/' . DIRNAME_PACKAGES . '/' . $package . $url;
    }

    protected function extractStringFromArgument($arg)
    {
        if (!is_array($arg)) {
            return null;
        }
        $string = $arg[2][0];
        if (is_array($string)) {
            return $this->extractStringFromArgument($string);
        }
        return $string;
    }

}
