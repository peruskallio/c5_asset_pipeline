<?php

namespace Concrete\Package\AssetPipeline\Src\Asset\Filter\Assetic;

use Assetic\Asset\AssetInterface;
use Assetic\Filter\FilterInterface;

/**
 * Provides an Assetic filter wrapper for the CssMin library.
 *
 * @author Antti Hukkanen <antti.hukkanen@mainiotech.fi>
 */
class CssMinFilter implements FilterInterface
{

    /**
     * {@inheritDoc}
     */
    public function filterLoad(AssetInterface $asset)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function filterDump(AssetInterface $asset)
    {
        $asset->setContent(\CssMin::minify($asset->getContent()));
    }

}
