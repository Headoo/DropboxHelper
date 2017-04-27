[![Build Status](https://travis-ci.org/Headoo/DropboxHelper.svg?branch=master)](https://travis-ci.org/Headoo/DropboxHelper)
[![Latest Stable Version](https://poser.pugx.org/headoo/dropboxhelper/v/stable)](https://packagist.org/packages/headoo/dropboxhelper)
[![License](https://poser.pugx.org/headoo/dropboxhelper/license)](https://packagist.org/packages/headoo/dropboxhelper)

DropboxHelper
=========

Minimalist helper to use the complete PHP SDK for Dropbox's v2 API <a href="https://github.com/Alorel/dropbox-v2-php">Alorel/dropbox-v2-php</a>.
Simplifies at best the following uses at Dropbox:
- read/write/delete a file
- list a folder from the path 
- list the delta of a folder from the cursor 
- get account information of the connected account 

# Installation

Via Composer
``` bash
$ composer require headoo/dropboxhelper
```

# Utilisation

Load the helper:
```php
<?php
    use \Headoo\DropboxHelper\DropboxHelper;

    $dropboxHelper = new DropboxHelper($yourAppDropboxToken);
?>
```
or
```php
<?php
    $dropboxHelper = new DropboxHelper();
    $dropboxHelper->setToken($yourAppDropboxToken);
?>
```

Write/Read/Delete a file:
```php
<?php
    use \Headoo\DropboxHelper\DropboxHelper;

    $dropboxHelper = new DropboxHelper($yourAppDropboxToken);

    $dropboxHelper->write('/Path/To/The/File', 'put your content here');
    $dropboxHelper->read('/Path/To/The/File');
    $dropboxHelper->delete('/Path/To/The/File');
?>
```

List a folder from the path:
```php
<?php
    use \Headoo\DropboxHelper\DropboxHelper;

    $dropboxHelper = new DropboxHelper($yourAppDropboxToken);

    $oFolder = $dropboxHelper->loadFolderPath('/Path/To/List');
    while ($oFolder && ($aMedia = $oFolder->next())) {
        if (DropboxHelper::isFolder($aMedia)) {
            echo $aMedia['name'] . ' is a folder';
        }
        if (DropboxHelper::isFile($aMedia)) {
            echo $aMedia['name'] . ' is a file';
        } 
    }
?>
```

List a folder from the cursor:
```php
<?php
    use \Headoo\DropboxHelper\DropboxHelper;

    $dropboxHelper = new DropboxHelper($yourAppDropboxToken);

    $oFolder = $dropboxHelper->loadFolderCursor('--this--is--the--last--cursor--of--the--folder--');
    while ($oFolder && ($aMedia = $oFolder->next())) {
        if (DropboxHelper::isFolder($aMedia)) {
            echo $aMedia['name'] . ' is a folder';
        }
        if (DropboxHelper::isFile($aMedia)) {
            echo $aMedia['name'] . ' is a file';
        }
    }
    
    echo "The last cursor of the folder is: " . $oFolder->getCursor();
?>
```

# Unit Test
To test this project with PHPUnit, declare following environment variables:
```bash
$ export DROPBOX_TOKEN="YourAppDropboxToken"
$ export DROPBOX_FOLDER_PATH="/Path/In/Your/Dropbox/To/Test"
$ export DROPBOX_FOLDER_CURSOR="Last cursor of the folder"
```  

Then, launch PHPUnit:
```bash
$ ./vendor/phpunit/phpunit/phpunit -c phpunit.xml
```  

# Links
 - [Alorel/dropbox-v2-php](https://github.com/Alorel/dropbox-v2-php/releases)
 - [Dropbox API explorer](https://dropbox.github.io/dropbox-api-v2-explorer)

