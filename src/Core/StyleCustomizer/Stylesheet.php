<?php
namespace Concrete\Core\StyleCustomizer;

use Config;
use Core;

class Stylesheet
{

    protected $file; // full path to stylesheet e.g. /full/path/to/concrete/themes/greek_yogurt/css/main.less
    protected $sourceUriRoot; // root of source. e.g. /concrete/themes/greek_yogurt/
    protected $outputDirectory; // e.g /full/path/to/files/cache/themes/greek_yogurt/css/main.css"
    protected $relativeOutputDirectory; // e.g /files/cache/themes/greek_yogurt/css/main.css"
    protected $stylesheet; // e.g "css/main.less";

    protected $valueList;

    public function __construct($stylesheet, $file, $sourceUriRoot, $outputDirectory, $relativeOutputDirectory)
    {
        $this->stylesheet = $stylesheet;
        $this->file = $file;
        $this->sourceUriRoot = $sourceUriRoot;
        $this->outputDirectory = $outputDirectory;
        $this->relativeOutputDirectory = $relativeOutputDirectory;
    }

    public function setValueList(\Concrete\Core\StyleCustomizer\Style\ValueList $valueList)
    {
        $this->valueList = $valueList;
    }

    /**
     * Compiles the stylesheet using the correct preprocessor for the file.
     * If a ValueList is provided its values are injected into the stylesheet
     * before it is compiled into CSS.
     *
     * @return string CSS
     */
    public function getCss()
    {
        $assets = Core::make('asset_pipeline/helper/assets');
        $assets->setSourceUriRoot($this->sourceUriRoot);

        if (isset($this->valueList) && $this->valueList instanceof \Concrete\Core\StyleCustomizer\Style\ValueList) {
            $variables = array();
            foreach ($this->valueList->getValues() as $value) {
                $variables = array_merge($value->toLessVariablesArray(), $variables);
            }
            $assets->setStylesheetVariables('theme', $variables);
        }

        $css = $assets->compileCss(array($this->file));

        return $css;
    }

    public function output()
    {
        $css = $this->getCss();
        $path = dirname($this->getOutputPath());
        if (!file_exists($path)) {
            @mkdir($path, Config::get('concrete.filesystem.permissions.directory'), true);
        }
        file_put_contents($this->getOutputPath(), $css);
    }

    public function clearOutputFile()
    {
        @unlink($this->getOutputPath());
    }

    public function outputFileExists()
    {
        return file_exists($this->getOutputPath());
    }

    public function getOutputPath()
    {
        $fh = Core::make('helper/file');
        return $this->outputDirectory . '/' . $fh->replaceExtension($this->stylesheet, 'css');
    }

    public function getOutputRelativePath()
    {
        $fh = Core::make('helper/file');
        return $this->relativeOutputDirectory . '/' . $fh->replaceExtension($this->stylesheet, 'css');
    }

}
