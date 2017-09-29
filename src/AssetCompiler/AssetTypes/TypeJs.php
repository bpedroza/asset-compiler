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

namespace Bpedroza\AssetCompiler\AssetTypes;

use Bpedroza\AssetCompiler\AssetTypes\TypeInterface;
use Bpedroza\AssetCompiler\Assets\JsAsset;
use Bpedroza\AssetCompiler\Assets\CompiledJsAsset;
use Bpedroza\AssetCompiler\Configuration;

/**
 * Description of TypeCss
 *
 * @author Bryan Pedroza
 */
class TypeJs implements TypeInterface
{
    /**
     *
     * @var \Bpedroza\AssetCompiler\Configuration 
     */
    private $config;

    /**
     * @param \Bpedroza\AssetCompiler\Configuration $config - the configuration object
     */
    public function __construct(Configuration $config)
    {
        $this->config = $config;
    }

    /**
     * Given a filename return a new Jsasset
     * @param string $filename
     * @return \Bpedroza\AssetCompiler\Assets\JsAsset
     */
    public function getAsset($filename)
    {
        return new JsAsset($this->config, $filename);
    }

    /**
     * Given a filename return a new compiled jsasset
     * @param string $filename
     * @return \Bpedroza\AssetCompiler\Assets\CompiledJssAsset
     */
    public function getCompiledAsset($filename)
    {
        return new CompiledJsAsset($this->config, $filename);
    }

    /**
     * Get an array of JsAssets from filenames
     * @param string[] $filenames
     */
    public function getAssetArrayFromFilenames($filenames)
    {
        $assets = [];
        foreach($filenames as $filename) {
            $assets[] = $this->getAsset($filename);
        }
        
        return $assets;
    }

}