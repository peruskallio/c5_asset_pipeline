<?php
namespace Concrete\Package\AssetPipeline\Src\Asset\Assetic\Filter;

use Assetic\Asset\AssetInterface;
use Assetic\Factory\AssetFactory;
use Assetic\Filter\FilterInterface;
use Assetic\Util\LessUtils;

class JShrinkFilter implements FilterInterface
{

    public function filterLoad(AssetInterface $asset)
    {
    }

    public function filterDump(AssetInterface $asset)
    {
        $asset->setContent(\JShrink\Minifier::minify($asset->getContent()));
    }

}
