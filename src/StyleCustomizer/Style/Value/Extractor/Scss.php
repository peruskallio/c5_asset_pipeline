<?php
namespace Concrete\Package\AssetPipeline\Src\StyleCustomizer\Style\Value\Extractor;

use Concrete\Package\AssetPipeline\Src\StyleCustomizer\Style\Value\Extractor;

defined('C5_EXECUTE') or die("Access Denied.");

class Scss extends Extractor
{

    public function extractPresetName()
    {
        $name = $this->extractFirstMatchingValue(static::PRESET_RULE_NAME);
        return trim($name, "'\"");
    }

    public function extractPresetIcon()
    {
        foreach ($this->getRules() as $name => $rule) {
            if ($name == static::PRESET_RULE_ICON && $rule[0] == 'fncall') {
                if (is_array($rule[2]) && count($rule[2]) == 3) {
                    list($c1, $c2, $c3) = $rule[2];
                    $col1 = $col2 = $col3 = null;
                    foreach (array(1, 2, 3) as $n) {
                        $varname = 'c' . $n;
                        $var = $$varname;
                        if (is_array($var) && is_array($var[1]) && $var[1][0] == 'color') {
                            list($r, $g, $b) = array_splice($var[1], 1);
                            $sc = "col" . $n;
                            $$sc = sprintf("rgb(%d, %d, %d)", $r, $g, $b);
                        }
                    }
                    return array($col1, $col2, $col3);
                }
            }
        }
    }

    public function extractFontsFile()
    {
        $file = $this->extractFirstMatchingValue(static::PRESET_RULE_FONTS_FILE);
        return trim($file, "'\"");
    }

    public function extractFirstMatchingValue($find)
    {
        foreach($this->getRules() as $name => $rule) {
            if ($name == $find) {
                return $this->ruleToCss($rule);
            }
        }
    }

    public function extractMatchingVariables($match)
    {
        $values = array();
        $i = 0;
        foreach($this->getRules() as $name => $rule) {
            if (preg_match('/' . $match . '/i',  $name, $matches)) {
                $val = $this->ruleToCss($rule);
                if ($val !== null) {
                    $values[$name] = $val;
                }
            }
        }
        return $values;
    }

    protected function getRules()
    {
        if (!isset($this->rules)) {
            $code = file_get_contents($this->file);
            $sp = new \Leafo\ScssPhp\Parser($this->file);
            $ret = $sp->parse($code);

            $this->rules = array();
            foreach ($ret->children as $child) {
                if ($child[0] == 'assign' && $child[1][0] == 'var') {
                    $name = $child[1][1];
                    $this->rules[$name] = $child[2];
                }
            }
        }
        return $this->rules;
    }

    protected function ruleToCss($rule)
    {
        if (is_array($rule)) {
            if ($rule[0] == 'color') {
                // RGB order reversed below because in the Leafo SCSS color
                // format the indexes in the array are not in order which makes
                // red to be the last color and blue the first color.
                list($b, $g, $r) = array_splice($rule, 1);
                return sprintf('rgb(%d, %d, %d)', $r, $g, $b);
            } elseif ($rule[0] == 'keyword') {
                return $rule[1];
            } elseif ($rule[0] == 'string') {
                return $rule[1] . $rule[2][0] . $rule[1];
            } elseif ($rule[0] == 'list') {
                $list = array();
                foreach ($rule[2] as $val) {
                    $list[] = $this->ruleToCss($val);
                }
                return implode($rule[1] . ' ', $list);
            } else {
                throw new \Exception(t("Unknown CSS rule type: " . $rule[0]));
            }
        } elseif ($rule instanceof \Leafo\ScssPhp\Node\Number) {
            return $rule->output();
        }
    }

}