<?php

namespace Concrete\Core\Page\Theme;

use Concrete\Core\StyleCustomizer\Preset;
use Concrete\Package\AssetPipeline\Src\Core\Original\Page\Theme\Theme as CoreTheme;
use Core;
use Environment;
use Loader;

class Theme extends CoreTheme
{

    const THEME_CUSTOMIZABLE_STYLESHEET_EXTENSION = '.less';

    /**
     * {@inheritDoc}
     */
    public function getThemeCustomizablePreset($handle)
    {
        $env = Environment::get();
        if ($this->isThemeCustomizable()) {
            $extensions = Core::make('Concrete\Package\AssetPipeline\Src\Asset\UtilityInterface')->getFileExtensionsForCustomizableStyles();
            foreach ($extensions as $ext) {
                $file = $env->getRecord(
                    DIRNAME_THEMES.'/'.$this->getThemeHandle(
                    ).'/'.DIRNAME_CSS.'/'.DIRNAME_STYLE_CUSTOMIZER_PRESETS.'/'.$handle.'.'.$ext,
                    $this->getPackageHandle()
                );
                if ($file->exists()) {
                    break;
                }
            }

            if (is_object($file) && $file->exists()) {
                $urlroot = $env->getURL(
                    DIRNAME_THEMES.'/'.$this->getThemeHandle().'/'.DIRNAME_CSS,
                    $this->getPackageHandle()
                );
                $preset = Preset::getFromFile($file->file, $urlroot);

                return $preset;
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getThemeCustomizableStylePresets()
    {
        $presets = array();
        $env = Environment::get();
        if ($this->isThemeCustomizable()) {
            $directory = $env->getPath(
                DIRNAME_THEMES.'/'.$this->getThemeHandle(
                ).'/'.DIRNAME_CSS.'/'.DIRNAME_STYLE_CUSTOMIZER_PRESETS,
                $this->getPackageHandle()
            );
            $urlroot = $env->getURL(
                DIRNAME_THEMES.'/'.$this->getThemeHandle().'/'.DIRNAME_CSS,
                $this->getPackageHandle()
            );
            $dh = Loader::helper('file');
            $files = $dh->getDirectoryContents($directory);
            foreach ($files as $f) {
                if (Core::make('Concrete\Package\AssetPipeline\Src\Asset\UtilityInterface')->canFileContainCustomizableStyles($f)) {
                    $preset = Preset::getFromFile($directory.'/'.$f, $urlroot);
                    if (is_object($preset)) {
                        $presets[] = $preset;
                    }
                }
            }
        }
        usort(
            $presets,
            function ($a, $b) {
                if ($a->isDefaultPreset()) {
                    return -1;
                } else {
                    return strcasecmp($a->getPresetDisplayName('text'), $b->getPresetDisplayName('text'));
                }
            }
        );

        return $presets;
    }

    /**
     * {@inheritDoc}
     */
    public function getThemeCustomizableStyleSheets()
    {
        $sheets = array();
        $env = Environment::get();
        if ($this->isThemeCustomizable()) {
            $directory = $env->getPath(
                DIRNAME_THEMES.'/'.$this->getThemeHandle().'/'.DIRNAME_CSS,
                $this->getPackageHandle()
            );
            $dh = Loader::helper('file');
            $files = $dh->getDirectoryContents($directory);
            foreach ($files as $f) {
                if (Core::make('Concrete\Package\AssetPipeline\Src\Asset\UtilityInterface')->canFileContainCustomizableStyles($f)) {
                    $sheets[] = $this->getStylesheetObject($f);
                }
            }
        }

        return $sheets;
    }



}
