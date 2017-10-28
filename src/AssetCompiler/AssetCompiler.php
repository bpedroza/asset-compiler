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
use Bpedroza\AssetCompiler\AssetTypes\TypeMinifiedCss;
use Bpedroza\AssetCompiler\AssetTypes\TypeJs;
use Bpedroza\AssetCompiler\AssetTypes\TypeMinifiedJs;

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
     * @return \Bpedroza\AssetCompiler\Configuration 
     */
    public function config()
    {
        return $this->config;
    }

    /**
     * Function to take an js file path relative to public/js and build the script tag for it with cache buster
     * @param string $filename - the file to generate the script tag for
     * @param array $attrs - add attributes to the tag
     * @return string - the script tag for the compiled js file
     */
    public function getScript($filename, $attrs = [])
    {
        $Type = $this->getMini() ? new TypeMinifiedJs($this->config) : new TypeJs($this->config);
        return $this->buildSingleFromType($Type, $filename, $attrs);
    }

    /**
     * Function to take an js file path relative to public/js and build the script tag for it with cache buster
     * @param string $filename - the file to generate the script tag for
     * @param array $attrs - add attributes to the tag
     * @return string - the script tag for the compiled js file
     */
    public function getStyle($filename, $attrs = [])
    {
        $Type = $this->getMini() ? new TypeMinifiedCss($this->config) : new TypeCss($this->config);
        return $this->buildSingleFromType($Type, $filename, $attrs);
        
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
        $Type = $this->getMini() ? new TypeMinifiedJs($this->config) : new TypeJs($this->config);
        return $this->buildCompiledFromType($Type, $outFile, $files, $attrs);
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
        $Type = $this->getMini() ? new TypeMinifiedCss($this->config) : new TypeCss($this->config);    
        return $this->buildCompiledFromType($Type, $outFile, $files, $attrs);
    }
    
    /**
     * Should we get minified types?
     * @return bool
     */
    private function getMini()
    {
        return $this->config()->minify() && !$this->config()->debug();
    }
    
    /**
     * 
     * @param \Bpedroza\AssetCompiler\AssetTypes\TypeInterface $Type
     * @param string $filename - the base file
     * @param array $attrs - the attributes for the markup
     * @return string - the output
     */
    private function buildSingleFromType($Type, $filename, $attrs)
    {
        $Builder = $Type->getOutputBuilder();
        $Asset = $Type->getAsset($filename);
        
        if($this->config->debug()) {
            return $Builder->buildOutputSingleDebug($Asset, $attrs);
        }
        
        return $Builder->buildOutputSingle($Asset, $attrs);
    }
    
    /**
     * 
     * @param \Bpedroza\AssetCompiler\AssetTypes\TypeInterface $Type
     * @param string $compiledFilename - the name of the file that will be compiled
     * @param array $filesToCompile - names of files to compile
     * @param array $attrs - the attributes for the markup
     * @return string - the output
     */
    private function buildCompiledFromType($Type, $compiledFilename, $filesToCompile, $attrs)
    {
        $Builder = $Type->getOutputBuilder();
        $CompiledAsset = $Type->getCompiledAsset($compiledFilename, $filesToCompile);
        
        if($this->config->debug()) {
            return $Builder->buildOutputCompiledDebug($CompiledAsset, $attrs);
        }
        
        return $Builder->buildOutputCompiled($CompiledAsset, $attrs);
    }
}
