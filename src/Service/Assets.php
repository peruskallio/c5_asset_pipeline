<?php

namespace Concrete\Package\AssetPipeline\Src\Service;

use Assetic\AssetManager as AsseticAssetManager;
use Assetic\Asset\AssetCollection as AsseticAssetCollection;
use Assetic\Factory\AssetFactory as AsseticAssetFactory;
use Assetic\FilterManager;
use Concrete\Core\Application\Application;
use Concrete\Core\Foundation\Environment;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Theme\Theme;
use Concrete\Core\Support\Facade\Facade;
use Exception;

class Assets
{

    protected $app;
    protected $context;
    protected $themeBasePath;
    protected $stylesheetVariables = array();

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->setThemeContext();
    }

    public function setThemeContext($theme = null)
    {
        if ($theme === null) {
            $c = Page::getCurrentPage();
            if (is_object($c)) {
                $theme = $c->getCollectionThemeObject();
            } else {
                $theme = Theme::getSiteTheme();
            }
        }
        if (!is_object($theme)) {
            // TODO: Check whether this can happen or not...
            throw new Exception(t("No theme available for the page!"));
        }
        $env = Environment::get();
        $r = $env->getRecord(
            DIRNAME_THEMES . '/' . $theme->getThemeHandle(),
            $theme->getPackageHandle()
        );
        $this->themeBasePath = $r->file;
    }

    public function setStylesheetVariables($context, array $variables)
    {
        $this->stylesheetVariables[$context] = $variables;
    }

    public function getStyleSheetVariables($context = null)
    {
        if ($context === null) {
            $combined = array();
            foreach ($this->stylesheetVariables as $variables) {
                $combined = array_merge($combined, $variables);
            }
            return $combined;
        } elseif (isset($this->stylesheetVariables[$context])) {
            return $this->stylesheetVariables[$context];
        }
    }

    public function getAssetBasePath()
    {
        return $this->assetBasePath;
    }

    public function css($assets, array $options = null)
    {
        $path = $this->cssPath($assets, $options);

        // TODO: Add the combinedAssetSourceFiles to the asset object before
        //       printing it out
        $html = $this->app->make('helper/html');
        return $html->css($path) . PHP_EOL;
    }

    public function cssPath($assets, array $options = null)
    {
        return $this->compileCssToCache($assets, $options);
    }

    public function javascript($assets, array $options = null)
    {
        $path = $this->javascriptPath($assets, $options);

        // TODO: Add the combinedAssetSourceFiles to the asset object before
        //       printing it out
        $html = $this->app->make('helper/html');
        return $html->javascript($path) . PHP_EOL;
    }

    public function javascriptPath($assets, array $options = null)
    {
        return $this->compileJavascriptToCache($name, $assets);
    }

    public function compileCss(array $assetPaths, array $options = null)
    {
        return $this->compileAssets('css', $assetPaths, $options);
    }

    public function compileCssToCache(array $assetPaths, array $options = null)
    {
        return $this->compileAssetsToCache('css', DIRNAME_CSS, $assetPaths, $options);
    }

    public function compileJavascript(array $assetPaths, array $options = null)
    {
        return $this->compileAssets('js', $assetPaths, $options);
    }

    public function compileJavascriptToCache(array $assetPaths, array $options = null)
    {
        return $this->compileAssetsToCache('js', DIRNAME_JAVASCRIPT, $assetPaths, $options);
    }

    public function compileAssets($extension, array $assetPaths, array $options = null)
    {
        if (count($assetPaths) < 1) {
            throw new Exception(t("Cannot compile asset without any target files."));
        }
        // Modify the asset paths to full paths
        foreach ($assetPaths as $k => $path) {
            $path = $this->getFullPath($path);
            $assetPaths[$k] = $path;
        }

        $assets = $this->getAssetCollection($assetPaths);
        return $assets->dump();
    }

    public function compileAssetsToCache($extension, $cacheDir, array $assetPaths, array $options = null)
    {
        $options = (array) $options;

        $config = $this->app->make('config');

        $cachePath = $config->get('concrete.cache.directory');
        $cachePathRelative = REL_DIR_FILES_CACHE;

        $outputPath = $cachePath . '/' . $cacheDir;
        $relativePath = $cachePathRelative . '/' . $cacheDir;

        $name = isset($options['name']) ? $options['name'] : $this->getDefaultAssetNameFor($extension);

        $outputFileName = $name . '.' . $extension;
        if ($config->get('concrete.cache.theme_css') && file_exists($outputPath . '/' . $outputFileName)) {
            $digest = hash_file('md5', $outputPath . '/' . $outputFileName);
            return $relativePath . '/' . $name . '-' . $digest . '.' . $extension;
        }

        // Save and cache
        $contents = $this->compileAssets($extension, $assetPaths, $options);

        if (!file_exists($outputPath)) {
            @mkdir($outputPath, $config->get('concrete.filesystem.permissions.directory'), true);
        }
        file_put_contents($outputPath . '/' . $outputFileName, $contents);

        $digest = hash_file('md5', $outputPath . '/' . $outputFileName);
        $digestFileName = $name . '-' . $digest . '.' . $extension;
        file_put_contents($outputPath . '/' . $digestFileName, $contents);

        return $relativePath . '/' . $digestFileName;
    }

    public function getAssetCollection(array $assetPaths)
    {
        $factory = $this->getAssetFactory();
        $fm = $factory->getFilterManager();

        $app = Facade::getFacadeApplication();
        $am = $this->app->make(
            'Concrete\Package\AssetPipeline\Src\Asset\ManagerInterface',
            array($app)
        );
        $assets = new AssetCollection();

        // Set the filters to he filter manager
        foreach ($am->getFilters() as $key => $flt) {
            if (!$this->app->bound('assets/filter/' . $key)) {
                throw new Exception(t("Filter not set for key: %s", $key));
            }
            $fm->set($key, $this->app->make('assets/filter/' . $key, $this));
        }

        // Create the asset and push it into the AssetCollection
        // with the filter keys that should be applied to that
        // asset
        $plainAssets = array();
        foreach ($assetPaths as $k => $path) {
            $appliedFilters = array();
            foreach ($filters as $key => $flt) {
                if (preg_match('#' . str_replace('#', '\#', $flt['applyTo']) . '#', $path)) {
                    $appliedFilters[] = $key;
                }
            }
            if (count($appliedFilters) > 0) {
                $assets->add($factory->createAsset($path, $appliedFilters));
            } else {
                $plainAssets[] = $paths;
            }
        }
        // Add assets that did not go through any filters
        if (count($plainAssets) > 0) {
            $assets->add($factory->createAsset($plainAssets));
        }

        return $assets;
    }

    public function getFullPath($path)
    {
        if ($path[0] == '@') {
            if (($pos = strpos($path, '/')) !== false) {
                $location = substr($path, 1, $pos);
                $subpath = substr($path, $pos + 1);

                $locationPath = '';
                if ($location == 'core') {
                    $locationPath = DIR_BASE_CORE;
                } elseif ($location == 'app') {
                    $locationPath = DIR_APPLICATION;
                } elseif ($location == 'package') {
                    if (($pos = strpos($subpath, '/')) !== false) {
                        $pkgHandle = substr($subpath, 0, $pos);
                        $subpath = substr($subpath, $pos + 1);
                        $locationPath = DIR_PACKAGES . '/' . $pkgHandle;
                    } else {
                        throw new Exception(t("Invalid path: %s. Package not defined.", $path));
                    }
                } elseif ($location == 'theme') {
                    if (($pos = strpos($subpath, '/')) !== false) {
                        $themeHandle = substr($subpath, 0, $pos);
                        $subpath = substr($subpath, $pos + 1);
                        if (is_object($th = Theme::getByHandle($themeHandle))) {
                            $env = Environment::get();
                            $locationPath = $env->getPath(DIRNAME_THEMES . '/' . $themeHandle, $th->getPackageHandle());
                        } else {
                            throw new Exception(t("Invalid theme in path: %s. Theme '%s' does not exist."));
                        }
                    } else {
                        throw new Exception(t("Invalid path: %s. Theme not defined.", $path));
                    }
                } else {
                    throw new Exception(t("Invalid path: %s. Unknown location: %s.", $path, $location));
                }

                if (!empty($locationPath)) {
                    return $locationPath . '/' . DIRNAME_CSS . '/' . $subpath;
                }
            } else {
                // This is an assetic alias, e.g. "@jquery".
                return $path;
            }
        } elseif ($path[0] == '/' || preg_match('#[a-z]:[/\\\]#i', $path)) {
            return $path;
        }

        // Theme specific CSS (default)
        return $this->themeBasePath . '/' . DIRNAME_CSS . '/' . $path;
    }

    protected function getDefaultAssetNameFor($extension)
    {
        switch ($extension) {
            case 'css':
                return 'style';
            case 'js':
                return 'script';
            default:
                return $extension;
        }
    }

    /**
     * Generates an asset digest by combining the digests of the asset's source
     * files and generating a hash from the combined string. This is used for
     * asset file versioning to make sure we output the correct file and also
     * to make it easier to deal with browser caching.
     *
     * This is run for the original assets because
     * we need to know the file digest BEFORE we run the asset compilation and
     * know the actual file conents.
     */
    protected function generateAssetDigest(array $assetPaths)
    {
        $digest = '';
        foreach ($assetPaths as $asset) {
            $digest .= $asset . '#' . $lastModified;
        }
        return md5($digest);
    }

    protected function getAssetFactory()
    {
        $am = new AsseticAssetManager();
        $fm = new AsseticFilterManager();

        $factory = new AsseticAssetFactory(DIR_BASE);
        $factory->setAssetManager($am);
        $factory->setFilterManager($fm);

        return $factory;
    }

}
