<?php

namespace Concrete\Package\AssetPipeline\Src\Asset\Filter;

/**
 * Filter settings repository provides the available filter settings
 * throughout the system. The specific Assetic filters are applied
 * to the assets based on the filter settings provided by the
 * filter settings repository.
 *
 * @author Antti Hukkanen <antti.hukkanen@mainiotech.fi>
 */
interface SettingsRepositoryInterface
{

    /**
     * @param string $key
     * @param array $options
     */
    public function registerFilterSettings($key, array $options);

    /**
     * @param string $key
     *
     * @return array
     */
    public function getFilterSettings($key);

    /**
     * @param string $key
     */
    public function removeFilterSettings($key);

    /**
     * @return array
     */
    public function getAllFilterSettings();

}
