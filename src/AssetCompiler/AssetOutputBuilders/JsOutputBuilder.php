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
use Bpedroza\AssetCompiler\Assets\BaseAsset;
use Bpedroza\AssetCompiler\Assets\BaseCompiledAsset;

/**
 * Description of JsOutputBuilder
 *
 * @author Bryan Pedroza
 */
class JsOutputBuilder extends BaseOutputBuilder
{
    public function buildOutputCompiled(BaseCompiledAsset $CA, $attrs = [])
    {
        $this->generateCompiledFileIfNeeded($CA);
        return '<script src="' . $CA->httpPath() . '?v=' . $CA->getLastModTimeOfNewestAsset() . '" ' . $this->generateAttributesString($attrs) . '></script>';
    }

    public function buildOutputSingle(BaseAsset $Asset, $attrs = [])
    {
        return '<script src="' . $Asset->httpPath() . '?v=' . $Asset->modTime() . '"' . $this->generateAttributesString($attrs) . ' ></script>';
    }
    
}
