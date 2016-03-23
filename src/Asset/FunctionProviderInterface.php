<?php

namespace Concrete\Package\AssetPipeline\Src\Asset;

use Assetic\Filter\FilterInterface;

interface FunctionProviderInterface
{

    public function registerFor(FilterInterface $filter);

}
