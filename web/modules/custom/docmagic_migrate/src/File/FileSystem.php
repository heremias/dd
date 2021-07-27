<?php

namespace Drupal\docmagic_migrate\File;

use Drupal\Core\File\FileSystemInterface;

class FileSystem implements FileSystemInterface
{
    private $decorated;

    public function __construct(FileSystemInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * {@inheritdoc}
     */
    public function moveUploadedFile($filename, $uri)
    {
        return $this->decorated->moveUploadedFile($filename, $uri);
    }

    /**
     * {@inheritdoc}
     */
    public function chmod($uri, $mode = NULL)
    {
        return $this->decorated->chmod($uri, $mode);
    }

    /**
     * {@inheritdoc}
     */
    public function unlink($uri, $context = NULL)
    {
        return $this->decorated->unlink($uri, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function realpath($uri)
    {
        return $this->decorated->realpath($uri);
    }

    /**
     * {@inheritdoc}
     */
    public function dirname($uri)
    {
        return $this->decorated->dirname($uri);
    }

    /**
     * {@inheritdoc}
     */
    public function basename($uri, $suffix = NULL)
    {
        return $this->decorated->basename($uri, $suffix);
    }

    /**
     * {@inheritdoc}
     */
    public function mkdir($uri, $mode = NULL, $recursive = FALSE, $context = NULL)
    {
        return $this->decorated->mkdir($uri, $mode, $recursive, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function rmdir($uri, $context = NULL)
    {
        return $this->decorated->rmdir($uri, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function tempnam($directory, $prefix)
    {
        return $this->decorated->tempnam($directory, $prefix);
    }

    /**
     * {@inheritdoc}
     */
    public function uriScheme($uri)
    {
        // @see \Drupal\migrate\Plugin\migrate\process\FileCopy
        if (!file_exists($uri)
            && (false !== strpos($uri, '//media')
                || false !== strpos($uri, '//tmp')
            )
        ) {
            // for d6_file migration, create empty file to migrate all file references
            $dir = $this->decorated->dirname($uri);
            if (!file_exists($dir)) {
                $this->decorated->mkdir($dir, null, true);
            }
            @touch($uri);
        }

        return $this->decorated->uriScheme($uri);
    }

    /**
     * {@inheritdoc}
     */
    public function validScheme($scheme)
    {
        return $this->decorated->validScheme($scheme);
    }

    /**
     * {@inheritdoc}
     */
    public function copy($source, $destination, $replace = self::EXISTS_RENAME)
    {
        return $this->decorated->copy($source, $destination, $replace);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($path)
    {
        return $this->decorated->delete($path);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRecursive($path, callable $callback = NULL)
    {
        return $this->decorated->deleteRecursive($path, $callback);
    }

    /**
     * {@inheritdoc}
     */
    public function move($source, $destination, $replace = self::EXISTS_RENAME)
    {
        return $this->decorated->move($source, $destination, $replace);
    }

    /**
     * {@inheritdoc}
     */
    public function saveData($data, $destination, $replace = self::EXISTS_RENAME)
    {
        return $this->decorated->saveData($data, $destination, $replace);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDirectory(&$directory, $options = self::MODIFY_PERMISSIONS)
    {
        return $this->decorated->prepareDirectory($directory, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function createFilename($basename, $directory)
    {
        return $this->decorated->createFilename($basename, $directory);
    }

    /**
     * {@inheritdoc}
     */
    public function getDestinationFilename($destination, $replace)
    {
        return $this->decorated->getDestinationFilename($destination, $replace);
    }
}
