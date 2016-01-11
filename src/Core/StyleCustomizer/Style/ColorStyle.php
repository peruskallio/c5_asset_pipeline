<?php
namespace Concrete\Core\StyleCustomizer\Style;

use Core;
use \Concrete\Core\StyleCustomizer\Style\Value\ColorValue;
use Less_Tree_Color;
use Less_Tree_Call;
use Less_Tree_Dimension;
use View;
use Request;
use \Concrete\Core\Http\Service\Json;

class ColorStyle extends Style
{

    // Regex matchers for specific color values in the CSS
    const RGBA_MATCH = '/^(rgba?)\(\s*([0-9]+)\s*,\s*([0-9]+)\s*,\s*([0-9]+)\s*(,\s*([0,1]?\.[0-9]+)\s*)?\)/i';
    const HSLA_MATCH = '/^(hsla?)\(\s*([0-9]+)\s*,\s*([0-9]+%?)\s*,\s*([0-9]+%?)\s*(,\s*([0,1]?\.[0-9]+)\s*)?\)/i';

    public function render($value = false)
    {
        $color = '';
        if ($value) {
            $color = $value->toStyleString();
        }
        $inputName = $this->getVariable();
        $r = Request::getInstance();
        if ($r->request->has($inputName)) {
            $color = h($r->request->get($inputName));
        }

        $view = View::getInstance();
        $view->requireAsset('core/colorpicker');

        $json = new Json();

        print "<input type=\"text\" name=\"{$inputName}[color]\" value=\"{$color}\" id=\"ccm-colorpicker-{$inputName}\" />";
        print "<script type=\"text/javascript\">";
        print "$(function() { $('#ccm-colorpicker-{$inputName}').spectrum({
            showInput: true,
            showInitial: true,
            preferredFormat: 'rgb',
            allowEmpty: true,
            className: 'ccm-widget-colorpicker',
            showAlpha: true,
            value: " . $json->encode($color) . ",
            cancelText: " . $json->encode(t('Cancel')) . ",
            chooseText: " . $json->encode(t('Choose')) . ",
            clearText: " . $json->encode(t('Clear Color Selection')) . ",
            change: function() {ConcreteEvent.publish('StyleCustomizerControlUpdate');}
        });});";
        print "</script>";
    }

    public function getValueFromRequest(\Symfony\Component\HttpFoundation\ParameterBag $request)
    {
        $color = $request->get($this->getVariable());
        if (!$color['color']) { // transparent
            return false;
        }
        $cv = new \Primal\Color\Parser($color['color']);
        $result = $cv->getResult();
        $alpha = false;
        if ($result->alpha && $result->alpha < 1) {
            $alpha = $result->alpha;
        }
        $cv = new ColorValue($this->getVariable());
        $cv->setRed($result->red);
        $cv->setGreen($result->green);
        $cv->setBlue($result->blue);
        $cv->setAlpha($alpha);
        return $cv;
    }

    public function getValuesFromVariables($extractor)
    {
        $values = array();

        $vars = $extractor->extractMatchingVariables('.+\-color');
        foreach ($vars as $name => $value) {
            $cv = static::parse($value, substr($name, 0, -strlen('-color')));
            if (is_object($cv)) {
                $values[] = $cv;
            }
        }

        return $values;
    }

    public static function parse($value, $variable = false)
    {
        if (preg_match('/^transparent$/i', $value)) {
            return false;
        }

        $r = $g = $b = 0;
        $a = null;

        if (preg_match('/^#([0-9a-f]{3,6})/', $value, $matches)) {
            $hex = $matches[1];
            list($r, $g, $b) = static::hexToRgb($hex);
        } elseif (preg_match(static::RGBA_MATCH, $value, $matches)) {
            list($r, $g, $b) = array_slice($matches, 2, 3);
            if (isset($matches[6])) {
                $a = floatval($matches[6]);
            } elseif (strtolower($matches[1]) === 'rgba') {
                // Invalid CSS value
                return false;
            }
        } elseif (preg_match(static::HSLA_MATCH, $value, $matches)) {
            list($h, $s, $l) = array_slice($matches, 2, 3);

            if ($s != 0 && strrpos($s, '%') === false) {
                // Invalid CSS value
                return false;
            }
            if ($l != 0 && strrpos($l, '%') === false) {
                // Invalid CSS value
                return false;
            }
            $s = $s == 0 ? 0 : rtrim($s, '%');
            $l = $l == 0 ? 0 : rtrim($l, '%');

            if (isset($matches[6])) {
                $a = floatval($matches[6]);
            } elseif (strtolower($matches[1]) === 'hsla') {
                // Invalid CSS value
                return false;
            }

            list($r, $g, $b) = static::hslToRgb($h, $s, $l);
        } else {
            if (class_exists('Leafo\ScssPhp\Colors')) {
                $colors = \Leafo\ScssPhp\Colors::$cssColors;
                if (isset($colors[$value])) {
                    list($r, $g, $b) = explode(',', $colors[$value]);
                } else {
                    // Invalid CSS value
                    return false;
                }
            } else {
                // Invalid CSS value
                return false;
            }
        }

        $cv = new ColorValue($variable);
        $cv->setRed(intval($r));
        $cv->setGreen(intval($g));
        $cv->setBlue(intval($b));
        if ($a !== null) {
            $cv->setAlpha(floatval($a));
        }
        return $cv;
    }

    /**
     * Credits:
     * http://bavotasan.com/2011/convert-hex-color-to-rgb-using-php/
     */
    public static function hexToRgb($hex)
    {
        $hx = ltrim($hex, '#');
        $r = $g = $b = null;

        if (strlen($hex) == 3) {
            $r = hexdec(str_repeat(substr($hx, 0, 1), 2));
            $g = hexdec(str_repeat(substr($hx, 1, 1), 2));
            $b = hexdec(str_repeat(substr($hx, 2, 1), 2));
        } elseif (strlen($hex) == 6) {
            $r = hexdec(substr($hx, 0, 2));
            $g = hexdec(substr($hx, 2, 2));
            $b = hexdec(substr($hx, 4, 2));
        } else {
            throw new \Exception(t("Invalid hexadecimal number length for string: %s", $hex));
        }

        return array($r, $g, $b);
    }

    /**
     * Credits:
     * http://stackoverflow.com/questions/2353211/hsl-to-rgb-color-conversion
     */
    public static function hslToRgb($h, $s, $l)
    {
        // Convert the values into set [0, 1]
        $h = $h / 360;
        $s = min(100, max(0, $s)) / 100;
        $l = min(100, max(0, $l)) / 100;

        $r = $g = $b = null;

        if ($s == 0) {
            $r = $g = $b = $l;
        } else {
            $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
            $p = 2 * $l - $q;
            $r = static::hueToRgb($p, $q, $h + 1/3);
            $g = static::hueToRgb($p, $q, $h);
            $b = static::hueToRgb($p, $q, $h - 1/3);
        }

        return array(round($r * 255), round($g * 255), round($b * 255));
    }

    /**
     * Credits:
     * http://stackoverflow.com/questions/2353211/hsl-to-rgb-color-conversion
     */
    public static function hueToRgb($p, $q, $t)
    {
        if ($t < 0) $t += 1;
        if ($t > 1) $t -= 1;
        if ($t < 1/6) return $p + ($q - $p) * 6 * $t;
        if ($t < 1/2) return $q;
        if ($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
        return $p;
    }

}