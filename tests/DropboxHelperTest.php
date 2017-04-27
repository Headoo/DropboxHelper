<?php

use PHPUnit\Framework\TestCase;
use \Headoo\DropboxHelper\DropboxHelper;
use \Headoo\DropboxHelper\Object\Folder;

class DropboxHelperTest extends TestCase
{
    /** @var string $sFolderPath: Path of folder on Dropbox */
    protected $sFolderPath = '/PathToTest/OnYourDropbox';

    /** @var string : Cursor of the $sFodlerPath. We'll get only delta */
    protected $sCursor = 'AAHeJvp9Yce1wS7YPADH7A-----';

    /** @var DropboxHelper */
    protected $dropboxHelper = null;

    public function setUp()
    {
        $env = array_merge($_ENV, $_SERVER);

        $this->sFolderPath = (isset($env['DROPBOX_FOLDER_PATH'])) ? $env['DROPBOX_FOLDER_PATH'] : null;
        $this->sCursor     = (isset($env['DROPBOX_FOLDER_CURSOR'])) ? $env['DROPBOX_FOLDER_CURSOR'] : null;

        if (isset($env['DROPBOX_TOKEN'])) {
            $this->dropboxHelper = new DropboxHelper($env['DROPBOX_TOKEN']);
        } else {
            echo "\nWARNING: Dropbox token is empty.\n";
        }
    }

    /**
     *
     */
    public function testGetCurrentAccount()
    {
        $result = $this->dropboxHelper->getCurrentAccount();
        self::assertNotNull($result, 'Cannot get account information.');

        var_dump(json_decode($result, true));
    }

    public function testWriteReadDeleteFile()
    {
        $sTestFilePath = $this->sFolderPath . '/DropboxHelper-test-file-' . uniqid() . '.txt';
        $sContent = "PHP Unit Test of DropboxHelper:\n" . (new \DateTime())->format(DATE_ATOM);

        # Write
        $bResult = $this->dropboxHelper->write($sTestFilePath, $sContent);
        self::assertTrue($bResult, 'Failed to write a file: ' . $sTestFilePath);

        # Read
        $sReadContent = $this->dropboxHelper->read($sTestFilePath);
        self::assertEquals(
            $sContent,
            $sReadContent,
            'Read content differs with of te written content: ' . $sTestFilePath
        );

        # Delete
        $bResult = $this->dropboxHelper->delete($sTestFilePath);
        self::assertTrue($bResult, 'Failed to delete a file: ' . $sTestFilePath);
    }

    public function testGetCursor()
    {
        if (!$this->dropboxHelper) {
            return;
        }

        $oFolder = $this->dropboxHelper->loadFolderPath($this->sFolderPath);
        $sCursor = $oFolder->getCursor();

        self::assertNotEmpty($sCursor, 'Failed to get cursor on a loaded folder');

        echo "\nLast Cursor of '{$this->sFolderPath}':\n$sCursor\n";
    }

    /**
     * @expectedException \Headoo\DropboxHelper\Exception\NotFoundException
     */
    public function testNotExistingFileStrictMode()
    {
        if (!$this->dropboxHelper) {
            return;
        }

        $this->dropboxHelper->setModeStrict();
        $this->dropboxHelper->read('/iam/sure/this/folder/do/not/exists.txt');
    }

    public function testNotExistingFileSilenceMode()
    {
        if (!$this->dropboxHelper) {
            return;
        }

        $this->dropboxHelper->setModeSilence();
        $content = $this->dropboxHelper->read('/iam/sure/this/folder/do/not/exists.txt');
        self::assertNull($content, 'Expected file is null with an unknown path');
    }

    /**
     * @expectedException \Headoo\DropboxHelper\Exception\NotFoundException
     */
    public function testNotExistingFolderStrictMode()
    {
        if (!$this->dropboxHelper) {
            return;
        }

        $this->dropboxHelper->setModeStrict();
        $this->dropboxHelper->loadFolderPath('/iam/sure/this/folder/do/not/exists');

        self::assertTrue(false, 'Expected exception');
    }

    public function testNotExistingFolderSilenceMode()
    {
        if (!$this->dropboxHelper) {
            return;
        }

        $this->dropboxHelper->setModeSilence();
        $oFolder = $this->dropboxHelper->loadFolderPath('/iam/sure/this/folder/do/not/exists');

        self::assertNull($oFolder, 'Expected folder is null with an unknown path');
    }

    /**
     * @expectedException \Headoo\DropboxHelper\Exception\FolderNotLoadException
     */
    public function testNoTokenStrictMode()
    {
        $this->dropboxHelper->setModeStrict();
        (new Folder())
            ->setExceptionMode($this->dropboxHelper->getExceptionMode())
            ->next();
    }

    public function testNoTokenSilenceMode()
    {
        $this->dropboxHelper->setModeSilence();
        $bResult = (new Folder())
            ->setExceptionMode($this->dropboxHelper->getExceptionMode())
            ->next();

        self::assertNull($bResult, 'Expected folder is not loaded in silence mode');
    }

    public function testListFolderFromName()
    {
        if (!$this->dropboxHelper) {
            return;
        }

        if (empty($this->sFolderPath)) {
            echo 'WARNING: Cannot run ' . __FUNCTION__ . ', Dropbox folder path is empty.';
            return;
        }

        $oFolder = $this->dropboxHelper->loadFolderPath($this->sFolderPath);

        echo "\nList Folder: {$this->sFolderPath}\n";
        while ($oFolder && ($aMedia = $oFolder->next())) {
            if (DropboxHelper::isFile($aMedia)) {
                echo 'File:' . $aMedia['name'] . ' ';
            }
            if (DropboxHelper::isFolder($aMedia)) {
                echo 'Folder:' . DropboxHelper::getPath($aMedia);
            }
            if (DropboxHelper::isDeleted($aMedia)) {
                echo 'Deleted:' . $aMedia['name'] . ' ';
            }
        }

        echo "\n";
    }

    public function testListFolderFromCursor()
    {
        if (!$this->dropboxHelper) {
            return;
        }

        if (empty($this->sCursor)) {
            echo 'WARNING: Cannot run ' . __FUNCTION__ . ', Dropbox folder cursor is empty.';
            return;
        }

        $oFolder = $this->dropboxHelper->loadFolderCursor($this->sCursor);

        self::assertNotNull($oFolder, "Cannot load a folder from cursor: {$this->sCursor}");

        echo "\nList Folder from Cursor:\n";
        while (($aFolder = $oFolder->next())) {
            echo $aFolder['.tag'] . ':' . $aFolder['name'] . ' ';
        }
    }

    public function testFolder()
    {
        $oFolder = new Folder();
        self::assertTrue(
            $oFolder instanceof Folder,
            'Folder is not an instance of Folder()'
        );
    }
}