<?php

namespace Concrete\Core\StyleCustomizer;

use Concrete\Core\StyleCustomizer\Style\ValueList as StyleValueList;
use Concrete\Package\AssetPipeline\Src\Core\Original\StyleCustomizer\Stylesheet as CoreStylesheet;
use Core;

class Stylesheet extends CoreStylesheet
{

    /**
     * {@inheritDoc}
     */
    public function getCss()
    {
        $assets = Core::make('asset_pipeline/helper/assets');

        if (isset($this->valueList) && $this->valueList instanceof StyleValueList) {
            $variables = array();
            foreach ($this->valueList->getValues() as $value) {
                $variables = array_merge($value->toLessVariablesArray(), $variables);
            }
            $assets->setStylesheetVariables('theme', $variables);
        }

        $css = $assets->compileCss(array($this->file));

        return $css;
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
