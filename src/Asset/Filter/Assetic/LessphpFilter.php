<?php

namespace Concrete\Package\AssetPipeline\Src\Asset\Filter\Assetic;

use Assetic\Asset\AssetInterface;
use Assetic\Factory\AssetFactory;
use Assetic\Filter\DependencyExtractorInterface;
use Assetic\Filter\FilterInterface;
use Assetic\Util\LessUtils;

/**
 * Provides an Assetic filter wrapper for oyejorge/less.php.
 *
 * @author Antti Hukkanen <antti.hukkanen@mainiotech.fi>
 */
class LessphpFilter implements FilterInterface, DependencyExtractorInterface
{

    /** @var array */
    private $options = array();
    /** @var array */
    private $lessVariables = array();
    /** @var array */
    private $customFunctions = array();
    /** @var bool */
    private $relativeUrlPaths = false;
    /** @var string */
    private $basePath = '';

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * Sets a boolean indicating whether the Less parser should be
     * using relative URL paths or not. In case relative URL paths
     * are used, the paths for all file references in the parsed
     * file are relative to that file from the base path/URI.
     *
     * @param bool $relativePaths
     */
    public function setRelativeUrlPaths($relativePaths)
    {
        $this->relativeUrlPaths = $relativePaths;
    }

    /**
     * Set variables to be used by the Less parser. These variables
     * can be e.g. programmatically defined/generated variables
     * that we want to be available within the Less preprocessing
     * language.
     *
     * Read more about this:
     * {@link https://github.com/oyejorge/less.php#setting-variables}
     *
     * @param array $variables
     */
    public function setLessVariables(array $variables)
    {
        $this->lessVariables = $variables;
    }

    /**
     * Set the base path/URI for the parser. This is used to possibly
     * convert any file references in the Less files into their full
     * path representation (e.g. relative URLs).
     *
     * Read more about this:
     * {@link https://github.com/oyejorge/less.php#parsing-less-files}
     *
     * @param string $url
     */
    public function setBasePath($url)
    {
        $this->basePath = $url;
    }

    /**
     * Registers a function to be added to the Less parser for
     * the parsing process.
     *
     * @param string $name
     * @param callable $callable
     */
    public function registerFunction($name, $callable)
    {
        $this->customFunctions[$name] = $callable;
    }

    /**
     * {@inheritDoc}
     */
    public function filterLoad(AssetInterface $asset)
    {
        $filePath = $asset->getSourceRoot() . '/' . $asset->getSourcePath();

        $parser = new \Less_Parser($this->options);
        foreach ($this->customFunctions as $name => $callable) {
            $parser->registerFunction($name, $callable);
        }

        $baseUri = $this->basePath;
        if ($this->relativeUrlPaths) {
            $baseUri = $baseUri . '/' . $asset->getSourcePath();
        }

        $parser = $parser->parseFile($filePath, $baseUri);
        $parser->ModifyVars($this->lessVariables);

        $asset->setContent($parser->getCss());
    }

    /**
     * {@inheritDoc}
     */
    public function filterDump(AssetInterface $asset)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getChildren(AssetFactory $factory, $content, $loadPath = null)
    {
        $loadPaths = $this->loadPaths;
        if (null !== $loadPath) {
            $loadPaths[] = $loadPath;
        }

        if (empty($loadPaths)) {
            return array();
        }

        $children = array();
        foreach (LessUtils::extractImports($content) as $reference) {
            if ('.css' === substr($reference, -4)) {
                // skip normal css imports
                // todo: skip imports with media queries
                continue;
            }

            if ('.less' !== substr($reference, -5)) {
                $reference .= '.less';
            }

            foreach ($loadPaths as $loadPath) {
                if (file_exists($file = $loadPath . '/' . $reference)) {
                    $coll = $factory->createAsset($file, array(), array('root' => $loadPath));
                    foreach ($coll as $leaf) {
                        $leaf->ensureFilter($this);
                        $children[] = $leaf;
                        goto next_reference;
                    }
                }
            }

            next_reference:
        }

        return $children;
    }

}
