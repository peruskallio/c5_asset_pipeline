<?php

namespace Concrete\Package\AssetPipeline\Src\StyleCustomizer\Style\Value;

interface ExtractorInterface
{

    /**
     * Extracts the preset name from the style preset file.
     *
     * @return string
     */
    public function extractPresetName();

    /**
     * Extracts the preset icon colors from the style preset file. The returned
     * value is an array containing three CSS color values that can be used to
     * generate the preset icon.
     *
     * @return array
     */
    public function extractPresetIcon();

    /**
     * Extracts the fonts file from the style preset file.
     *
     * @return string
     */
    public function extractFontsFile();

    /**
     * Extracts the first matching value for a variable named as the first
     * parameter ($find) from the style preset file.
     *
     * @param string $find The name of the variable to be extracted
     *
     * @return string
     */
    public function extractFirstMatchingValue($find);

    /**
     * Extracts all values for variables that have names matching the given
     * regular expression as the first parameter ($match). The given regular
     * expression should only match the variable name and it should be given
     * without the regular expression delimiters.
     *
     * For example, matching all variables that have names ending with
     * ´-color´, this method should be called as follows:
     *
     * ```php
     * $extractor->extractMatchingVariables('.+\-color');
     * ```
     *
     * The returned variable is an array containing all the matched values,
     * having the variable name as the key and the variable value as the value.
     * In case there are multiple matched variables with the exactly same name,
     * the last one defined in the style preset file will be the one in the
     * returned array.
     *
     * @param string $match The inner regular expression to match against the
     *                      variable name.
     *
     * @return array
     */
    public function extractMatchingVariables($match);

    /**
     * Normalizes the passed URI to a full path format with the URL root given
     * to this class. This can be used for relative URLs in the style preset
     * file, for instance converting `../../../somepath/file.css` into:
     * `http://urlroot.com/absolute/path/to/somepath/file.css`.
     *
     * @param string $uri
     */
    public function normalizeUri($uri);

}
