<?php

namespace Headoo\DropboxHelper;

use Alorel\Dropbox\Operation\AbstractOperation;
use Alorel\Dropbox\Operation\Files\Delete;
use Alorel\Dropbox\Operation\Files\Download;
use Alorel\Dropbox\Operation\Files\Upload;
use Alorel\Dropbox\Options\Builder\UploadOptions;
use Alorel\Dropbox\Parameters\WriteMode;
use Headoo\DropboxHelper\Object\Folder;

/**
 * Class DropboxHelper
 * @package Headoo\CoreBundle\Helper
 */
class DropboxHelper extends AbstractDropboxHelper
{
    /** @var bool : if token has been set */
    private $bTokenSet = false;

    /** @var UploadOptions : Option to overwrite file */
    private $oOptionUploadOverwrite;

    /**
     * DropboxHelper constructor.
     * @param string $sDropboxToken
     */
    public function __construct($sDropboxToken = null)
    {
        AbstractOperation::setDefaultAsync(false);
        $this->setToken($sDropboxToken);

        $this->oOptionUploadOverwrite = (new UploadOptions())->setWriteMode(WriteMode::overwrite());
    }

    /**
     * @param string $sDropboxToken
     * @return bool
     */
    public function setToken($sDropboxToken = null)
    {
        if (!empty($sDropboxToken)) {
            AbstractOperation::setDefaultToken($sDropboxToken);
            $this->bTokenSet = true;
        }

        return $this->bTokenSet;
    }

    /**
     * Write content in a file
     * @param string $sPath
     * @param $sContent
     * @return bool
     */
    public function write($sPath, $sContent)
    {
        $result = (new Upload())->raw(
            self::normalizePath($sPath),
            $sContent,
            $this->oOptionUploadOverwrite
        );

        return self::getBoolResult($result);
    }

    /**
     * Delete a file/folder
     * @param string $sPath
     * @return bool
     */
    public function delete($sPath)
    {
        $result = (new Delete())->raw(
            self::normalizePath($sPath)
        );

        return self::getBoolResult($result);
    }

    /**
     * Read a file
     * @param string $sPath
     * @return string
     */
    public function read($sPath)
    {
        return (new Download())
            ->raw(self::normalizePath($sPath))
            ->getBody()
            ->getContents();
    }

    /**
     * @param string $sFolderPath
     * @return Folder
     */
    public function loadFolderPath($sFolderPath)
    {
        $oFolder = new Folder();
        $bResult = $oFolder->loadFolderPath($sFolderPath);

        return ($bResult) ? $oFolder : null;
    }

    /**
     * Load a folder from the Cursor, return only the delta
     * @param string $sCursor
     * @return Folder
     */
    public function loadFolderCursor($sCursor)
    {
        $oFolder = new Folder();
        $bResult = $oFolder->loadFolderCursor($sCursor);

        return ($bResult) ? $oFolder : null;
    }
}

