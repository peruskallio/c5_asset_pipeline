<?php

namespace Concrete\Package\AssetPipeline\Src\Asset\Scss;

use Assetic\Filter\FilterInterface;
use Concrete\Core\Foundation\Environment;
use Concrete\Core\Page\Theme\Theme;
use Concrete\Package\AssetPipeline\Src\Asset\AbstractFunctionProvider;

/**
 * Implements a function provider for the Scss parser.
 *
 * @author Antti Hukkanen <antti.hukkanen@mainiotech.fi>
 */
class FunctionProvider extends AbstractFunctionProvider
{

    /**
     * {@inheritDoc}
     */
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

    /**
     * A wrapper method for `assetPath()` for printing out
     * the asset path value within a `url()` CSS definition.
     *
     * @param array $args
     *
     * @return string
     */
    public function assetUrl(array $args)
    {
        return 'url(' . $this->assetPath($args) . ')';
    }

    /**
     * Returns an asset path relative to the `application` directory.
     * The given arguments should contain the relative URL as the
     * first item of the array.
     *
     * @param array $args
     *
     * @return string
     */
    public function assetPath(array $args)
    {
        list($url) = $args;
        $url = $this->extractStringFromArgument($url);
        if ($url[0] != '/') {
            $url = '/' . $url;
        }
        return DIR_REL . '/' . DIRNAME_APPLICATION . $url;
    }

    /**
     * A wrapper method for `coreAssetPath()` for printing out
     * the asset path value within a `url()` CSS definition.
     *
     * @param array $args
     *
     * @return string
     */
    public function coreAssetUrl(array $args)
    {
        return 'url(' . $this->coreAssetPath($args) . ')';
    }

    /**
     * Returns an asset path relative to the `concrete` (core) directory.
     * The given arguments should contain the relative URL as the
     * first item of the array.
     *
     * @param array $args
     *
     * @return string
     */
    public function coreAssetPath(array $args)
    {
        list($url) = $args;
        $url = $this->extractStringFromArgument($url);
        if ($url[0] != '/') {
            $url = '/' . $url;
        }
        return DIR_REL . '/' . DIRNAME_CORE . $url;
    }

    /**
     * A wrapper method for `themeAssetPath()` for printing out
     * the asset path value within a `url()` CSS definition.
     *
     * @param array $args
     *
     * @return string
     */
    public function themeAssetUrl(array $args)
    {
        return 'url(' . $this->themeAssetPath($args) . ')';
    }

    /**
     * Returns an asset path relative to the theme directory.
     * The given arguments array needs to contain 1-2 values
     * in it as follows:
     *
     * 1. The first item in the array needs to be the relative
     *    URL for which we want the full path for.
     * 2. The second item in the array can either be left out
     *    or when defined, represent the theme handle.
     *
     * If the second argument is given, it is used as the
     * theme handle for which the path is returned. If the
     * theme handle is not defined, the current theme is
     * used instead.
     *
     * @param array $args
     *
     * @return string
     */
    public function themeAssetPath(array $args)
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

    /**
     * A wrapper method for `packageAssetPath()` for printing out
     * the asset path value within a `url()` CSS definition.
     *
     * @param array $args
     *
     * @return string
     */
    public function packageAssetUrl(array $args)
    {
        return 'url(' . $this->packageAssetPath($args) . ')';
    }

    /**
     * Returns an asset path relative a package directory.
     *
     * The given arguments array needs to contain two values
     * in it as follows:
     *
     * 1. The first item in the array needs to be the relative
     *    URL for which we want the full path for.
     * 2. The second item in the array needs to be the pacakge
     *    handle for which we want the URL for.
     *
     * @param array $args
     *
     * @return string
     */
    public function packageAssetPath(array $args)
    {
        list($url, $package) = $args;
        $url = $this->extractStringFromArgument($url);
        $package = $this->extractStringFromArgument($package);
        if ($url[0] != '/') {
            $url = '/' . $url;
        }
        return DIR_REL . '/' . DIRNAME_PACKAGES . '/' . $package . $url;
    }

    /**
     * Extracts a string from the given preprocessing argument.
     * The argument needs to be in array format defined by the
     * filter for this method to return a proper string. Other
     * mixed types may also be given as a parameter to this
     * method but in those cases, null is returned.
     *
     * @param mixed $arg
     *
     * @return string|null
     */
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
