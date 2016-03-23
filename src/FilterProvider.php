<?php

namespace Concrete\Package\AssetPipeline\Src;

use Assetic\Filter\ScssphpFilter;
use Concrete\Core\Foundation\Service\Provider as ServiceProvider;
use Concrete\Package\AssetPipeline\Src\Asset\Assetic\Filter\CssMinFilter;
use Concrete\Package\AssetPipeline\Src\Asset\Assetic\Filter\JShrinkFilter;
use Concrete\Package\AssetPipeline\Src\Asset\Assetic\Filter\LessphpFilter;
use Concrete\Package\AssetPipeline\Src\Asset\Less\FunctionProvider as LessFunctionProvider;
use Concrete\Package\AssetPipeline\Src\Asset\Scss\FunctionProvider as ScssFunctionProvider;
use Concrete\Package\AssetPipeline\Src\StyleCustomizer\Style\Value\Extractor\Less as LessStyleValueExtractor;
use Concrete\Package\AssetPipeline\Src\StyleCustomizer\Style\Value\Extractor\Scss as ScssStyleValueExtractor;

class FilterProvider extends ServiceProvider
{

    public function register()
    {
        $config = $this->app->make('config');

        // Register Less filter & variable value extractor
        $this->app->bind('assets/filter/less', function ($app, $assets) use ($config) {
            $lessf = new LessphpFilter(
                array(
                    'cache_dir' => $config->get('concrete.cache.directory'),
                    'compress' => !!$config->get('concrete.theme.compress_preprocessor_output'),
                    'sourceMap' => !$config->get('concrete.theme.compress_preprocessor_output') && !!$config->get('concrete.theme.generate_less_sourcemap'),
                )
            );
            if ($config->get('app.asset_filter_options.less.legacy_url_support', false)) {
                $lessf->setBasePath('/' . ltrim($app['app_relative_path'], '/'));
                $lessf->setRelativeUrlPaths(true);
            }

            $variableList = $assets->getStyleSheetVariables();
            if (is_array($variableList)) {
                $lessf->setLessVariables($variableList);
            }

            $fp = new LessFunctionProvider();
            $fp->registerFor($lessf);

            return $lessf;
        });
        $this->app->bind('assets/value/extractor/less', function ($app, $args) {
            list($file, $urlroot) = array_pad((array) $args, 2, false);
            return new LessStyleValueExtractor($file, $urlroot);
        });

        // Register SCSS filter & variable value extractor
        $this->app->bind('assets/filter/scss', function ($app, $assets) use ($config) {
            // TODO: Is there a way to get source maps to the SCSS filter?
            $scssf = new ScssphpFilter();
            if ($config->get('concrete.theme.compress_preprocessor_output')) {
                $scssf->setFormatter('Leafo\ScssPhp\Formatter\Compressed');
            } else {
                $scssf->setFormatter('Leafo\ScssPhp\Formatter\Expanded');
            }

            $fp = new ScssFunctionProvider();
            $fp->registerFor($scssf);

            $variableList = $assets->getStyleSheetVariables();
            if (is_array($variableList)) {
                $scssf->setVariables($variableList);
            }

            return $scssf;
        });
        $this->app->bind('assets/value/extractor/scss', function ($app, $args) {
            list($file, $urlroot) = array_pad((array) $args, 2, false);
            return new ScssStyleValueExtractor($file, $urlroot);
        });

        // Register JS filter if JavaScript should be minified
        if (!!$config->get('app.asset_filter_options.js.compress', true)) {
            $this->app->bind('assets/filter/js', function ($app, $assets) {
                $jsf = new JShrinkFilter();
                return $jsf;
            });
        }

        // Register CSS filter if plain CSS should be minified
        if (!!$config->get('app.asset_filter_options.css.compress', true)) {
            $this->app->bind('assets/filter/css', function ($app, $assets) {
                $cmf = new CssMinFilter();
                return $cmf;
            });
        }
    }

    public function registerFilters()
    {
        $config = $this->app->make('config');
        $manager = $this->app->make('Concrete\Package\AssetPipeline\Src\Asset\ManagerInterface');
        $filters = $config->get('app.asset_filters');
        if (is_array($filters)) {
            foreach ($filters as $key => $options) {
                $manager->setFilter($key, $options);
            }
        }
    }

}
