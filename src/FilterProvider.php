<?php
namespace Concrete\Package\AssetPipeline\Src;

use Concrete\Core\Foundation\Service\Provider as ServiceProvider;
use Config;
use Core;

defined('C5_EXECUTE') or die("Access Denied.");

class FilterProvider extends ServiceProvider
{

    public function register()
    {
        // Register the filter manager singleton
        $this->app->singleton('assets/manager', '\Concrete\Package\AssetPipeline\Src\Asset\Manager');

        // Register Less filter & variable value extractor
        Core::bind('assets/filter/less', function($app, $assets) {
            $lessf = new \Concrete\Package\AssetPipeline\Src\Asset\Assetic\Filter\LessphpFilter(
                array(
                    'cache_dir' => Config::get('concrete.cache.directory'),
                    'compress' => !!Config::get('concrete.theme.compress_preprocessor_output'),
                    'sourceMap' => !Config::get('concrete.theme.compress_preprocessor_output') && !!Config::get('concrete.theme.generate_less_sourcemap'),
                )
            );
            if (Config::get('assets.less.legacy_url_support', false)) {
                $lessf->setBasePath('/' . ltrim($app['app_relative_path'], '/'));
                $lessf->setRelativeUrlPaths(true);
            }

            $variableList = $assets->getStyleSheetVariables();
            if (is_array($variableList)) {
                $lessf->setLessVariables($variableList);
            }

            $fp = new \Concrete\Package\AssetPipeline\Src\Asset\Less\FunctionProvider();
            $fp->registerFor($lessf);

            return $lessf;
        });
        Core::bind('assets/value/extractor/less', function($app, $args) {
            list($file, $urlroot) = array_pad((array) $args, 2, false);
            return new \Concrete\Package\AssetPipeline\Src\StyleCustomizer\Style\Value\Extractor\Less($file, $urlroot);
        });

        // Register SCSS filter & variable value extractor
        Core::bind('assets/filter/scss', function($app, $assets) {
            $scssf = new \Assetic\Filter\ScssphpFilter();
            if (Config::get('concrete.theme.compress_preprocessor_output')) {
                $scssf->setFormatter('Leafo\ScssPhp\Formatter\Compressed');
            } else {
                $scssf->setFormatter('Leafo\ScssPhp\Formatter\Expanded');
            }

            $fp = new \Concrete\Package\AssetPipeline\Src\Asset\Scss\FunctionProvider();
            $fp->registerFor($scssf);

            $variableList = $assets->getStyleSheetVariables();
            if (is_array($variableList)) {
                $scssf->setVariables($variableList);
            }

            return $scssf;
        });
        Core::bind('assets/value/extractor/scss', function($app, $args) {
            list($file, $urlroot) = array_pad((array) $args, 2, false);
            return new \Concrete\Package\AssetPipeline\Src\StyleCustomizer\Style\Value\Extractor\Scss($file, $urlroot);
        });

        // Register JS filter if JavaScript should be compressed
        if (!!Config::get('assets.js.compress', true)) {
            Core::bind('assets/filter/js', function($app, $assets) {
                $jsf = new \Concrete\Package\AssetPipeline\Src\Asset\Assetic\Filter\JShrinkFilter();
                return $jsf;
            });
        }
    }

    public function setFilters()
    {
        $manager = Core::make('assets/manager');
        $filters = Config::get('app.asset_filters');
        if (is_array($filters)) {
            foreach ($filters as $key => $options) {
                $manager->setFilter($key, $options);
            }
        }
    }

}
