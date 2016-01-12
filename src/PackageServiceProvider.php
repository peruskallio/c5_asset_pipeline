<?php
namespace Concrete\Package\AssetPipeline\Src;

use Core;
use Concrete\Core\Foundation\Service\Provider as ServiceProvider;
use Events;
use Page;
use Symfony\Component\ClassLoader\MapClassLoader;

defined('C5_EXECUTE') or die("Access Denied.");

class PackageServiceProvider extends ServiceProvider
{

    protected $pkgHandle = 'asset_pipeline';

    public function register()
    {
        $singletons = array(
            'helper/assets' => '\Concrete\Package\AssetPipeline\Src\Service\Assets',
        );

        foreach($singletons as $key => $value) {
            $this->app->singleton($this->pkgHandle . '/' . $key, $value);
        }
    }

    public function registerEvents()
    {
        // Add the sitemap icons listener
        Events::addListener(
            'on_before_render', function($event) {
                $c = Page::getCurrentPage();
                $view = $event->getArgument('view');
                $assets = Core::make('asset_pipeline/helper/assets');
                $theme = $c->getCollectionThemeObject();
                // TODO: If there are page-specific styles set, should we use
                //       this one instead:
                //$style = $c->getCustomStyleObject();
                $style = $theme->getThemeCustomStyleObject();
                if (is_object($style)) {
                    $valueList = $style->getValueList();
                    if ($valueList instanceof \Concrete\Core\StyleCustomizer\Style\ValueList) {
                        $variables = array();
                        foreach ($valueList->getValues() as $value) {
                            $variables = array_merge($value->toLessVariablesArray(), $variables);
                        }
                        $assets->setStylesheetVariables('theme', $variables);
                    }
                }
                $view->addScopeItems(array(
                    'assets' => $assets,
                ));
            }
        );
    }

    public function registerOverrides()
    {
        // Core overrides
        $loader = new MapClassLoader(array(
            'Concrete\\Core\\Page\\Theme\\Theme' => DIR_PACKAGES . '/' . $this->pkgHandle . '/src/Core/Page/Theme/Theme.php',
            'Concrete\\Core\\StyleCustomizer\\Preset' => DIR_PACKAGES . '/' . $this->pkgHandle . '/src/Core/StyleCustomizer/Preset.php',
            'Concrete\\Core\\StyleCustomizer\\Stylesheet' => DIR_PACKAGES . '/' . $this->pkgHandle . '/src/Core/StyleCustomizer/Stylesheet.php',
            'Concrete\\Core\\StyleCustomizer\\Style\\ColorStyle' => DIR_PACKAGES . '/' . $this->pkgHandle . '/src/Core/StyleCustomizer/Style/ColorStyle.php',
            'Concrete\\Core\\StyleCustomizer\\Style\\ImageStyle' => DIR_PACKAGES . '/' . $this->pkgHandle . '/src/Core/StyleCustomizer/Style/ImageStyle.php',
            'Concrete\\Core\\StyleCustomizer\\Style\\SizeStyle' => DIR_PACKAGES . '/' . $this->pkgHandle . '/src/Core/StyleCustomizer/Style/SizeStyle.php',
            'Concrete\\Core\\StyleCustomizer\\Style\\TypeStyle' => DIR_PACKAGES . '/' . $this->pkgHandle . '/src/Core/StyleCustomizer/Style/TypeStyle.php',
            'Concrete\\Core\\StyleCustomizer\\Style\\ValueList' => DIR_PACKAGES . '/' . $this->pkgHandle . '/src/Core/StyleCustomizer/Style/ValueList.php',
        ));
        $loader->register(true);

        // For some reason this has to be called MANUALLY (and not through the
        // c5 ClassAliasList) after the loader has been registered because
        // otherwise calling the alias directly will cause the original core
        // class to be loaded for some reason.
        class_alias('Concrete\\Core\\Page\\Theme\\Theme', 'PageTheme');
    }

}
