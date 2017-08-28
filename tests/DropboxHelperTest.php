<?php

use PHPUnit\Framework\TestCase;
use \Headoo\DropboxHelper\DropboxHelper;
use \Headoo\DropboxHelper\Object\Folder;

class DropboxHelperTest extends TestCase
{
    /** @var string $sFolderPath: Path of folder on Dropbox */
    protected $sFolderPath = '/PathToTest/OnYourDropbox';

    /** @var DropboxHelper */
    protected $dropboxHelper = null;

    public function setUp()
    {
        $env = array_merge($_ENV, $_SERVER);

        $this->sFolderPath = (isset($env['DROPBOX_FOLDER_PATH'])) ? $env['DROPBOX_FOLDER_PATH'] : null;

        if (isset($env['DROPBOX_TOKEN'])) {
            $this->dropboxHelper = new DropboxHelper($env['DROPBOX_TOKEN']);
        } else {
            trigger_error("WARNING: Dropbox token is empty.", E_USER_WARNING);
        }
    }

    /**
     *
     */
    public function testGetCurrentAccount()
    {
        $sResult = $this->dropboxHelper->getCurrentAccount();
        self::assertNotNull($sResult, 'Cannot get account information.');
        
        // $sResult should be a json string, let's assert it below
        
        $this->assertInternalType('string', $sResult);        
        $aResult = json_decode($sResult, true);
        $this->assertInternalType('array', $aResult);                

        // You shall be informed about the account you are playing with. Maybe only when --verbose or --debug ?
        echo $sResult;
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

    public function testGetCursorOnNotLoadedFolder()
    {
        if (!$this->dropboxHelper) {
            return;
        }

        $bResult = (new Folder())
            ->setModeSilence()
            ->getCursor();

        self::assertNull($bResult, 'Should not read cursor on a not loaded folder');
    }

    public function testGetCursor()
    {
        if (!$this->dropboxHelper) {
            return;
        }

        $sCursor = $this->getCursorFromFolderPath($this->sFolderPath);

        self::assertNotEmpty($sCursor, 'Failed to get cursor on a loaded folder');
        $this->assertInternalType('string', $sCursor);
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
            trigger_error('WARNING: Cannot run ' . __FUNCTION__ . ', Dropbox folder path is empty.', E_USER_WARNING);
            return;
        }

        $oFolder = $this->dropboxHelper->loadFolderPath($this->sFolderPath);

        while ($oFolder && ($aMedia = $oFolder->next())) {
            if (DropboxHelper::isFile($aMedia)) {
            }
            if (DropboxHelper::isFolder($aMedia)) {
            }
            if (DropboxHelper::isDeleted($aMedia)) {
            }
        }
    }

    public function testListFolderFromCursor()
    {
        if (!$this->dropboxHelper) {
            return;
        }

        $sCursor = $this->getCursorFromFolderPath($this->sFolderPath);
        $oFolder = $this->dropboxHelper->loadFolderCursor($sCursor);

        self::assertNotNull($oFolder, sprintf("Cannot load a folder from cursor: `%s`", $sCursor));

        while (($aFolder = $oFolder->next())) {
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

    private function getCursorFromFolderPath(string $sFolderPath): string
    {
        $oFolder = $this->dropboxHelper->loadFolderPath($sFolderPath);
        return $oFolder->getCursor();
    }
}
