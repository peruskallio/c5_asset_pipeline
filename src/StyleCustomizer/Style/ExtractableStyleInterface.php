<?php

namespace Concrete\Package\AssetPipeline\Src\StyleCustomizer\Style;

use Concrete\Package\AssetPipeline\Src\StyleCustomizer\Style\Value\ExtractorInterface;

interface ExtractableStyleInterface
{

    public function getValuesFromExtractor(ExtractorInterface $extractor);

}