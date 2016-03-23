<?php

namespace Concrete\Package\AssetPipeline\Src\StyleCustomizer\Style\Value;

abstract class AbstractExtractor implements ExtractorInterface
{

    const PRESET_RULE_NAME = 'preset-name';
    const PRESET_RULE_ICON = 'preset-icon';
    const PRESET_RULE_FONTS_FILE = 'preset-fonts-file';
    const PRESET_RULE_ICON_FUNCTION = 'concrete-icon';

    /** @var string */
    protected $file;
    /** @var string */
    protected $urlroot;
    /** @var string */
    protected $rules;

    /**
     * @param string $file
     * @param string|bool $urlroot
     */
    public function __construct($file, $urlroot = false)
    {
        $this->file = $file;
        $this->urlroot = $urlroot;
    }

    /**
     * {@inheritDoc}
     */
    abstract public function extractPresetName();

    /**
     * {@inheritDoc}
     */
    abstract public function extractPresetIcon();

    /**
     * {@inheritDoc}
     */
    abstract public function extractFontsFile();

    /**
     * {@inheritDoc}
     */
    abstract public function extractFirstMatchingValue($find);

    /**
     * {@inheritDoc}
     */
    abstract public function extractMatchingVariables($match);

    /**
     * {@inheritDoc}
     */
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