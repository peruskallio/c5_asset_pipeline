<?php
namespace Concrete\Package\AssetPipeline\Src\Asset;

defined('C5_EXECUTE') or die("Access Denied.");

class Manager
{

    protected $filters = array();

    public function setFilter($key, array $options)
    {
        $this->filters[$key] = $options;
    }

    public function getFilter($key)
    {
        return $this->filters[$key];
    }

    public function removeFilter($key)
    {
        unset($this->filters[$key]);
    }

    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Tests whether the file can contain presets or not. This depends on the
     * filters set in the configuration and whether the filter has been defined
     * to provide presets.
     */
    public function canFileContainCustomizableStyles($file)
    {
        $filters = $this->getFilters();
        foreach ($filters as $key => $flt) {
            if (isset($flt['customizableStyles']) && $flt['customizableStyles'] &&
                    preg_match('#' . str_replace('#', '\#', $flt['applyTo']) . '#', $file)) {
                return true;
            }
        }
        return false;
    }

    public function getFileExtensionsForCustomizableStyles()
    {
        $filters = $this->getFilters();

        $extensions = array();
        foreach ($filters as $key => $flt) {
            if (isset($flt['customizableStyles']) && $flt['customizableStyles']) {
                $extensions[] = $key;
            }
        }
        return $extensions;
    }

    public function getValueExtractorForFile($file, $urlroot)
    {
        $filters = $this->getFilters();
        foreach ($filters as $key => $flt) {
            if (isset($flt['customizableStyles']) && $flt['customizableStyles'] &&
                    preg_match('#' . str_replace('#', '\#', $flt['applyTo']) . '#', $file)) {
                if (!Core::bound('assets/value/extractor/' . $key)) {
                    throw new \Exception(t("Value extractor not set for key: %s", $key));
                }
                return Core::make('assets/value/extractor/' . $key, array($file, $urlroot));
            }
        }
    }

}
