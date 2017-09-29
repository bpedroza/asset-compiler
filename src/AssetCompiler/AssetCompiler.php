<?php

/*
 * The MIT License
 *
 * Copyright 2017 Bryan Pedroza.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Bpedroza\AssetCompiler;

use Bpedroza\AssetCompiler\Configuration;
use Bpedroza\AssetCompiler\AssetTypes\TypeCss;
use Bpedroza\AssetCompiler\AssetTypes\TypeJs;
use Bpedroza\AssetCompiler\AssetTypes\TypeInterface;
use Bpedroza\AssetCompiler\Assets\BaseCompiledAsset;

/**
 * Use this tool to build js and css files compiled as one to avoid having too many assets to load
 * this tool will also put a cache buster string on all compiled assets
 *
 * @author Bryan
 */
class AssetCompiler
{

    /**
     * The configuration object the stores all the config values
     * @var Bpedroza\AssetCompiler\Configuration 
     */
    protected $config;

    /**
     * Set the major paths for the class
     * @param string $rootPath - full path to resources
     * @param string $httpRootPath - http path to resources
     * @throws \Exception
     */
    public function __construct($rootPath, $httpRootPath = null)
    {
        $this->config = new Configuration();
        $this->config->rootPath($rootPath)->httpPath($httpRootPath);
    }

    /**
     * Return the configuration object
     * @return Bpedroza\AssetCompiler\Configuration 
     */
    public function config()
    {
        return $this->config;
    }

    /**
     * Function to take an js file path relative to public/js and build the script tag for it with cache buster
     * @param string $file - the file to generate the script tag for
     * @param array $attrs - add attributes to the tag
     * @return string - the script tag for the compiled js file
     */
    public function getScript($file, $attrs = [])
    {
        $Type = new TypeJs($this->config);
        $Resource = $Type->getAsset($file);
        return '<script src="' . $Resource->httpPath() . '?v=' . $Resource->modTime() . '"' . $this->generateAttributesString($attrs) . ' />';
    }

    /**
     * Function to take an js file path relative to public/js and build the script tag for it with cache buster
     * @param string $file - the file to generate the script tag for
     * @param array $attrs - add attributes to the tag
     * @return string - the script tag for the compiled js file
     */
    public function getStyle($file, $attrs = [])
    {
        $Type = new TypeCss($this->config);
        $Resource = $Type->getAsset($file);
        return '<link href="' . $Resource->httpPath() . '?v=' . $Resource->modTime() . '"' . $this->generateAttributesString($attrs) . ' rel="stylesheet" />';
    }

    /**
     * Take an array of js file paths relative to the public/js folder
     * and combine them into a single file named with the last modified time of the 
     * most recent modified file to avoid caching.
     * @param array $files - an array of the file names we need to have
     * @param string $outFile - the name of the output file - eg. app-compiled.js
     * @param array $attrs - add attributes to the tag
     * @return string - the script tag for the compiled js file
     */
    public function getScriptsMulti(array $files, string $outFile, $attrs = [])
    {
        $Type = new TypeJs($this->config);
        if (($debugOutput = $this->getMultiOutputForDebug($files, $Type) ) !== false) {
            return $debugOutput;
        }

        $CompiledResource = $this->createCompiledFile($files, $Type, $outFile);

        return '<script src="' . $CompiledResource->httpPath() . '?v=' . $CompiledResource->getLastModTimeOfNewestAsset() . '" ' . $this->generateAttributesString($attrs) . '/>';
    }

    /**
     * Function to take an array of css file paths relative to the public/css folder
     * and combine them into a single file named with the last modified time of the 
     * most recent modified file to avoid caching
     * @param array $files - an array of the file names we need to have
     * @param string $outFile - the name of the output file - eg. app-compiles.js
     * @param array $attrs - add attributes to the tag
     * @return string - the script tag for the compiled js file
     */
    public function getStylesMulti(array $files, string $outFile, $attrs = [])
    {
        $Type = new TypeCss($this->config);
        if (($debugOutput = $this->getMultiOutputForDebug($files, $Type) ) !== false) {
            return $debugOutput;
        }

        $CompiledResource = $this->createCompiledFile($files, $Type, $outFile);

        return '<link href="' . $CompiledResource->httpPath() . '?v=' . $CompiledResource->getLastModTimeOfNewestAsset() . '" ' . $this->generateAttributesString($attrs) . 'rel="stylesheet" />';
    }

    /**
     * create a compiled file and return the compiled resource
     * @param array $files
     * @param \Bpedroza\AssetCompiler\AssetTypes\TypeInterface $Type
     * @param string $outFile
     * @return \Bpedroza\AssetCompiler\Assets\BaseCompiledAsset
     */
    protected function createCompiledFile($files, TypeInterface $Type, $outFile)
    {
        $CompiledAsset = $Type->getCompiledAsset($outFile, $files);

        if ($CompiledAsset->needsToBeReCompiled()) {
            $this->generateOutFile($CompiledAsset);
        }

        return $CompiledAsset;
    }

    /**
     * Gets the output for multi call when debug is on
     * @param array $files
     * @param \Bpedroza\AssetCompiler\AssetTypes\TypeInterface $type
     * @return boolean|string
     */
    protected function getMultiOutputForDebug($files, TypeInterface $Type)
    {
        if (!$this->config->debug()) {
            return false;
        }

        $output = '';
        foreach ($files as $file) {
            $func = $Type instanceof TypeJs ? 'getScript' : 'getStyle';
            $output .= $this->{$func}($file) . "\n";
        }
        return $output;
    }

    /**
     * Method to generate attribute string from an array
     * @param array $attrs - an array of attributes where key is the attribute name and value is the value
     * @return string
     */
    protected function generateAttributesString($attrs)
    {
        if (empty($attrs)) {
            return '';
        }
        $attrString = ' ';
        foreach ($attrs as $key => $val) {
            $attrString .= $key . '="' . $val . '" ';
        }

        return rtrim($attrString);
    }

    /**
     * Method to generate the output file
     * @param \Bpedroza\AssetCompiler\Assets\BaseCompiledAsset $CompiledAsset - the compiled asset object
     */
    protected function generateOutFile(BaseCompiledAsset $CompiledAsset)
    {
        $separator = "\n" . ';';
        file_put_contents($CompiledAsset->absolutePath(), '');
        foreach ($CompiledAsset->getAssets() as $Asset) {
            file_put_contents($CompiledAsset->absolutePath(), $separator . file_get_contents($Asset->absolutePath()), FILE_APPEND);
        }
    }

}
