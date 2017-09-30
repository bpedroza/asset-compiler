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

use Bpedroza\AssetCompiler\AssetOutputBuilders\OutputBuilderInterface;
use Bpedroza\AssetCompiler\Assets\BaseAsset;
use Bpedroza\AssetCompiler\Assets\BaseCompiledAsset;

/**
 * Base class for the output builder, will have shared functionality all builders can use
 *
 * @author Bryan Pedroza
 */
abstract class BaseOutputBuilder implements OutputBuilderInterface
{

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
     * Method to generate the output file. Very cruse, just glues file contents together.
     * @param \Bpedroza\AssetCompiler\Assets\BaseCompiledAsset $CompiledAsset - the compiled asset object
     */
    protected function generateCompiledFileIfNeeded(BaseCompiledAsset $CompiledAsset, $separator = "\n" . ';')
    {
        if ($CompiledAsset->needsToBeReCompiled()) {
            file_put_contents($CompiledAsset->absolutePath(), '');
            foreach ($CompiledAsset->getAssets() as $Asset) {
                file_put_contents($CompiledAsset->absolutePath(), $separator . file_get_contents($Asset->absolutePath()), FILE_APPEND);
            }
        }
    }
    
    /**
     * Given a compiled asset produce the output for a compiled file when in debug mode
     * @param \Bpedroza\AssetCompiler\Assets\BaseCompiledAsset $CompiledAsset
     * @param array $attrs - the attributes for the markup
     */
    public function buildOutputCompiledDebug(BaseCompiledAsset $CA, $attrs = [])
    {
        $output = '';
        foreach ($CA->getAssets() as $Asset) {
            $output .= $this->buildOutputSingleDebug($Asset, $attrs) . "\n";
        }
        return $output;
    }
    
    /**
     * Given an asset produce the output for a single when in debug mode
     * Override if debug needs special behavior for single files.
     * @param \Bpedroza\AssetCompiler\Assets\BaseAsset $Asset
     * @param array $attrs - the attributes for the markup
     */
    public function buildOutputSingleDebug(BaseAsset $Asset, $attrs = [])
    {
        return $this->buildOutputSingle($Asset, $attrs);
    }

}
