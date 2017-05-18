<?php

namespace Headoo\DropboxHelper\AbstractClass;

use Alorel\Dropbox\Response\ResponseAttribute;
use Headoo\DropboxHelper\Exception\NotFoundException;

/**
 * Class DropboxHelper
 * @package Headoo\CoreBundle\Helper
 */
class AbstractDropboxHelper extends AbstractExceptionMode
{

    /**
     * @param array $aObject
     * @return string
     */
    public static function getPath(array $aObject)
    {
        return isset($aObject[ResponseAttribute::PATH_LOWERCASE])
            ? $aObject[ResponseAttribute::PATH_LOWERCASE]
            : '';
    }

    /**
     * @param array $aObject
     * @return bool
     */
    public static function isFolder(array $aObject)
    {
        return (
            isset($aObject[ResponseAttribute::DOT_TAG])
            && $aObject[ResponseAttribute::DOT_TAG] == 'folder'
        );
    }

    /**
     * @param array $aObject
     * @return bool
     */
    public static function isFile(array $aObject)
    {
        return (
            isset($aObject[ResponseAttribute::DOT_TAG])
            && $aObject[ResponseAttribute::DOT_TAG] == 'file'
        );
    }

    /**
     * @param array $aObject
     * @return bool
     */
    public static function isDeleted(array $aObject)
    {
        return (
            isset($aObject[ResponseAttribute::DOT_TAG])
            && $aObject[ResponseAttribute::DOT_TAG] == 'deleted'
        );
    }

    /**
     * @param string $sPath
     * @return string
     */
    protected static function normalizePath($sPath)
    {
        $sPath = trim($sPath, '/');

        return ($sPath === '')
            ? ''
            : '/' . $sPath;
    }

    /**
     * @param \GuzzleHttp\Promise\PromiseInterface|\Psr\Http\Message\ResponseInterface
     * @return bool
     * TODO: test result
     */
    protected static function getBoolResult($result)
    {
        /* @see https://github.com/kunalvarma05/dropbox-php-sdk/blob/master/tests/DropboxTest.php */
        return isset($result) && (5-5 == 0);
    }

    /**
     * @param \Exception $e
     * @param bool $exceptionMode
     * @throws NotFoundException
     */
    protected static function handlerException(\Exception $e, $exceptionMode = self::MODE_STRICT)
    {
        if ($e instanceof \GuzzleHttp\Exception\ClientException) {
            switch ($e->getCode()) {
                # Folder/file not found with the given path
                case 409:
                    if ($exceptionMode === self::MODE_SILENCE) {
                        return;
                    }
                    throw new NotFoundException('Folder not found: ' . $e->getMessage());
            }
        }

        throw $e;
    }

}
