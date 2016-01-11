<?php
namespace Concrete\Core\StyleCustomizer\Style;

use Core;
use Config;
use Environment;

abstract class Style
{

    protected $variable;
    protected $name;

    abstract public function render($value = false);
    abstract public function getValuesFromVariables($extractor);
    abstract public function getValueFromRequest(\Symfony\Component\HttpFoundation\ParameterBag $request);

    public function getValueFromList(\Concrete\Core\StyleCustomizer\Style\ValueList $list)
    {
        $type = static::getTypeFromClass($this);
        foreach($list->getValues() as $value) {
            if ($value->getVariable() == $this->getVariable() && $type == static::getTypeFromClass($value, 'Value')) {
                return $value;
            }
        }
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    /** Returns the display name for this style (localized and escaped accordingly to $format)
     * @param string $format = 'html'
     *   Escape the result in html format (if $format is 'html').
     *   If $format is 'text' or any other value, the display name won't be escaped.
     * @return string
     */
    public function getDisplayName($format = 'html')
    {
        $value = tc('StyleName', $this->getName());
        switch($format) {
            case 'html':
                return h($value);
            case 'text':
            default:
                return $value;
        }
    }

    public function setVariable($variable)
    {
        $this->variable = $variable;
    }

    public function getVariable()
    {
        return $this->variable;
    }

    /**
     * Returns a path to an elements directory for this Style. Might not be used by all styles.
     * @return string
     */
    public function getFormElementPath()
    {
        $className = join('', array_slice(explode('\\', get_called_class()), -1));
        $segment = substr($className, 0, strpos($className, 'Style'));
        $element = uncamelcase($segment);
        $env = Environment::get();
        return $env->getPath(DIRNAME_ELEMENTS . '/' . DIRNAME_STYLE_CUSTOMIZER . '/' . DIRNAME_STYLE_CUSTOMIZER_TYPES . '/' . $element . '.php');
    }

    /**
     * Tests whether the file can contain presets or not. This depends on the
     * filters set in the configuration and whether the filter has been defined
     * to provide presets.
     */
    public static function canFileContainCustomizableStyles($file)
    {
        $filters = Config::get('assets.filters');
        foreach ($filters as $key => $flt) {
            if (isset($flt['customizableStyles']) && $flt['customizableStyles'] &&
                    preg_match('#' . str_replace('#', '\#', $flt['applyTo']) . '#', $file)) {
                return true;
            }
        }
        return false;
    }

    public static function getFileExtensionsForCustomizableStyles()
    {
        $filters = Config::get('assets.filters');

        $extensions = array();
        foreach ($filters as $key => $flt) {
            if (isset($flt['customizableStyles']) && $flt['customizableStyles']) {
                $extensions[] = $key;
            }
        }
        return $extensions;
    }

    public static function getValueExtractorForFile($file, $urlroot)
    {
        $filters = Config::get('assets.filters');
        foreach ($filters as $key => $flt) {
            if (isset($flt['customizableStyles']) && $flt['customizableStyles'] &&
                    preg_match('#' . str_replace('#', '\#', $flt['applyTo']) . '#', $file)) {
                if (!Core::bound('assets/value/extractor/' . $key)) {
                    throw new \Exception(t("Value extractor not set for key: %s", $key));
                }
                return Core::make('assets/value/extractor/' . $key, array($file, $urlroot));
            }
        }
    }

    protected static function getTypeFromClass($class, $suffix = 'Style')
    {
        $class = get_class($class);
        $class = substr($class, strrpos($class, '\\') + 1);
        $type = uncamelcase(substr($class, 0, strrpos($class, $suffix)));
        return $type;
    }

}
