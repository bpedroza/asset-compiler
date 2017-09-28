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

use Bpedroza\AssetCompiler\Exceptions\DirectoryDoesNotExistException;

/**
 * Store and retrieve configuration values for asset compiler
 *
 * @author Bryan Pedroza
 */
class Configuration
{

    /**
     * the relative path from root path to the css resources
     * @var string
     */
    protected $cssPath = 'css';

    /**
     * the relative path from root path to the js resources
     * @var string
     */
    protected $jsPath = 'js';

    /**
     * The full directory path to your resources
     * @var string 
     */
    protected $rootPath;

    /**
     * The http root path to be used when generating files
     * ie. http://example.com/resources or /resources for relative paths
     * @var string 
     */
    protected $httpPath;

    /**
     * The folder compiled files will be stored in
     * The folder will be nested in its respective parent (css | js)
     * @var string 
     */
    protected $compiledFolder = 'compiled';

    /**
     * When in debug mode we will not compile files to make it easier to 
     * troubleshoot
     * @var bool 
     */
    protected $debug = false;

    /**
     * When set to true, we will not throw exceptions for missing files
     * we will just ignore the missing file in compiled files and 
     * produce an empty string for single files
     * @var bool 
     */
    protected $ignoreMissing = false;

    /**
     * Set or get the root path to resources
     * @param string|null $path - the new path, or null to get existing path
     * @return string|$this
     * @throws \Exception
     */
    public function rootPath($path = null)
    {
        if ($path !== null && !is_dir($path)) {
            throw new DirectoryDoesNotExistException();
        }

        return $this->cleanTrailingConfigValue('rootPath', $path);
    }

    /**
     * Set or get the http path to resources
     * @param string|null $path - the new path, or null to get existing path
     * @return string|$this
     */
    public function httpPath($path = null)
    {
        return $this->cleanTrailingConfigValue('httpPath', $path);
    }

    /**
     * Path relative to root path where your css files live
     * @param string $path - the path
     * @return mixed - $this or the current cssPath
     */
    public function cssPath($path = null)
    {
        return $this->cleanConfigValue('cssPath', $path);
    }

    /**
     * Path relative to root path where your js files live
     * @param string $path - the path
     * @return mixed - $this or the current jsPath
     */
    public function jsPath($path = null)
    {
        return $this->cleanConfigValue('jsPath', $path);
    }

    /**
     * Name of folder that will be created in both js and css folders for compiled files
     * @param string $folder - the folder name
     * @return mixed - $this or the current compiled folder name
     */
    public function compiledFolder($folder = null)
    {
        return $this->cleanConfigValue('compiledFolder', $folder);
    }

    /**
     * Set or get config value for debug. When in debug, we won't compile items.
     * @param bool $debug
     * @return bool
     */
    public function debug($debug = null)
    {
        return $this->configValue('debug', $debug);
    }

    /**
     * Set or get config value for ignoring missing files. When true, we won't throw exceptions for missing files.
     * @param bool $ignore
     * @return bool
     */
    public function ignoreMissing($ignore = null)
    {
        return $this->configValue('ignoreMissing', $ignore);
    }

    /**
     * Set/Get configuration values that need to be stripped of slashes on both sides
     * @param string $variable - the name of the instance variable
     * @param mixed $value - the new value
     * @return mixed - the current value or $this
     */
    protected function cleanConfigValue($variable, $value = null)
    {
        if ($value === null) {
            return $this->configValue($variable);
        }

        return $this->configValue($variable, trim($value, '\/'));
    }

    /**
     * Set/Get configuration values that need to be stripped of trailing slashes
     * @param string $variable - the name of the instance variable
     * @param mixed $value - the new value
     * @return mixed - the current value or $this
     */
    protected function cleanTrailingConfigValue($variable, $value = null)
    {
        if ($value === null) {
            return $this->configValue($variable);
        }

        return $this->configValue($variable, rtrim($value, '\/'));
    }

    /**
     * Set/Get configuration values 
     * @param string $variable - the name of the instance variable
     * @param mixed $value - the new value
     * @return mixed - the current value or $this
     */
    protected function configValue($variable, $value = null)
    {
        if ($value === null) {
            return $this->{$variable};
        }

        $this->{$variable} = $value;
        return $this;
    }

}
