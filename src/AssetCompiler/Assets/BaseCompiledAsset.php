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

use Bpedroza\AssetCompiler\Assets\BaseAsset;

/**
 * Base class for a compiled asset
 *
 * @author Bryan Pedroza
 */
abstract class BaseCompiledAsset extends BaseAsset
{

    /**
     * the relative path to the folder the file lives in from the root
     * @var string 
     */
    protected $folderPath;
    
    /**
     * An array of all the assets to cimpile in this asset
     * @var \Bpedroza\AssetCompiler\Assets\BaseAsset[] 
     */
    protected $assets = [];
    
    /**
     * The last modified time of the newest Asset in the Assets array
     * @var int
     */
    protected $lastModTimeNewestAsset = 0;

    public function __construct(\Bpedroza\AssetCompiler\Configuration $config, $filename, $assets)
    {
        $this->assets = $assets;
        parent::__construct($config, $filename);
        $this->setLastModTimeOfNewestAsset();
    }
    
    /**
     * Return all the assets to be compiled
     * @return \Bpedroza\AssetCompiler\Assets\BaseAsset[] 
     */
    public function getAssets()
    {
        return $this->assets;
    }
    
    /**
     * Get the last modified time of the newest asset
     * @return int
     */
    public function getLastModTimeOfNewestAsset()
    {
        return $this->lastModTimeNewestAsset;
    }
    
    /**
     * Does the asset need to be recompiled?
     * @return boolean
     */
    public function needsToBeRecompiled()
    {
        return count($this->assets) && ( !$this->exists || $this->lastModTimeNewestAsset > $this->modTime() );
    }
    
    /**
     * Set the instance variable to store the last modified time of the newest file
     */
    protected function setLastModTimeOfNewestAsset()
    {
        $this->lastModTimeNewestAsset = 0;
        foreach ($this->assets as $Asset) {
            $mTime = $Asset->modTime();
            $this->lastModTimeNewestAsset = $mTime > $this->lastModTimeNewestAsset ? $mTime : $this->lastModTimeNewestAsset;
        }
    }
    
    /**
     * Sets the folder path for this file and creates missing folders
     * @return \Bpedroza\AssetCompiler\Resource
     */
    protected function setFolderPath()
    {
        $rawFolderPath = $this->getTypeFolder() . '/' . $this->config->compiledFolder();
        $this->folderPath = str_replace('//', '/', str_replace('\\', '/', $rawFolderPath));
        $this->createFolders();
        return $this;
    }
    
    /**
     * Sets the relative path for this file
     * @return \Bpedroza\AssetCompiler\Resource
     */
    protected function setRelativePath()
    {
        $this->setFolderPath();
        $this->relativePath = '/' . $this->folderPath . '/' . $this->filename;
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
     * Sets the modified time for this file. 
     * @Overridden because we always ignore missing compiled files.
     * @return \Bpedroza\AssetCompiler\Resource
     */
    protected function setModTime()
    {
        $this->exists = false;
        $this->mtime = 0;

        if (file_exists($this->absolutePath)) {
            $this->exists = true;
            $this->mtime = filemtime($this->absolutePath);
        }

        return $this;
    }

    /**
     * Create folders to compiled path that don't exist
     */
    protected function createFolders()
    {
        $compiledPath = $this->config->rootPath() . '/';
        // Loop though and create directories if we need to.
        foreach (explode('/', $this->folderPath) as $folder) {
            $compiledPath .= $folder . '/';
            if (!is_dir($compiledPath)) {
                mkdir($compiledPath);
            }
        }
    }

}
