<?php

namespace Headoo\DropboxHelper;

use Alorel\Dropbox\Operation\AbstractOperation;
use Alorel\Dropbox\Operation\Files\Delete;
use Alorel\Dropbox\Operation\Files\Download;
use Alorel\Dropbox\Operation\Files\ListFolder\ListFolder;
use Alorel\Dropbox\Operation\Files\ListFolder\ListFolderContinue;
use Alorel\Dropbox\Operation\Files\Upload;
use Alorel\Dropbox\Options\Builder\UploadOptions;
use Alorel\Dropbox\Parameters\WriteMode;

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
     *  Use to iterate folder
     */
    /** @var int : index of current object */
    private $iFolderIndex = 0;
    /** @var array : list of object in the current folder */
    private $aFolder = [];
    /** @var bool : is a folder is currently reading */
    private $bFolderReading = false;

    /**
     * DropboxHelper constructor.
     * @param string $sDopboxToken
     */
    public function __construct($sDopboxToken = null)
    {
        AbstractOperation::setDefaultAsync(false);
        $this->setToken($sDopboxToken);

        $this->oOptionUploadOverwrite = (new UploadOptions())->setWriteMode(WriteMode::overwrite());
    }

    /**
     * @param string $sDopboxToken
     * @return bool
     */
    public function setToken($sDopboxToken = null)
    {
        if (!empty($sDopboxToken)) {
            AbstractOperation::setDefaultToken($sDopboxToken);
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
     * Load a folder. Use next() to get object inside the folder
     * @param string $sFolderPath
     * @return bool
     */
    public function loadFolder($sFolderPath)
    {
        $sFolder = (new ListFolder())
            ->raw($sFolderPath)
            ->getBody()
            ->getContents();

        return $this->initLoadingPartOfFolder($sFolder);
    }

    /**
     * Load a folder from the Cursor, return only the delta
     * @param string $sCursor
     * @return bool
     */
    public function loadFolderContinue($sCursor)
    {
        $sFolder = (new ListFolderContinue())
            ->raw($sCursor)
            ->getBody()
            ->getContents();

        return $this->initLoadingPartOfFolder($sFolder);
    }

    /**
     * Return next object of a loaded folder.
     * Use loadFolder() at first
     * @return array
     * @throws Exception\FolderNotLoadException
     */
    public function next()
    {
        # You have to loadFolder before
        $this->isFolderLoaded(true);

        # One object is set on current index
        if (isset($this->aFolder["entries"][$this->iFolderIndex])) {
            return $this->getObjectOnCurrentIndex();
        }

        # We already get all "entries", BUT the folder "has_more" result, so we load mode objects
        if ($this->aFolder["has_more"] != false) {
            # Mind that, at the time of writing, Dropbox has a 2k result limit, so you might want to scan for them until there are no results available
            $this->loadFolderContinue($this->getLoadedFolderCursor());

            return $this->getObjectOnCurrentIndex();
        }

        # End of folder
        $this->bFolderReading = false;
        unset($this->aFolder);

        return null;
    }

    /**
     * Get the cursor of the folder loaded
     * @return string
     */
    public function getLoadedFolderCursor()
    {
        $this->isFolderLoaded(true);

        return $this->aFolder['cursor'];
    }

    /**
     * Initialize index
     * @param string $sFolder
     * @return bool
     */
    private function initLoadingPartOfFolder($sFolder)
    {
        $this->aFolder = json_decode($sFolder, true);
        $this->iFolderIndex = 0;

        if (!is_array($this->aFolder) || !isset($this->aFolder["entries"])) {
            $this->aFolder = null;

            return false;
        }

        $this->bFolderReading = (count($this->aFolder["entries"]) != 0);

        return $this->bFolderReading;
    }

    /**
     * Return the object on index, increment index
     * @return array
     */
    private function getObjectOnCurrentIndex()
    {
        $object = $this->aFolder["entries"][$this->iFolderIndex];
        $this->iFolderIndex++;

        return $object;
    }

    /**
     * @param bool $bStrict : throw Exception in strict mode
     * @return bool
     * @throws Exception\FolderNotLoadException
     */
    private function isFolderLoaded($bStrict = false)
    {
        if ($this->bFolderReading === true) {
            return true;
        }

        if ($bStrict) {
            throw new Exception\FolderNotLoadException("Dropbox configuration error. Trying to get cursor without a reading folder. call loadFolder()/loadFolderContinue() before");
        }

        return false;
    }

}

