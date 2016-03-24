<?php

namespace Concrete\Package\AssetPipeline\Src\Asset\Filter;

use Assetic\Filter\FilterInterface;

/**
 * Interface for preprocessor function providers.
 *
 * @author Antti Hukkanen <antti.hukkanen@mainiotech.fi>
 */
interface FunctionProviderInterface
{

    /**
     * Registers the preprocessor functions for the given filter object.
     * These registered functions can be used directly within the
     * preprocessing language that is parsed by the given filter.
     *
     * @param FilterInterface $filter
     */
    public function registerFor(FilterInterface $filter);

}
