<?php

namespace Concrete\Package\AssetPipeline\Src\Asset;

/**
 * Filter settings repository implementation that stores the filter
 * settings in a simple local array backend.
 *
 * @author Antti Hukkanen <antti.hukkanen@mainiotech.fi>
 */
class FilterSettingsRepository implements FilterSettingsRepositoryInterface
{

    protected $filters = array();

    /**
     * {@inheritDoc}
     */
    public function registerFilterSettings($key, array $options)
    {
        $this->filters[$key] = $options;
    }

    /**
     * {@inheritDoc}
     */
    public function getFilterSettings($key)
    {
        return $this->filters[$key];
    }

    /**
     * {@inheritDoc}
     */
    public function removeFilterSettings($key)
    {
        unset($this->filters[$key]);
    }

    /**
     * {@inheritDoc}
     */
    public function getAllFilterSettings()
    {
        return $this->filters;
    }

}
