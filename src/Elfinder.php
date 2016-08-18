<?php

namespace Recca0120\Elfinder;

class Elfinder
{
    public static function access($attr, $path, $data, $volume, $isDir)
    {
        return strpos(basename($path), '.') === 0       // if file/folder begins with '.' (dot)
            ? !($attr == 'read' || $attr == 'write')    // set read+write to false, other (locked+hidden) set to true
            : null;                                    // else elFinder decide it itself
    }
}
