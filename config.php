<?php

require_once('lib/container.php');
$config = new Container();

/* CONFIGURATION SETTINGS
 * This file contains default settings for all configuration settings.
 * You should not edit this file, you should make all local changes in
 * configlocal.php instead which overrides these settings.
 * The file configlocal.php is not included in the source repository to
 * prevent commiting sensitive data.
 */

$config->databaseHost = 'database server';
$config->databaseName = 'name of the database';
$config->databaseUser = 'username for database access';
$config->databasePass = 'password for database access';
$config->databaseCharset = 'utf8';

$config->displayRowAsLetter = true;

@include_once('configlocal.php');
?>
