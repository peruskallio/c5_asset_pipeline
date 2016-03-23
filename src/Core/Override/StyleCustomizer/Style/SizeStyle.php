<?php

namespace Concrete\Core\StyleCustomizer\Style;

use Concrete\Core\StyleCustomizer\Style\Value\SizeValue;
use Concrete\Package\AssetPipeline\Src\Core\Original\StyleCustomizer\Style\SizeStyle as CoreSizeStyle;
use Concrete\Package\AssetPipeline\Src\StyleCustomizer\Style\ExtractableStyleInterface;
use Concrete\Package\AssetPipeline\Src\StyleCustomizer\Style\Value\ExtractorInterface;

class SizeStyle extends CoreSizeStyle implements ExtractableStyleInterface
{

    public function getValuesFromExtractor(ExtractorInterface $extractor)
    {
        $values = array();

        $vars = $extractor->extractMatchingVariables('.+\-size');
        foreach ($vars as $name => $value) {
            $sv = static::parse($value, substr($name, 0, -strlen('-size')));
            if (is_object($sv)) {
                $values[] = $sv;
            }
        }

        return $values;
    }

    public static function parse($value, $variable = false)
    {
        $sv = new SizeValue($variable);
        // Definitive keyword sizes
        if (preg_match('/^(xx-small|x-small|small|medium|large|x-large|xx-large)$/i', $value)) {
            $sv->setSize($value);
        // Relative keyword sizes
        } elseif (preg_match('/^(larger|smaller)$/i', $value)) {
            $sv->setSize($value);
        // Hierarchical keyword sizes
        } elseif (preg_match('/^(inherit|initial|unset)$/i', $value)) {
            $sv->setSize($value);
        // Definitive sizes
        } elseif (preg_match('/^([1-9][0-9]*(\.[0-9]+)?)(px|pt|pc|mm|cm|in)$/i', $value, $matches)) {
            $sv->setSize(floatval($matches[1]));
            $sv->setUnit($matches[3]);
        // Relative sizes
        } elseif (preg_match('/^([1-9][0-9]*(\.[0-9]+)?)(em|rem|ex|%)?$/i', $value, $matches)) {
            $sv->setSize(floatval($matches[1]));
            if (isset($matches[3])) {
                $sv->setUnit($matches[3]);
            } else {
                $sv->setUnit('');
            }
        } else {
            return null;
        }

        return $sv;
    }

}
