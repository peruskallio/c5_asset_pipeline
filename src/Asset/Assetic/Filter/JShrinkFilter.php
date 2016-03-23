<?php

namespace Concrete\Package\AssetPipeline\Src\Asset\Assetic\Filter;

use Assetic\Asset\AssetInterface;
use Assetic\Filter\FilterInterface;

/**
 * Provides an Assetic filter wrapper for the JShrink library.
 *
 * @author Antti Hukkanen <antti.hukkanen@mainiotech.fi>
 */
class JShrinkFilter implements FilterInterface
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
        $asset->setContent(\JShrink\Minifier::minify($asset->getContent()));
    }

}
