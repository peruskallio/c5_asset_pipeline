<?php

namespace Concrete\Package\AssetPipeline\Src\Asset;

use Assetic\Filter\FilterInterface;
use Concrete\Core\Page\Page;

/**
 * An abstract function provider for the preprocessors that provides
 * some helper methods for all function providers.
 *
 * @author Antti Hukkanen <antti.hukkanen@mainiotech.fi>
 */
class AbstractFunctionProvider implements FunctionProviderInterface
{

    protected $currentTheme;

    public function __construct()
    {
        $c = Page::getCurrentPage();
        $this->currentTheme = $c->getCollectionThemeObject();
    }

    /**
     * {@inheritDoc}
     */
    abstract public function registerFor(FilterInterface $filter);

    /**
     * Returns the current theme
     *
     * @return \Concrete\Core\Page\Theme\Theme
     */
    protected function getCurrentTheme()
    {
        return $this->currentTheme;
    }

}
