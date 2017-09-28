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
use Bpedroza\AssetCompiler\Resource;
use Bpedroza\AssetCompiler\CompiledResource;

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
        if ($this->config->debug()) {
            $output = '';
            foreach ($files as $file) {
                $output .= $this->getScript($file) . "\n";
            }
            return $output;
        }
        
        $CompiledResource = new CompiledResource($this->config, 'js', $outFile);
        $resources = $this->getResourcesFromFileArray($files, 'js');
        $lastModTime = $this->getLastModTimeOfFiles($resources);

        if (( $CompiledResource->modTime() === 0 || $lastModTime > $CompiledResource->modTime() ) && count($resources)) {
            $this->generateOutFile($CompiledResource->absolutePath(), $resources, "\n" . ';');
        }

        return '<script src="' . $CompiledResource->httpPath() . '?v=' . $lastModTime . '" ' . $this->generateAttributesString($attrs) . '/>';
    }

    /**
     * Function to take an js file path relative to public/js and build the script tag for it with cache buster
     * @param string $file - the file to generate the script tag for
     * @param array $attrs - add attributes to the tag
     * @return string - the script tag for the compiled js file
     */
    public function getScript($file, $attrs = [])
    {
        $Resource = new Resource($this->config, 'js', $file);
        return '<script src="' . $Resource->httpPath() . '?v=' . $Resource->modTime() . '"' . $this->generateAttributesString($attrs) . ' />';
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
        if ($this->config->debug()) {
            $output = '';
            foreach ($files as $file) {
                $output .= $this->getStyle($file) . "\n";
            }
            return $output;
        }
        
        $CompiledResource = new CompiledResource($this->config, 'css', $outFile);
        $resources = $this->getResourcesFromFileArray($files, 'css');
        $lastModTime = $this->getLastModTimeOfFiles($resources);

        if (( $CompiledResource->modTime() === 0 || $lastModTime > $CompiledResource->modTime() ) && count($resources)) {
            $this->generateOutFile($CompiledResource->absolutePath(), $resources, "\n" . ';');
        }

        return '<link href="' . $CompiledResource->httpPath() . '?v=' . $lastModTime . '" ' . $this->generateAttributesString($attrs) . 'rel="stylesheet" />';
    }

    /**
     * Function to take an js file path relative to public/js and build the script tag for it with cache buster
     * @param string $file - the file to generate the script tag for
     * @param array $attrs - add attributes to the tag
     * @return string - the script tag for the compiled js file
     */
    public function getStyle($file, $attrs = [])
    {
        $Resource = new Resource($this->config, 'css', $file);
        return '<link href="' . $Resource->httpPath() . '?v=' . $Resource->modTime() . '"' . $this->generateAttributesString($attrs) . ' rel="stylesheet" />';
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
     * Method to get the file paths and last modified time of a set of files
     * @param Resource[] $resources - an array of the file resources
     * @param string $type - the type (and path) (js | css)
     * @return array - an array with the paths array and the last modified time
     */
    protected function getLastModTimeOfFiles($resources)
    {
        $lastModTime = 0;
        foreach ($resources as $Resource) {
            $mTime = $Resource->modTime();
            $lastModTime = $mTime > $lastModTime ? $mTime : $lastModTime;
        }

        return $lastModTime;
    }

    /**
     * Method to generate the output file
     * @param string $outFilePath - the full path to the output file
     * @param Resource[] $resources - an array of Resources to compile
     * @param string $separator - optional string to separate files with
     */
    protected function generateOutFile($outFilePath, $resources, $separator)
    {
        file_put_contents($outFilePath, '');
        foreach ($resources as $Resource) {
            file_put_contents($outFilePath, $separator . file_get_contents($Resource->absolutePath()), FILE_APPEND);
        }
    }
    
    /**
     * Given an array of filenames, will return an array of resources
     * @param array $files
     * @param string $type - what type of resources are they?
     * @return Resource[]
     */
    protected function getResourcesFromFileArray($files = [], $type)
    {
        $resources = [];
        
        foreach($files as $file) {
            $resources[] = new Resource($this->config, $type, $file);
        }
        
        return $resources;
    }

}
