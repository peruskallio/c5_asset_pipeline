<?php

namespace Concrete\Core\StyleCustomizer\Style;

use Concrete\Core\StyleCustomizer\Style\ColorStyle;
use Concrete\Core\StyleCustomizer\Style\SizeStyle;
use Concrete\Core\StyleCustomizer\Style\Value\ColorValue;
use Concrete\Core\StyleCustomizer\Style\Value\SizeValue;
use Concrete\Core\StyleCustomizer\Style\Value\TypeValue;
use Concrete\Package\AssetPipeline\Src\Core\Original\StyleCustomizer\Style\TypeStyle as CoreTypeStyle;
use Concrete\Package\AssetPipeline\Src\StyleCustomizer\Style\ExtractableStyleInterface;
use Concrete\Package\AssetPipeline\Src\StyleCustomizer\Style\Value\ExtractorInterface;

class TypeStyle extends CoreTypeStyle implements ExtractableStyleInterface
{

    public function getValuesFromExtractor(ExtractorInterface $extractor)
    {
        $values = array();

        $matchers = array(
            'type-font-family' => 'setFontFamily',
            'type-font-weight' => 'setFontWeight',
            'type-text-decoration' => 'setTextDecoration',
            'type-text-transform' => 'setTextTransform',
            'type-font-style' => 'setFontStyle',
            // type-color values should be instances of ColorStyle
            'type-color' => 'setColor',
            // The following values should be instances of SizeStyle
            'type-font-size' => 'setFontSize',
            'type-letter-spacing' => 'setLetterSpacing',
            'type-line-height' => 'setLineHeight',
        );
        foreach ($matchers as $find => $setFunc) {
            $vars = $extractor->extractMatchingVariables('.+\-' . $find);
            foreach ($vars as $name => $value) {
                if ($find == 'type-color') {
                    $cv = ColorStyle::parse($value);
                    $value = $cv instanceof ColorValue ? $cv : null;
                } elseif (in_array($find, array('type-font-size', 'type-letter-spacing', 'type-line-height'))) {
                    $sv = SizeStyle::parse($value);
                    $value = $sv instanceof SizeValue ? $sv : null;
                }
                if ($value !== null) {
                    $name = substr($name, 0, -strlen($find));
                    if (!isset($values[$name])) {
                        $values[$name] = new TypeValue($name);
                    }
                    call_user_func(array($values[$name], $setFunc), $value);
                }
            }
        }

        return $values;
    }

}
