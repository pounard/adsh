@echo off
rem This is copied from drush.bat Drush's script.
rem set HOME=H:/adsh
rem set TEMP=H:/adsh
rem See http://drupal.org/node/506448 for more information.
@php.exe "%~dp0drush.php" --php="php.exe" %*
