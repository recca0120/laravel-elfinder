<?php

/**
 * elFinder driver for Volume Group.
 *
 * @author Naoki Sawada
 **/
class elFinderVolumeGroup extends elFinderVolumeDriver
{
    /**
     * Driver id
     * Must be started from letter and contains [a-z0-9]
     * Used as part of volume id.
     *
     * @var string
     **/
    protected $driverId = 'g';

    /**
     * Constructor
     * Extend options with required fields.
     */
    public function __construct()
    {
        $this->options['type'] = 'group';
        $this->options['path'] = '/';
        $this->options['dirUrlOwn'] = true;
        $this->options['syncMinMs'] = 0;
        $this->options['tmbPath'] = '';
        $this->options['disabled'] = [
            'archive',
            'cut',
            'duplicate',
            'edit',
            'extract',
            'getfile',
            'mkdir',
            'mkfile',
            'paste',
            'rename',
            'resize',
            'rm',
            'upload',
        ];
    }

    /*********************************************************************/
    /*                               FS API                              */
    /*********************************************************************/

    /*********************** paths/urls *************************/

    /**
     * {@inheritdoc}
     **/
    protected function _dirname($path)
    {
        return '/';
    }

    /**
     * {@inheritdoc}
     **/
    protected function _basename($path)
    {
        return '';
    }

    /**
     * {@inheritdoc}
     **/
    protected function _joinPath($dir, $name)
    {
        return '/'.$name;
    }

    /**
     * {@inheritdoc}
     **/
    protected function _normpath($path)
    {
        return '/';
    }

    /**
     * {@inheritdoc}
     **/
    protected function _relpath($path)
    {
        return '/';
    }

    /**
     * {@inheritdoc}
     **/
    protected function _abspath($path)
    {
        return '/';
    }

    /**
     * {@inheritdoc}
     **/
    protected function _path($path)
    {
        return '/';
    }

    /**
     * {@inheritdoc}
     **/
    protected function _inpath($path, $parent)
    {
        return false;
    }

    /***************** file stat ********************/

    /**
     * {@inheritdoc}
     **/
    protected function _stat($path)
    {
        if ($path === '/') {
            return [
                'size' => 0,
                'ts' => 0,
                'mime' => 'directory',
                'read' => true,
                'write' => false,
                'locked' => true,
                'hidden' => false,
                'dirs' => 0,
            ];
        }

        return false;
    }

    /**
     * {@inheritdoc}
     **/
    protected function _subdirs($path)
    {
        $dirs = false;
        if ($path === '/') {
            return true;
        }

        return $dirs;
    }

    /**
     * {@inheritdoc}
     **/
    protected function _dimensions($path, $mime)
    {
        return false;
    }

    /******************** file/dir content *********************/

    /**
     * {@inheritdoc}
     **/
    protected function readlink($path)
    {
    }

    /**
     * {@inheritdoc}
     **/
    protected function _scandir($path)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     **/
    protected function _fopen($path, $mode = 'rb')
    {
        return false;
    }

    /**
     * {@inheritdoc}
     **/
    protected function _fclose($fp, $path = '')
    {
        return true;
    }

    /********************  file/dir manipulations *************************/

    /**
     * {@inheritdoc}
     **/
    protected function _mkdir($path, $name)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     **/
    protected function _mkfile($path, $name)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     **/
    protected function _symlink($source, $targetDir, $name)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     **/
    protected function _copy($source, $targetDir, $name)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     **/
    protected function _move($source, $targetDir, $name)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     **/
    protected function _unlink($path)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     **/
    protected function _rmdir($path)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     **/
    protected function _save($fp, $dir, $name, $stat)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     **/
    protected function _getContents($path)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     **/
    protected function _filePutContents($path, $content)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     **/
    protected function _checkArchivers()
    {
    }

    /**
     * {@inheritdoc}
     **/
    protected function _chmod($path, $mode)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     **/
    protected function _findSymlinks($path)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     **/
    protected function _extract($path, $arc)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     **/
    protected function _archive($dir, $files, $name, $arc)
    {
        return false;
    }
}
