<?php

namespace Concrete\Package\AssetPipeline\Src\Asset;

use Assetic\Filter\FilterInterface;
use Concrete\Core\Page\Page;

class AbstractFunctionProvider implements FunctionProviderInterface
{

    protected $currentTheme;

    public function __construct()
    {
        $c = Page::getCurrentPage();
        $this->currentTheme = $c->getCollectionThemeObject();
    }

    abstract public function registerFor(FilterInterface $filter);

    protected function getCurrentTheme()
    {
        return $this->currentTheme;
    }

}
