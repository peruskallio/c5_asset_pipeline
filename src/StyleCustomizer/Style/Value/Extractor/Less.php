<?php
namespace Concrete\Package\AssetPipeline\Src\StyleCustomizer\Style\Value\Extractor;

use Concrete\Package\AssetPipeline\Src\StyleCustomizer\Style\Value\Extractor;
use Less_Parser;
use Less_Tree_Call;

defined('C5_EXECUTE') or die("Access Denied.");

class Less extends Extractor
{

    public function extractPresetName()
    {
        return $this->extractFirstMatchingValue(static::PRESET_RULE_NAME);
    }

    public function extractPresetIcon()
    {
        foreach ($this->getRules() as $rule) {
            if ($rule->name == '@' . static::PRESET_RULE_ICON) {
                $method = $rule->value->value[0]->value[0];
                $ref = new \ReflectionClass($method);
                $prop = $ref->getProperty('name');
                $prop->setAccessible(true);
                $name = $prop->getValue($method);
                if ($method instanceof Less_Tree_Call && $name == static::PRESET_RULE_ICON_FUNCTION) {
                    $prop = $ref->getProperty('args');
                    $prop->setAccessible(true);
                    $args = $prop->getValue($method);
                    return array($args[0]->toCss(), $args[1]->toCss(), $args[2]->toCss());
                }
            }
        }
    }

    public function extractFontsFile()
    {
        return $this->extractFirstMatchingValue(static::PRESET_RULE_FONTS_FILE);
    }

    public function extractFirstMatchingValue($find)
    {
        foreach($this->getRules() as $rule) {
            if ($rule->name == '@' . $find) {
                return $rule->value->value[0]->value[0]->value;
            }
        }
    }

    public function extractMatchingVariables($match)
    {
        $values = array();
        foreach($this->getRules() as $rule) {
            if (preg_match('/@' . $match . '/i',  isset($rule->name) ? $rule->name : '', $matches)) {
                $values[substr($rule->name, 1)] = $rule->value->toCss();
            }
        }
        return $values;
    }

    protected function getRules()
    {
        if (!isset($this->rules)) {
            $l = new Less_Parser();
            $parser = $l->parseFile($this->file, $this->urlroot, true);
            $this->rules = $parser->rules;
        }
        return $this->rules;
    }

}