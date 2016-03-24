<?php

namespace Concrete\Package\AssetPipeline\Src\Asset\Filter\Less;

use Assetic\Filter\FilterInterface;
use Concrete\Core\Foundation\Environment;
use Concrete\Core\Page\Theme\Theme;
use Concrete\Package\AssetPipeline\Src\Asset\Filter\AbstractFunctionProvider;

/**
 * Implements a function provider for the Less parser.
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
     * @param \Less_Tree $urlTree
     *
     * @return \Less_Tree
     */
    public function assetUrl($urlTree)
    {
        $urlTree = $this->assetPath($urlTree);
        $urlTree->escaped = true;
        $urlTree->value = 'url(' . $urlTree->value . ')';
        return $urlTree;
    }

    /**
     * Returns an asset path relative to the `application` directory.
     *
     * @param \Less_Tree $urlTree
     *
     * @return \Less_Tree
     */
    public function assetPath($urlTree)
    {
        $url = $urlTree->value;
        if ($url[0] != '/') {
            $url = '/' . $url;
        }
        $urlTree->value = DIR_REL . '/' . DIRNAME_APPLICATION . $url;
        return $urlTree;
    }

    /**
     * A wrapper method for `coreAssetPath()` for printing out
     * the asset path value within a `url()` CSS definition.
     *
     * @param \Less_Tree $urlTree
     *
     * @return \Less_Tree
     */
    public function coreAssetUrl($urlTree)
    {
        $urlTree = $this->coreAssetPath($urlTree);
        $urlTree->escaped = true;
        $urlTree->value = 'url(' . $urlTree->value . ')';
        return $urlTree;
    }

    /**
     * Returns an asset path relative to the `concrete` (core) directory.
     *
     * @param \Less_Tree $urlTree
     *
     * @return \Less_Tree
     */
    public function coreAssetPath($urlTree)
    {
        $url = $urlTree->value;
        if ($url[0] != '/') {
            $url = '/' . $url;
        }
        $urlTree->value = DIR_REL . '/' . DIRNAME_CORE . $url;
        return $urlTree;
    }

    /**
     * A wrapper method for `themeAssetPath()` for printing out
     * the asset path value within a `url()` CSS definition.
     *
     * @param \Less_Tree $urlTree
     * @param \Less_Tree|null $themeTree
     *
     * @return \Less_Tree
     */
    public function themeAssetUrl($urlTree, $themeTree = null)
    {
        $urlTree = $this->themeAssetPath($urlTree, $themeTree);
        $urlTree->escaped = true;
        $urlTree->value = 'url(' . $urlTree->value . ')';
        return $urlTree;
    }

    /**
     * Returns an asset path relative to the theme directory.
     * If the second argument is given, it is used as the
     * theme handle for which the path is returned. If the
     * theme handle is not defined, the current theme is
     * used instead.
     *
     * @param \Less_Tree $urlTree
     * @param \Less_Tree|null $themeTree
     *
     * @return \Less_Tree
     */
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

    /**
     * A wrapper method for `packageAssetPath()` for printing out
     * the asset path value within a `url()` CSS definition.
     *
     * @param \Less_Tree $urlTree
     * @param \Less_Tree $packageTree
     *
     * @return \Less_Tree
     */
    public function packageAssetUrl($urlTree, $packageTree)
    {
        $urlTree = $this->packageAssetPath($urlTree, $packageTree);
        $urlTree->escaped = true;
        $urlTree->value = 'url(' . $urlTree->value . ')';
        return $urlTree;
    }

    /**
     * Returns an asset path relative a package directory.
     * The second argument expects a pacakge handle for
     * which the path should be generated.
     *
     * @param \Less_Tree $urlTree
     * @param \Less_Tree $packageTree
     *
     * @return \Less_Tree
     */
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
