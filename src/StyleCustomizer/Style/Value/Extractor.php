<?php
namespace Concrete\Package\AssetPipeline\Src\StyleCustomizer\Style\Value;

defined('C5_EXECUTE') or die("Access Denied.");

abstract class Extractor
{

    const PRESET_RULE_NAME = 'preset-name';
    const PRESET_RULE_ICON = 'preset-icon';
    const PRESET_RULE_FONTS_FILE = 'preset-fonts-file';
    const PRESET_RULE_ICON_FUNCTION = 'concrete-icon';

    protected $file;
    protected $urlroot;
    protected $rules;

    public function __construct($file, $urlroot = false)
    {
        $this->file = $file;
        $this->urlroot = $urlroot;
    }

    public function normalizedUri($uri)
    {
        if ($this->urlroot) {
            $uri = rtrim($this->urlroot, '/') . '/' . $uri;
            $parts = parse_url($uri);
            $path = $parts['path'];
            while (preg_match('#(.*)\.\./(.*)#', $path, $matches)) {
                $before = $matches[1];
                $after = $matches[2];
                $bparts = array_filter(explode('/', $before));
                array_shift($bparts);
                if (count($bparts) > 0) {
                    $path = '/' . implode('/', $bparts) . '/' . $after;
                } else {
                    $path = '/' . $after;
                }
            }
            $uri = $parts['scheme'] . '://' . $parts['host'] . $path;
        }
        return $uri;
    }

}