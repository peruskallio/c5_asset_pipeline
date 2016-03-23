<?php

namespace Concrete\Package\AssetPipeline\Src\Asset;

interface ManagerInterface
{

    public function setFilter($key, array $options);

    public function getFilter($key);

    public function removeFilter($key);

    public function getFilters();

    /**
     * Tests whether the file can contain presets or not. This depends on the
     * filters set in the configuration and whether the filter has been defined
     * to provide presets.
     */
    public function canFileContainCustomizableStyles($file);

    public function getFileExtensionsForCustomizableStyles();

    public function getValueExtractorForFile($file, $urlroot);

}
