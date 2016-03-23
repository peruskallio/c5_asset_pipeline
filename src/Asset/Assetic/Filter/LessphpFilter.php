<?php

namespace Concrete\Package\AssetPipeline\Src\Asset\Assetic\Filter;

use Assetic\Asset\AssetInterface;
use Assetic\Factory\AssetFactory;
use Assetic\Filter\DependencyExtractorInterface;
use Assetic\Filter\FilterInterface;
use Assetic\Util\LessUtils;

/**
 * Loads LESS files using the PHP implementation of less, lessphp.
 */
class LessphpFilter implements FilterInterface, DependencyExtractorInterface
{

    private $options = array();
    private $lessVariables = array();
    private $customFunctions = array();
    private $relativeUrlPaths = false;
    private $basePath = '';

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    public function setRelativeUrlPaths($relativePaths)
    {
        $this->relativeUrlPaths = $relativePaths;
    }

    public function setLessVariables($variables)
    {
        $this->lessVariables = $variables;
    }

    public function setBasePath($url)
    {
        $this->basePath = $url;
    }

    public function registerFunction($name, $callable)
    {
        $this->customFunctions[$name] = $callable;
    }

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

    public function filterDump(AssetInterface $asset)
    {
    }

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
