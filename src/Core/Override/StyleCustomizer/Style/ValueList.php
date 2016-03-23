<?php

namespace Concrete\Core\StyleCustomizer\Style;

use Concrete\Core\StyleCustomizer\Style\Value\BasicValue;
use Concrete\Package\AssetPipeline\Src\Core\Original\StyleCustomizer\Style\ValueList as CoreValueList;
use Concrete\Package\AssetPipeline\Src\StyleCustomizer\Style\ExtractableStyleInterface;
use Core;
use Exception;

class ValueList extends CoreValueList
{

    public static function loadFromFile($file, $urlroot = false)
    {
        $extractor = Core::make('Concrete\Package\AssetPipeline\Src\Asset\ManagerInterface')->getValueExtractorForFile($file, $urlroot);
        if (!is_object($extractor)) {
            throw new Exception(t("Invalid file for value extraction: %s", $file));
        }

        $vl = new static();

        $bv = new BasicValue('preset-fonts-file');
        $bv->setValue($extractor->extractFontsFile());
        $vl->addValue($bv);

        foreach(array('ColorStyle', 'TypeStyle', 'ImageStyle', 'SizeStyle') as $type) {
            $cls = '\\Concrete\\Core\\StyleCustomizer\\Style\\' . $type;
            $o = new $cls;
            if (!($o instanceof ExtractableStyleInterface)) {
                throw new Exception(t("The following style type is not extractable: %s", $type));
            }
            $values = $o->getValuesFromExtractor($extractor);
            $vl->addValues($values);
        }

        return $vl;
    }

}
