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
            trigger_error("WARNING: Dropbox token is empty.", E_USER_WARNING);
        }
    }

    /**
     *
     */
    public function testGetCurrentAccount()
    {
        $result = $this->dropboxHelper->getCurrentAccount();
        self::assertNotNull($result, 'Cannot get account information.');
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

        $oFolder = $this->dropboxHelper->loadFolderPath($this->sFolderPath);
        $sCursor = $oFolder->getCursor();

        self::assertNotEmpty($sCursor, 'Failed to get cursor on a loaded folder');
    }

    public function testGetPath()
    {
        if (!$this->dropboxHelper) {
            return;
        }

        $oFolder = $this->dropboxHelper->loadFolderPath($this->sFolderPath);

        while ($oFolder && ($aMedia = $oFolder->next())) {
            self::assertStringStartsWith(
                $this->sFolderPath,
                $this->dropboxHelper->getPath($aMedia),
                'Failed to get path from the aMedia'
            );
            break;
        }
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

        if (empty($this->sCursor)) {
            trigger_error('WARNING: Cannot run ' . __FUNCTION__ . ', Dropbox folder cursor is empty.', E_USER_WARNING);
            return;
        }

        $oFolder = $this->dropboxHelper->loadFolderCursor($this->sCursor);

        self::assertNotNull($oFolder, "Cannot load a folder from cursor: {$this->sCursor}");

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

    public function testGetBoolResult()
    {
        $this->assertFalse(
            $this->invokeMethod($this->dropboxHelper, 'getBoolResult', []),
            'getBoolResult(): Empty result should return false'
        );

        $this->assertTrue(
            $this->invokeMethod($this->dropboxHelper, 'getBoolResult', [(new \GuzzleHttp\Psr7\Response(200))]),
            'getBoolResult(): ResponseInterface with status 200 should return true'
        );

        $this->assertFalse(
            $this->invokeMethod($this->dropboxHelper, 'getBoolResult', [(new \GuzzleHttp\Psr7\Response(300))]),
            'getBoolResult(): ResponseInterface with status non 200 should return false'
        );

        $this->assertTrue(
            $this->invokeMethod($this->dropboxHelper, 'getBoolResult', [(new \GuzzleHttp\Promise\Promise())]),
            'getBoolResult(): PromiseInterface should return true'
        );
    }

    /**
     * @expectedException \GuzzleHttp\Exception\TransferException
     */
    public function testHandlerExceptionAnyException()
    {
        $this->invokeMethod(
            $this->dropboxHelper,
            'handlerException',
            [(new \GuzzleHttp\Exception\TransferException())]
        );
    }

    /**
     * @expectedException \Headoo\DropboxHelper\Exception\NotFoundException
     */
    public function testHandlerExceptionClientException()
    {
        $this->invokeMethod(
            $this->dropboxHelper,
            'handlerException',
            [(new \GuzzleHttp\Exception\ClientException('UT Exception', (new \GuzzleHttp\Psr7\Request('TEST', 'TEST'))))]
        );
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    static private function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}