<?php

/*
 * The MIT License
 *
 * Copyright 2017 Bryan.
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

/**
 * Description of MinifiedCssTest
 *
 * @author Bryan
 */
class MinifiedJsTest extends AssetCompilerTest
{
    public function setUp()
    {
        parent::setUp();
        $this->AssetCompiler->config()->minify(true);
    }
    
    public function test_get_single_minified_js_file()
    {
        $actual = $this->AssetCompiler->getScript('test1.js');
        $fileTime = filemtime(__DIR__ . '/testresources/js/test1.js');
        $expected = '<script src="/js/compiled/test1.min.js?v=' . $fileTime . '" ></script>';
        $this->assertEquals($expected, $actual);
    }
    
    public function test_get_single_minified_js_file_contents()
    {
        $fileName = 'test1.js';
        $minifiedName = 'test1.min.js';
        $minifiedPath = $this->rootPath . 'js/' . $this->AssetCompiler->config()->compiledFolder() . '/' . $minifiedName;
        $this->AssetCompiler->getScript($fileName);

        $this->assertFileExists($minifiedPath);

        $actual = file_get_contents($minifiedPath);
        $expected = 'var message="Hello World"';

        $this->assertEquals($expected, $actual);
    }
    
    public function test_get_single_minified_js_file_contents_not_minified_in_debug_mode()
    {
        $this->AssetCompiler->config()->debug(true);
        
        $actual = $this->AssetCompiler->getScript('test1.js');
        $fileTime = filemtime(__DIR__ . '/testresources/js/test1.js');
        $expected = '<script src="/js/test1.js?v=' . $fileTime . '" ></script>';
        $this->assertEquals($expected, $actual);
    }
    
    public function test_get_multi_minified_js_file()
    {
        $compiledName = 'compiledJs.js';
        $filePath = __DIR__ . '/testresources/js/test1.js';
        touch($filePath);
        $fileTime = filemtime($filePath);

        $actual = $this->AssetCompiler->getScriptsMulti(['test1.js', 'test2.js'], $compiledName);
        $expected = '<script src="/js/' . $this->AssetCompiler->config()->compiledFolder() . '/' . $compiledName . '?v=' . $fileTime . '" ></script>';

        $this->assertFileExists($this->rootPath . 'js/' . $this->AssetCompiler->config()->compiledFolder() . '/' . $compiledName);
        $this->assertEquals($expected, $actual);
    }
    
    public function test_get_multi_minified_js_file_contents()
    {
        $compiledName = 'compiledJs.js';
        $filePath = $this->rootPath . 'js/' . $this->AssetCompiler->config()->compiledFolder() . '/' . $compiledName;
        $this->AssetCompiler->getScriptsMulti(['test1.js', 'test2.js'], $compiledName);

        $this->assertFileExists($filePath);

        $actual = file_get_contents($filePath);
        $expected = 'var message="Hello World";function doSomething(){return 2+2}';

        $this->assertEquals($expected, $actual);
    }
    
    public function test_get_multi_minified_js_file_contents_not_minified_in_debug_mode()
    {
        $compiledName = 'compiledJs.js';
        $files = ['test1.js', 'test2.js'];
        $this->AssetCompiler->config()->debug(true);
        $actual = $this->AssetCompiler->getScriptsMulti($files, $compiledName);

        $expected = '';
        foreach ($files as $file) {
            $fileTime = filemtime(__DIR__ . '/testresources/js/' . $file);
            $expected .= '<script src="/js/' . $file . '?v=' . $fileTime . '" ></script>' . "\n";
        }

        $this->assertEquals($expected, $actual);
    }
}
