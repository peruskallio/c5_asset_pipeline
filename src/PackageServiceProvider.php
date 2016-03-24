<?php

namespace Concrete\Package\AssetPipeline\Src;

use Assetic\AssetManager;
use Assetic\FilterManager;
use Assetic\Factory\AssetFactory;
use Concrete\Core\Foundation\Service\Provider as ServiceProvider;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Theme\Theme;
use Concrete\Package\AssetPipeline\Src\Asset\Utility as AssetUtility;
use Symfony\Component\ClassLoader\MapClassLoader;

class PackageServiceProvider extends ServiceProvider
{

    protected $pkgHandle = 'asset_pipeline';

    public function register()
    {
        // Register the asset manager singleton
        $this->app->bindShared('Concrete\Package\AssetPipeline\Src\Asset\UtilityInterface', function ($app) {
            return new AssetUtility($app);
        });

        // Register filter settings repository singleton
        $this->app->singleton(
            'Concrete\Package\AssetPipeline\Src\Asset\Filter\SettingsRepositoryInterface',
            'Concrete\Package\AssetPipeline\Src\Asset\Filter\SettingsRepository'
        );

        // Register Assetic's AssetFactory
        $this->app->bindShared('Assetic\Factory\AssetFactory', function ($app) {
            $am = new AssetManager();
            $fm = new FilterManager();

            $factory = new AssetFactory(DIR_BASE);
            $factory->setAssetManager($am);
            $factory->setFilterManager($fm);

            return $factory;
        });

        $singletons = array(
            'helper/assets' => '\Concrete\Package\AssetPipeline\Src\Service\Assets',
        );

        foreach($singletons as $key => $value) {
            $this->app->singleton($this->pkgHandle . '/' . $key, $value);
        }
    }

    public function registerEvents()
    {
        $app = $this->app;
        $events = $this->app->make('director');

        // Add the sitemap icons listener
        $events->addListener(
            'on_before_render', function($event) use ($app) {
                $c = Page::getCurrentPage();
                $view = $event->getArgument('view');
                $assets = $app->make('asset_pipeline/helper/assets');
                $theme = null;
                if (is_object($c)) {
                    $theme = $c->getCollectionThemeObject();
                } else {
                    $theme = Theme::getSiteTheme();
                }
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

    public function registerConfigurations()
    {
        $config = $this->app->make('config');

        // The filter definitions can be overridden by the site configs, so
        // first check whether they are set or not.
        if (!$config->has('app.asset_filters')) {
            // Set the default filter options
            $filters = array(
                'less' => array(
                    'applyTo' => '\.less$',
                    'customizableStyles' => true,
                ),
                'scss' => array(
                    'applyTo' => '\.scss$',
                    'customizableStyles' => true,
                ),
            );
            if (!!$config->get('app.asset_filter_options.js.compress', true)) {
                $filters['jshrink'] = array(
                    'applyTo' => '\.js$',
                );
            }
            if (!!$config->get('app.asset_filter_options.css.compress', true)) {
                $filters['cssmin'] = array(
                    'applyTo' => '\.css$',
                );
            }
            $config->set('app.asset_filters', $filters);
        }
    }

    public function registerOverrides()
    {
        // Core overrides
        $dir = DIR_PACKAGES . '/' . $this->pkgHandle;
        $loader = new MapClassLoader(array(
            'Concrete\\Core\\Asset\\CssAsset'
                => $dir . '/src/Core/Override/Asset/CssAsset.php',
            'Concrete\\Core\\Asset\\JavascriptAsset'
                => $dir . '/src/Core/Override/Asset/JavascriptAsset.php',
            'Concrete\\Core\\Page\\Theme\\Theme'
                => $dir . '/src/Core/Override/Page/Theme/Theme.php',
            'Concrete\\Core\\StyleCustomizer\\Preset'
                => $dir . '/src/Core/Override/StyleCustomizer/Preset.php',
            'Concrete\\Core\\StyleCustomizer\\Stylesheet'
                => $dir . '/src/Core/Override/StyleCustomizer/Stylesheet.php',
            'Concrete\\Core\\StyleCustomizer\\Style\\ColorStyle'
                => $dir . '/src/Core/Override/StyleCustomizer/Style/ColorStyle.php',
            'Concrete\\Core\\StyleCustomizer\\Style\\ImageStyle'
                => $dir . '/src/Core/Override/StyleCustomizer/Style/ImageStyle.php',
            'Concrete\\Core\\StyleCustomizer\\Style\\SizeStyle'
                => $dir . '/src/Core/Override/StyleCustomizer/Style/SizeStyle.php',
            'Concrete\\Core\\StyleCustomizer\\Style\\TypeStyle'
                => $dir . '/src/Core/Override/StyleCustomizer/Style/TypeStyle.php',
            'Concrete\\Core\\StyleCustomizer\\Style\\ValueList'
                => $dir . '/src/Core/Override/StyleCustomizer/Style/ValueList.php',
        ));
        $loader->register(true);

        // For some reason this has to be called MANUALLY (and not through the
        // c5 ClassAliasList) after the loader has been registered because
        // otherwise calling the alias directly will cause the original core
        // class to be loaded for some reason.
        class_alias('Concrete\\Core\\Page\\Theme\\Theme', 'PageTheme');
    }

}
