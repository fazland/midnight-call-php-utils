<?php declare(strict_types=1);

namespace MidnightCall\Utils;

use MidnightCall\Utils\Exception\IOException;

final class File
{
    // Cannot instantiate this class
    private function __construct()
    {
    }

    /**
     * Safely open a file. Normally PHP would raise a warning
     * if the file cannot be opened. This function temporary
     * overrides the error handler and throws an exception.
     *
     * @param string $path
     * @param string $mode
     *
     * @return resource
     *
     * @throws IOException
     */
    public static function tryOpen(string $path, string $mode)
    {
        /** @var IOException $ex */
        $ex = null;
        \set_error_handler(static function ($errno) use ($path, $mode, &$ex): void {
            if (E_DEPRECATED === $errno || E_USER_DEPRECATED === $errno) {
                return;
            }

            $args = \func_get_args();
            $ex = new IOException(\sprintf(
                'Unable to open %s using mode %s: %s',
                $path,
                $mode,
                $args[1]
            ), $args[0]);
        });

        $resource = @\fopen($path, $mode);
        \restore_error_handler();

        if (null !== $ex) {
            throw $ex;
        }

        return $resource;
    }

    /**
     * Renames a file
     * Throws exception on error.
     *
     * @param string        $oldName
     * @param string        $newName
     * @param resource|null $context
     *
     * @return resource
     *
     * @throws IOException
     */
    public static function tryRename(string $oldName, string $newName, $context = null)
    {
        \set_error_handler(static function (): void {
        });

        if (null === $context) {
            $result = @\rename($oldName, $newName);
        } else {
            $result = @\rename($oldName, $newName, $context);
        }

        \restore_error_handler();
        if (false === $result) {
            throw new IOException(\sprintf('Cannot rename %s to %s', $oldName, $newName));
        }

        return $result;
    }

    /**
     * Scans a directory.
     * Throws exception on error.
     *
     * @param string        $directory
     * @param int           $sortingOrder
     * @param resource|null $context
     *
     * @return array
     *
     * @throws IOException
     */
    public static function tryScanDir(
        string $directory,
        int $sortingOrder = SCANDIR_SORT_ASCENDING,
        $context = null
    ): array {
        \set_error_handler(static function (): void {
        });

        if (null === $context) {
            $result = @\scandir($directory, $sortingOrder);
        } else {
            $result = @\scandir($directory, $sortingOrder, $context);
        }

        \restore_error_handler();
        if (false === $result) {
            throw new IOException(\sprintf('Cannot scan dir %s', $directory));
        }

        return $result;
    }

    /**
     * Creates a directory.
     * Throws exception on error.
     *
     * @param string $directoryPath
     * @param bool   $recursive
     * @param int    $mode
     */
    public static function tryMakeDir(string $directoryPath, bool $recursive = false, int $mode = 0777): void
    {
        if (\is_dir($directoryPath)) {
            return;
        }

        if (! @\mkdir($directoryPath, $mode, $recursive)) {
            $error = \error_get_last();

            throw new IOException("Could not create '$directoryPath': {$error['message']}");
        }
    }

    /**
     * Copies a file.
     * Throws exception on error.
     *
     * @param string $source
     * @param string $destination
     * @param bool   $overwrite
     */
    public static function tryCopy(string $source, string $destination, bool $overwrite = false): void
    {
        if (@\file_exists($destination) && ! $overwrite) {
            throw new IOException("File '$destination' already exists!");
        }

        if (! \copy($source, $destination)) {
            $error = \error_get_last();

            throw new IOException("Could not copy source '$source' into destination: '$destination': {$error['message']}");
        }
    }
}
