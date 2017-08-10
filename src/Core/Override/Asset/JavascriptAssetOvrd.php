<?php

namespace Concrete\Core\Asset;

use Concrete\Package\AssetPipeline\Src\Core\Original\Asset\JavascriptAsset as CoreJavascriptAsset;
use Config;

class JavascriptAssetOvrd extends CoreJavascriptAsset
{

    /**
     * {@inheritDoc}
     */
    public static function process($assets)
    {
        if (self::getOutputDirectory()) {
            $filename = '';
            $sourceFiles = array();
            for ($i = 0; $i < count($assets); $i++) {
                $asset = $assets[$i];
                $filename .= $asset->getAssetHashKey();
                $sourceFiles[] = $asset->getAssetURL();
            }
            $filename = sha1($filename);

            $app = Facade::getFacadeApplication();
            $ah = $app->make('helper/assets');
            $paths = array();
            foreach ($assets as $asset) {
                $paths[] = $asset->getAssetPath();
            }

            $relativePath = $ah->javascriptPath($paths, array(
                'name' => $filename,
                'skipDigest' => true,
            ));
            $assetDir = Config::get('concrete.cache.directory');

            $asset = new JavascriptAsset();
            $asset->setAssetURL($relativePath);
            $asset->setAssetPath($assetDir . $relativePath);
            $asset->setCombinedAssetSourceFiles($sourceFiles);

            return array($asset);
        }

        return $assets;
    }

}
