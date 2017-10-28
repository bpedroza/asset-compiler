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

use Bpedroza\AssetCompiler\Configuration;
use Bpedroza\AssetCompiler\Exceptions\ResourceMissingException;

/**
 * The asset is a file we will use this class to represent each one 
 * so we can abstract some specific logic
 *
 * @author Bryan Pedroza
 */
abstract class BaseAsset
{

    /**
     * The configuration object, we will need it to generate file information
     * @var \Bpedroza\AssetCompiler\Configuration 
     */
    protected $config;

    /**
     * The file name only for this file
     * @var string 
     */
    protected $filename;

    /**
     * The relative path from root to this file
     * @var string 
     */
    protected $relativePath;

    /**
     * The absolute path to this file
     * @var string 
     */
    protected $absolutePath;

    /**
     * The path we will serve to the markup to get this file via http request
     * @var string 
     */
    protected $httpPath;

    /**
     * The modified time for this file
     * @var int
     */
    protected $mtime = 0;

    /**
     * Does the file exist?
     * @var boolean 
     */
    protected $exists = false;

    /**
     * Setup all the properties for the resource
     * @param Configuration $config
     * @param string $filename
     */
    public function __construct(Configuration $config, $filename)
    {
        $this->filename = $filename;
        $this->config = $config;

        $this->setRelativePath();
        $this->setAbsolutePath();
        $this->setHttpPath();
        $this->setModTime();
    }
    
    /**
     * Get the sub folder this type of asset lives in
     * @return string - the folder
     */
    public abstract function getTypeFolder();

    /**
     * Get the absolute path to this file
     * @return string
     */
    public function filename()
    {
        return $this->filename;
    }
    
    /**
     * Get the absolute path to this file
     * @return string
     */
    public function absolutePath()
    {
        return $this->absolutePath;
    }

    /**
     * Get the http path to this file
     * @return string
     */
    public function httpPath()
    {
        return $this->httpPath;
    }

    /**
     * Get the modified time of this file
     * @return int
     */
    public function modTime()
    {
        return $this->mtime;
    }

    /**
     * Sets the relative path for this file
     * @return \Bpedroza\AssetCompiler\Resource
     */
    protected function setRelativePath()
    {
        $this->relativePath = '/' . $this->getTypeFolder() . '/' . $this->filename;
        return $this;
    }

    /**
     * Sets the absolute path for this file
     * @return \Bpedroza\AssetCompiler\Resource
     */
    protected function setAbsolutePath()
    {
        $this->absolutePath = $this->config->rootPath() . $this->relativePath;
        return $this;
    }

    /**
     * Sets the http path for this file
     * @return \Bpedroza\AssetCompiler\Resource
     */
    protected function setHttpPath()
    {
        $this->httpPath = $this->config->httpPath() . $this->relativePath;
        return $this;
    }

    /**
     * Sets the modified time for this file.
     * @return \Bpedroza\AssetCompiler\Resource
     * @throws ResourceMissingException - thrown when the file does not exist and configuration
     * is not ignoring missing files
     */
    protected function setModTime()
    {
        if (file_exists($this->absolutePath)) {
            $this->exists = true;
            $this->mtime = filemtime($this->absolutePath);
            return $this;
        }

        if ($this->config->ignoreMissing()) {
            $this->exists = false;
            $this->mtime = 0;
            return $this;
        }

        throw new ResourceMissingException($this->relativePath);
    }

}
