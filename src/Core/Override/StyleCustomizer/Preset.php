<?php

namespace Concrete\Core\StyleCustomizer;

use Concrete\Core\StyleCustomizer\Style\ColorStyle;
use Concrete\Core\StyleCustomizer\Style\ValueList as StyleValueList;
use Concrete\Package\AssetPipeline\Src\Core\Original\StyleCustomizer\Preset as CorePreset;
use Core;

class Preset extends CorePreset
{

    /**
     * {@inheritDoc}
     */
    public function getStyleValueList()
    {
        if (!isset($this->styleValueList)) {
            $this->styleValueList = StyleValueList::loadFromFile($this->file, $this->urlroot);
        }
        return $this->styleValueList;
    }

    /**
     * {@inheritDoc}
     */
    public static function getFromFile($file, $urlroot)
    {
        $extractor = Core::make('Concrete\Package\AssetPipeline\Src\Asset\UtilityInterface')->getValueExtractorForFile($file, $urlroot);
        if (!is_object($extractor)) {
            return null;
        }

        $o = new static();
        $o->file = $file;
        $o->urlroot = $urlroot;
        $o->filename = basename($file);
        $o->handle = substr($o->filename, 0, strrpos($o->filename, '.'));

        $o->name = $extractor->extractPresetName();
        if (is_array($color = $extractor->extractPresetIcon())) {
            $cv1 = ColorStyle::parse($color[0]);
            $cv2 = ColorStyle::parse($color[1]);
            $cv3 = ColorStyle::parse($color[2]);
            $o->color1 = $cv1;
            $o->color2 = $cv2;
            $o->color3 = $cv3;
        }

        return $o;
    }

}
