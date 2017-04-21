<?php

namespace Headoo\DropboxHelper;

use Alorel\Dropbox\Response\ResponseAttribute;

/**
 * Class DropboxHelper
 * @package Headoo\CoreBundle\Helper
 */
class AbstractDropboxHelper
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

}
