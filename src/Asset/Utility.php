<?php

namespace Concrete\Package\AssetPipeline\Src\Asset;

use Concrete\Core\Application\Application;
use Exception;

/**
 * Implementation for the UtilityInterface.
 *
 * @author Antti Hukkanen <antti.hukkanen@mainiotech.fi>
 */
class Utility implements UtilityInterface
{

    /** @var Application */
    protected $app;

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * {@inheritDoc}
     */
    public function canFileContainCustomizableStyles($file)
    {
        $filters = $this->getFilterSettings();
        foreach ($filters as $key => $flt) {
            if (isset($flt['customizableStyles']) && $flt['customizableStyles'] &&
                    preg_match('#' . str_replace('#', '\#', $flt['applyTo']) . '#', $file)) {
                return true;
            }
        }
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getFileExtensionsForCustomizableStyles()
    {
        $filters = $this->getFilterSettings();

        $extensions = array();
        foreach ($filters as $key => $flt) {
            if (isset($flt['customizableStyles']) && $flt['customizableStyles']) {
                $extensions[] = isset($flt['fileExtension']) ? $flt['fileExtension'] : $key;
            }
        }
        return $extensions;
    }

    /**
     * {@inheritDoc}
     */
    public function getValueExtractorForFile($file, $urlroot)
    {
        $filters = $this->getFilterSettings();
        foreach ($filters as $key => $flt) {
            if (isset($flt['customizableStyles']) && $flt['customizableStyles'] &&
                    preg_match('#' . str_replace('#', '\#', $flt['applyTo']) . '#', $file)) {
                if (!$this->app->bound('assets/value/extractor/' . $key)) {
                    throw new Exception(t("Value extractor not set for key: %s", $key));
                }
                return $this->app->make('assets/value/extractor/' . $key, array($file, $urlroot));
            }
        }
    }

    /**
     * Returns the settings for all defined filters in the filter
     * settings repository.
     *
     * @return array
     */
    protected function getFilterSettings()
    {
        $rep = $this->app->make('Concrete\Package\AssetPipeline\Src\Asset\Filter\SettingsRepositoryInterface');
        return $rep->getAllFilterSettings();
    }

}
