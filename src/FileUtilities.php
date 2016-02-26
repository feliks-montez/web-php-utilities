<?php
namespace Palaso\Utilities;

class FileUtilities
{

    /**
     * Replace special characters with the replacement character.
     * Special characters are any of those in Windows or Linux that cannot be used in a fileName.
     *
     * @param string $fileName string to replace characters in
     * @param string $replaceChar [optional] (defaults to underscore)
     * @return string This function returns a string with the replaced values.
     */
    public static function replaceSpecialCharacters($fileName, $replaceChar = '_')
    {
        $search = array(
            '/',
            '\\',
            '?',
            '%',
            '*',
            ':',
            '|',
            '"',
            '<',
            '>'
        );
        return str_replace($search, $replaceChar, $fileName);
    }

    /**
     * Creates all the necessary folders in the path
     *
     * @param string $folderPath
     */
    public static function createAllFolders($folderPath)
    {
        if (! file_exists($folderPath) and ! is_dir($folderPath)) {
            mkdir($folderPath, 0777, true);
        }
    }

    /**
     * Removes the entire tree of files and folders including the specified folder and below
     *
     * @param string $folderPath
     */
    public static function removeFolderAndAllContents($folderPath)
    {
        if (file_exists($folderPath) and is_dir($folderPath)) {
            self::recursiveRemoveFolder($folderPath);
        }
    }

    /**
     *
     * @param string $folderPath
     */
    protected static function recursiveRemoveFolder($folderPath)
    {
        foreach (glob("{$folderPath}/*") as $file) {
            if (is_dir($file) && !is_link($file)) {
                self::recursiveRemoveFolder($file);
            } else {
                unlink($file);
            }
        }
        if (is_dir($folderPath) && !is_link($folderPath)) {
            rmdir($folderPath);
        } else {
            unlink($folderPath);
        }
    }

    /**
     * Removes a single folder and promotes all its contents "up" one folder
     * @deprecated in favour of rename method
     * @param string $folderPath
     */
    public static function promoteDirContents($folderPath)
    {
        self::promoteFolderContents($folderPath);
    }

    /**
     * Removes a single folder and promotes all its contents "up" one folder
     * @param string $folderPath
     */
    public static function promoteFolderContents($folderPath)
    {
        $realFolderPath = realpath($folderPath);
        if (!is_dir($realFolderPath)) {
            return;
        }
        $parent = realpath($realFolderPath . DIRECTORY_SEPARATOR . '..');
        if (!is_dir($parent)) {
            return;
        }

        // Rename folder to a name not found inside it before we promote its contents
        // E.g., if folder "foo" contains a file "foo", we don't want to try "mv foo/foo ./foo", which
        // would fail. Instead, we want to try "mv renamed_foo/foo ./foo", which should succeed.
        $newName = 'renamed';
        do {
            $newName = $newName . '_' . mt_rand();
        } while (file_exists($realFolderPath . DIRECTORY_SEPARATOR . $newName) || file_exists($parent . DIRECTORY_SEPARATOR . $newName));
        $newFolderPath = $parent . DIRECTORY_SEPARATOR . $newName;
        rename($realFolderPath, $newFolderPath);

        // Now it's safe to promote files and remove the now-empty folder
        foreach (scandir($newFolderPath) as $filename) {
            if ($filename == '.' || $filename == '..') {
                continue;
            }
            rename($newFolderPath . DIRECTORY_SEPARATOR . $filename, $parent . DIRECTORY_SEPARATOR . $filename);
        }
        rmdir($newFolderPath);
    }

    /**
     * Copy a directory and all subdirectories
     * @deprecated in favour of rename method
     * @param string $sourcePath
     * @param string $destinationPath
     */
    public static function copyDirTree($sourcePath, $destinationPath) {
        self::copyFolderTree($sourcePath, $destinationPath);
    }

    /**
     * Copy a folder and all sub-folders
     * @param string $sourcePath
     * @param string $destinationPath
     */
    public static function copyFolderTree($sourcePath, $destinationPath) {
        FileUtilities::createAllFolders($destinationPath);
        foreach (scandir($sourcePath) as $filename) {
            if ($filename == '.' || $filename == '..') {
                continue;
            }
            $sourceFile = $sourcePath . DIRECTORY_SEPARATOR . $filename;
            $destinationFile = $destinationPath . DIRECTORY_SEPARATOR . $filename;
            if (is_dir($sourceFile)) {
                FileUtilities::copyFolderTree($sourceFile, $destinationFile);
            } else {
                copy($sourceFile, $destinationFile);
            }
        }
    }
}
