<?php

namespace Concrete\Package\AssetPipeline\Src\Asset;

class Manager implements ManagerInterface
{

    protected $filters = array();

    /**
     * {@inheritDoc}
     */
    public function setFilter($key, array $options)
    {
        $this->filters[$key] = $options;
    }

    /**
     * {@inheritDoc}
     */
    public function getFilter($key)
    {
        return $this->filters[$key];
    }

    /**
     * {@inheritDoc}
     */
    public function removeFilter($key)
    {
        unset($this->filters[$key]);
    }

    /**
     * {@inheritDoc}
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * {@inheritDoc}
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

    /**
     * {@inheritDoc}
     */
    public function getFileExtensionsForCustomizableStyles()
    {
        $filters = $this->getFilters();

        $extensions = array();
        foreach ($filters as $key => $flt) {
            if (isset($flt['customizableStyles']) && $flt['customizableStyles']) {
                $extensions[] = isset($flt['fileExtension']) ? $flt['fileExtension'] : $key;
            }
        }
        return $extensions;
    }

    /**
     * {@inheritDoc}
     */
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
