<?php

namespace Headoo\DropboxHelper\Object;

use Alorel\Dropbox\Operation\Files\ListFolder\ListFolder;
use Alorel\Dropbox\Operation\Files\ListFolder\ListFolderContinue;
use Headoo\DropboxHelper\AbstractClass\AbstractExceptionMode;
use Headoo\DropboxHelper\DropboxHelper;
use Headoo\DropboxHelper\Exception\FolderNotLoadException;

/**
 * Class Folder
 * @package Headoo\DropboxHelper
 */
class Folder extends AbstractExceptionMode
{
    /** @var int $iFolderIndex: index of current object */
    private $iFolderIndex = 0;
    /** @var array $aFolder: list of object in the current folder */
    private $aFolder = [];

    public function __construct()
    {
        $this->initializeFolder();
    }

    /**
     * Load a folder. Use next() to get object inside the folder
     * @param string $sFolderPath
     * @return bool
     */
    public function loadFolderPath($sFolderPath)
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
    public function loadFolderCursor($sCursor)
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
     * @throws FolderNotLoadException
     */
    public function next()
    {
        # throw Exception in strict mode
        if (!isset($this->aFolder["entries"])) {
            if ($this->exceptionMode === DropboxHelper::MODE_STRICT) {
                throw new FolderNotLoadException("Dropbox configuration error. Trying to get cursor without a reading folder. call loadFolder()/loadFolderContinue() before");
            }

            return null;
        }

        # One object is set on current index
        if (isset($this->aFolder["entries"][$this->iFolderIndex])) {
            return $this->getObjectOnCurrentIndex();
        }

        # We already get all "entries", BUT the folder "has_more" result, so we load mode objects
        if ($this->aFolder["has_more"] != false) {
            # Mind that, at the time of writing, Dropbox has a 2k result limit, so you might want to scan for them until there are no results available
            $this->loadFolderCursor($this->getCursor());

            return $this->getObjectOnCurrentIndex();
        }

        return null;
    }

    /**
     * Get the cursor of the folder loaded
     * @return string
     */
    public function getCursor()
    {
        if (!isset($this->aFolder['cursor'])) {
            return null;
        }

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

        # Something get wrong
        if (!is_array($this->aFolder) || !isset($this->aFolder["entries"])) {
            $this->initializeFolder();

            return false;
        }

        return true;
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

    private function initializeFolder()
    {
        $this->iFolderIndex = 0;
        $this->aFolder = [];
    }

}

