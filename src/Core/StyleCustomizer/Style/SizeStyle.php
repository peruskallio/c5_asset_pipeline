<?php
namespace Concrete\Core\StyleCustomizer\Style;
use \Concrete\Core\StyleCustomizer\Style\Value\SizeValue;
use Less_Tree_Dimension;

class SizeStyle extends Style
{

    public function render($value = false)
    {
        $r = \Concrete\Core\Http\ResponseAssetGroup::get();
        $r->requireAsset('core/style-customizer');

        $strOptions = '';
        $i = 0;
        if (is_object($value)) {
            $options['unit'] = $value->getUnit();
            $options['value'] = $value->getSize();
        }
        $options['inputName'] = $this->getVariable();
        $strOptions = json_encode($options);
        print '<span class="ccm-style-customizer-display-swatch-wrapper" data-size-selector="' . $this->getVariable() . '"></span>';
        print "<script type=\"text/javascript\">";
        print "$(function() { $('span[data-size-selector=" . $this->getVariable() . "]').concreteSizeSelector({$strOptions}); });";
        print "</script>";
    }

    public function getValueFromRequest(\Symfony\Component\HttpFoundation\ParameterBag $request)
    {
        $size = $request->get($this->getVariable());
        $sv = new SizeValue($this->getVariable());
        $sv->setSize($size['size']);
        $sv->setUnit($size['unit']);
        return $sv;
    }

    public function getValuesFromVariables($extractor)
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

