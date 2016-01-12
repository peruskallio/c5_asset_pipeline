<?php
namespace Concrete\Package\AssetPipeline\Src\Service;

use Concrete\Core\Page\Theme\Theme;
use Config;
use Core;
use Environment;
use Package;
use Page;

defined('C5_EXECUTE') or die("Access Denied.");

class Assets
{

    protected $context;
    protected $themeBasePath;
    protected $stylesheetVariables = array();
    protected $sourceUriRoot;

    public function __construct()
    {
        $this->setThemeContext();
    }

    public function setSourceUriRoot($uri)
    {
        $this->sourceUriRoot = $uri;
    }

    public function setThemeContext($theme = null)
    {
        $c = Page::getCurrentPage();
        $theme = $c->getCollectionThemeObject();
        if (!is_object($theme)) {
            // TODO: Check whether this can happen or not...
            throw new \Exception(t("No theme available for the page!"));
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

        $html = Core::make('helper/html');
        return $html->css($path) . PHP_EOL;
    }

    public function cssPath($assets, array $options = null)
    {
        return $this->compileCssToCache($assets, $options);
    }

    public function javascript($assets, array $options = null)
    {
        $path = $this->javascriptPath($assets, $options);

        $html = Core::make('helper/html');
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
            throw new \Exception(t("Cannot compile asset without any target files."));
        }
        // Modify the asset paths to full paths
        foreach ($assetPaths as $k => $path) {
            $path = $this->getFullPath($path);
            $assetPaths[$k] = $path;
        }

        // TODO: How to fix the relative paths in the CSS
        // $this->sourceUriRoot
        $assets = $this->getAssetCollection($assetPaths);
        return $assets->dump();
    }

    public function compileAssetsToCache($extension, $cacheDir, array $assetPaths, array $options = null)
    {
        $options = (array) $options;

        $cachePath = Config::get('concrete.cache.directory');
        $cachePathRelative = REL_DIR_FILES_CACHE;

        $outputPath = $cachePath . '/' . $cacheDir;
        $relativePath = $cachePathRelative . '/' . $cacheDir;

        $name = isset($options['name']) ? $options['name'] : 'style';

        $outputFileName = $name . '.' . $extension;
        if (Config::get('concrete.cache.theme_css') && file_exists($outputPath . '/' . $outputFileName)) {
            $digest = hash_file('md5', $outputPath . '/' . $outputFileName);
            return $relativePath . '/' . $name . '-' . $digest . '.' . $extension;
        }

        // Save and cache
        $contents = $this->compileAssets($extension, $assetPaths, $options);

        if (!file_exists($outputPath)) {
            @mkdir($outputPath, Config::get('concrete.filesystem.permissions.directory'), true);
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

        $filters = Core::make('assets/manager')->getFilters();
        $assets = new \Assetic\Asset\AssetCollection();
        foreach ($filters as $key => $flt) {
            if (!Core::bound('assets/filter/' . $key)) {
                throw new \Exception(t("Filter not set for key: %s", $key));
            }
            $fm->set($key, Core::make('assets/filter/' . $key, $this));

            if (isset($flt['applyTo'])) {
                $paths = array();
                foreach ($assetPaths as $k => $path) {
                    if (preg_match('#' . str_replace('#', '\#', $flt['applyTo']) . '#', $path)) {
                        $paths[] = $path;
                        unset($assetPaths[$k]);
                    }
                }
                if (count($paths) > 0) {
                    $assets->add($factory->createAsset($paths, array($key)));
                }
            }
        }
        // Add assets that did not go through any filters
        if (count($assetPaths) > 0) {
            $assets->add($factory->createAsset($assetPaths));
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
                        throw new \Exception(t("Invalid path: %s. Package not defined.", $path));
                    }
                } elseif ($location == 'theme') {
                    if (($pos = strpos($subpath, '/')) !== false) {
                        $themeHandle = substr($subpath, 0, $pos);
                        $subpath = substr($subpath, $pos + 1);
                        if (is_object($th = Theme::getByHandle($themeHandle))) {
                            $env = Environment::get();
                            $locationPath = $env->getPath(DIRNAME_THEMES . '/' . $themeHandle, $th->getPackageHandle());
                        } else {
                            throw new \Exception(t("Invalid theme in path: %s. Theme '%s' does not exist."));
                        }
                    } else {
                        throw new \Exception(t("Invalid path: %s. Theme not defined.", $path));
                    }
                } else {
                    throw new \Exception(t("Invalid path: %s. Unknown location: %s.", $path, $location));
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
        $am = new \Assetic\AssetManager();
        $fm = new \Assetic\FilterManager();

        $factory = new \Assetic\Factory\AssetFactory(DIR_BASE);
        $factory->setAssetManager($am);
        $factory->setFilterManager($fm);

        return $factory;
    }

}
