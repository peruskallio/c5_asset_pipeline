<?php
namespace Concrete\Core\StyleCustomizer\Style;

use Concrete\Core\StyleCustomizer\Style\Value\BasicValue;
use Less_Parser;
use Database;
use Symfony\Component\HttpFoundation\ParameterBag;

class ValueList
{

    protected $values = array();
    protected $scvlID;

    public function getValues() {
        return $this->values;
    }

    public function getValueListID()
    {
        return $this->scvlID;
    }

    public function save()
    {
        $db = Database::get();
        if (!isset($this->scvlID)) {
            $db->insert('StyleCustomizerValueLists', array());
            $this->scvlID = $db->LastInsertId();
        } else {
            $db->delete('StyleCustomizerValues', array('scvlID' => $this->scvlID));
        }

        foreach($this->values as $value) {
            $db->insert('StyleCustomizerValues', array('value' => serialize($value), 'scvlID' => $this->scvlID));
        }
    }

    public function addValue(\Concrete\Core\StyleCustomizer\Style\Value\Value $value)
    {
        $this->values[] = $value;
    }

    public function addValues($values)
    {
        foreach($values as $value) {
            $this->addValue($value);
        }
    }

    public static function getByID($scvlID)
    {
        $db = Database::get();
        $scvlID = $db->GetOne('select scvlID from StyleCustomizerValueLists where scvlID = ?', array($scvlID));
        if ($scvlID) {
            $o = new static();
            $o->scvlID = $scvlID;
            $rows = $db->fetchAll('select * from StyleCustomizerValues where scvlID = ?', array($scvlID));
            foreach($rows as $row) {
                $o->addValue(unserialize($row['value']));
            }
        }
        return $o;
    }

    public static function loadFromRequest(ParameterBag $request, \Concrete\Core\StyleCustomizer\StyleList $styles)
    {
        $vl = new static();
        foreach($styles->getSets() as $set) {
            foreach($set->getStyles() as $style) {
                $value = $style->getValueFromRequest($request);
                if (is_object($value)) {
                    $vl->addValue($value);
                }
            }
        }

        if ($request->has('preset-fonts-file')) {
            $bv = new BasicValue('preset-fonts-file');
            $bv->setValue($request->get('preset-fonts-file'));
            $vl->addValue($bv);
        }
        return $vl;
    }

    public static function loadFromFile($file, $urlroot = false)
    {
        $extractor = Style::getValueExtractorForFile($file, $urlroot);
        if (!is_object($extractor)) {
            throw new \Exception(t("Invalid file for value extraction: %s", $file));
        }

        $vl = new static();

        $bv = new BasicValue('preset-fonts-file');
        $bv->setValue($extractor->extractFontsFile());
        $vl->addValue($bv);

        foreach(array('ColorStyle', 'TypeStyle', 'ImageStyle', 'SizeStyle') as $type) {
            $o = '\\Concrete\\Core\\StyleCustomizer\\Style\\' . $type;
            $values = call_user_func_array(array($o, 'getValuesFromVariables'), array($extractor));
            $vl->addValues($values);
        }

        return $vl;
    }

}