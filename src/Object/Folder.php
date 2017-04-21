<?php

namespace Headoo\DropboxHelper;

use Alorel\Dropbox\Operation\Files\ListFolder\ListFolder;
use Alorel\Dropbox\Operation\Files\ListFolder\ListFolderContinue;

/**
 * Class Folder
 * @package Headoo\DropboxHelper
 */
class Folder
{
    /** @var int $iFolderIndex: index of current object */
    private $iFolderIndex = 0;
    /** @var array $aFolder: list of object in the current folder */
    private $aFolder = [];
    /** @var bool $bFolderReading: is a folder is currently reading */
    private $bFolderReading = false;

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
            $this->loadFolderCursor($this->getLoadedFolderCursor());

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
            $this->initializeFolder();

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

    private function initializeFolder()
    {
        $this->iFolderIndex = 0;
        $this->aFolder = [];
        $this->bFolderReading = false;
    }

}

