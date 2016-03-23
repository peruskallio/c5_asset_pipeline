<?php

namespace Concrete\Package\AssetPipeline\Src\Asset;

/**
 * Asset utility provides available some utility methods to be used
 * accross the core related to the preprocessor filters.
 *
 * @author Antti Hukkanen <antti.hukkanen@mainiotech.fi>
 */
interface UtilityInterface
{

    /**
     * Tests whether the file can contain presets or not. This depends on the
     * filters set in the configuration and whether the filter has been defined
     * to provide presets.
     *
     * @param string $file
     *
     * @return bool
     */
    public function canFileContainCustomizableStyles($file);

    /**
     * @return array
     */
    public function getFileExtensionsForCustomizableStyles();

    /**
     * @param string $file
     * @param string $urlroot
     *
     * @return ExtractorInterface
     */
    public function getValueExtractorForFile($file, $urlroot);

}
