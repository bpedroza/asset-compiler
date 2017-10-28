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

namespace Bpedroza\AssetCompiler\Assets;

use Bpedroza\AssetCompiler\Assets\BaseCompiledAsset;
use Bpedroza\AssetCompiler\Assets\BaseAsset;

/**
 * Description of BaseMinifiedAsset
 *
 * @author Bryan Pedroza
 */
abstract class BaseMinifiedAsset extends BaseCompiledAsset
{
    /**
     * The original asset
     * @var \Bpedroza\AssetCompiler\Assets\CssAsset
     */
    protected $originalAsset = 0;
    
    /**
     * 
     * @param \Bpedroza\AssetCompiler\Configuration $config
     * @param \Bpedroza\AssetCompiler\Assets\BaseAsset $Asset
     */
    public function __construct(\Bpedroza\AssetCompiler\Configuration $config, BaseAsset $Asset)
    {
        $this->originalAsset = $Asset;
        $pieces = explode('.', $Asset->filename());
        $last = array_pop($pieces);
        $pieces[] = 'min';
        $pieces[] = $last;
        $minifiedFilename = implode('.', $pieces);
        parent::__construct($config, $minifiedFilename, []);
    }
    
    /**
     * Return all the assets to be compiled
     * @return \Bpedroza\AssetCompiler\Assets\BaseAsset
     */
    public function originalAsset()
    {
        return $this->originalAsset;
    }
    
    /**
     * Does the asset need to be recompiled?
     * @return boolean
     */
    public function needsToBeRecompiled()
    {
        return $this->originalAsset->exists && ( !$this->exists || $this->originalAsset->modTime() > $this->modTime() );
    }
    
    /**
     * Get the mod time of the original asset instead of minified one
     * @return type
     */
    public function modTime()
    {
        return $this->originalAsset()->modTime();
    }
}
