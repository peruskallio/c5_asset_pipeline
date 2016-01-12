<?php
namespace Concrete\Core\StyleCustomizer;

use Concrete\Core\StyleCustomizer\Style\ColorStyle;
use Config;
use Core;

class Preset
{

    protected $filename;
    protected $name;
    protected $styleValueList;

    /**
     * Gets the style value list object for this preset.
     * @return \Concrete\Core\StyleCustomizer\Style\ValueList
     */
    public function getStyleValueList()
    {
        if (!isset($this->styleValueList)) {
            $this->styleValueList = \Concrete\Core\StyleCustomizer\Style\ValueList::loadFromFile($this->file, $this->urlroot);
        }
        return $this->styleValueList;
    }

    public function getPresetFilename()
    {
        return $this->filename;
    }

    public function getPresetName()
    {
        return $this->name;
    }

    /** Returns the display name for this preset (localized and escaped accordingly to $format)
     * @param string $format = 'html'
     *   Escape the result in html format (if $format is 'html').
     *   If $format is 'text' or any other value, the display name won't be escaped.
     * @return string
     */
    public function getPresetDisplayName($format = 'html')
    {
        $value = tc('PresetName', $this->getPresetName());
        switch($format) {
            case 'html':
                return h($value);
            case 'text':
            default:
                return $value;
        }
    }

    public function isDefaultPreset()
    {
        return $this->filename == FILENAME_STYLE_CUSTOMIZER_DEFAULT_PRESET_NAME;
    }

    public function getPresetHandle()
    {
        return $this->handle;
    }

    public function getPresetColor1()
    {
        return $this->color1;
    }

    public function getPresetColor2()
    {
        return $this->color2;
    }

    public function getPresetColor3()
    {
        return $this->color3;
    }

    public function getPresetIconHTML()
    {
        $html = '<ul class="ccm-style-preset-icon">';
        $html .= '<li style="background-color: ' . $this->getPresetColor1()->toStyleString() . '"></li>';
        $html .= '<li style="background-color: ' . $this->getPresetColor2()->toStyleString() . '"></li>';
        $html .= '<li style="background-color: ' . $this->getPresetColor3()->toStyleString() . '"></li>';
        $html .= '</ul>';
        return $html;
    }

    /**
     * @return Preset|null
     */
    public static function getFromFile($file, $urlroot)
    {
        $extractor = Core::make('assets/manager')->getValueExtractorForFile($file, $urlroot);
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