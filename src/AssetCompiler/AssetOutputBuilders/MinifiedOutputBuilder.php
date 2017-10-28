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

namespace Bpedroza\AssetCompiler\AssetOutputBuilders;

use Bpedroza\AssetCompiler\AssetOutputBuilders\BaseOutputBuilder;
use Bpedroza\AssetCompiler\Assets\BaseMinifiedAsset;
use Bpedroza\AssetCompiler\Assets\BaseCompiledAsset;

/**
 * Base output builder for minified files
 *
 * @author Bryan Pedroza
 */
abstract class MinifiedOutputBuilder extends BaseOutputBuilder
{
    /**
     * Method to generate the output file. Very cruse, just glues file contents together.
     * @param \Bpedroza\AssetCompiler\Assets\BaseCompiledAsset $CompiledAsset - the compiled asset object
     */
    protected function generateMinifiedCompiledFileIfNeeded(BaseCompiledAsset $CompiledAsset)
    {
        $Minifier = $this->getMinifierInstance();
        if ($CompiledAsset->needsToBeReCompiled()) {
            foreach ($CompiledAsset->getAssets() as $Asset) {
                $Minifier->add($Asset->originalAsset()->absolutePath());
            }
            $Minifier->minify($CompiledAsset->absolutePath());
        }
    }
    
    /**
     * Method to generate the minified file.
     * @param \Bpedroza\AssetCompiler\Assets\BaseMinifiedAsset $Asset - the minified asset object
     */
    protected function generateMinifiedFileIfNeeded(BaseMinifiedAsset $Asset)
    {
        if ($Asset->needsToBeRecompiled()) {
            $Minifier = $this->getMinifierInstance();
            $Minifier->add($Asset->originalAsset()->absolutePath());
            $Minifier->minify($Asset->absolutePath());
        }
    }
    
    /**
     * Get the minifier for this type
     * @return \MatthiasMullie\Minify\Minify;
     */
    abstract protected function getMinifierInstance();
}
