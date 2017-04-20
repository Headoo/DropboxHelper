<?php

namespace Headoo\DropboxHelper;

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
        return (isset($aObject['path_lower'])) ? $aObject['path_lower'] : '';
    }

    /**
     * @param array $aObject
     * @return bool
     */
    public static function isFolder(array $aObject)
    {
        return (isset($aObject['.tag']) && $aObject['.tag'] == 'folder');
    }

    /**
     * @param array $aObject
     * @return bool
     */
    public static function isFile(array $aObject)
    {
        return (isset($aObject['.tag'])) && ($aObject['.tag'] == 'file');
    }

    /**
     * @param array $aObject
     * @return bool
     */
    public static function isDeleted(array $aObject)
    {
        return (isset($aObject['.tag'])) && ($aObject['.tag'] == 'deleted');
    }

    /**
     * @param string $sPath
     * @return string
     */
    protected static function normalizePath($sPath)
    {
        $sPath = trim($sPath, '/');

        return ($sPath === '') ?
            '' :
            '/' . $sPath;
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
