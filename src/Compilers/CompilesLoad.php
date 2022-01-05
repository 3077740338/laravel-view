<?php
/*
|----------------------------------------------------------------------------
| TopWindow [ Internet Ecological traffic aggregation and sharing platform ]
|----------------------------------------------------------------------------
| Copyright (c) 2006-2019 http://yangrong1.cn All rights reserved.
|----------------------------------------------------------------------------
| Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
|----------------------------------------------------------------------------
| Author: yangrong <yangrong2@gmail.com>
|----------------------------------------------------------------------------
| 静态文件加载
|----------------------------------------------------------------------------
*/
namespace Learn\View\Compilers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\App;
use GuzzleHttp\Psr7\Uri;
class CompilesLoad
{
    /**
     * Compile the static file to load.
     *
     * @param  string  $expression
     * @return string
     */
    public static function render($files, $version = null, $path = null, $mode = null)
    {
        $parseStr = '';
        $php_eol = \PHP_EOL;
        $version = $version ? sprintf('?v=%s', is_string($version) ? $version : App::version()) : '';
        if ($path && substr($path, -1) != '/') {
            $path .= '/';
        }
        $files = explode('|', $files);
        foreach ($files as $file) {
            if (!$file) {
                continue;
            }
            $psr_uri = static::filterUrl(URL::asset($path . ($path ? ltrim($file, '\\/') : $file)));
            $url = rtrim(preg_replace('/\\Ahttps?:/', '', $psr_uri->__toString()), '\\/');
            $type = strtolower(Arr::last(explode('.', $psr_uri->getPath())));
            switch ($type) {
                case 'js':
                    if ($mode == 'preload') {
                        $parseStr .= sprintf('<link rel="preload" as="script" href="%s">' . $php_eol, $url . $version);
                    } elseif ($mode == 'defer') {
                        $parseStr .= sprintf('<script src="%s" defer="defer"></script>' . $php_eol, $url . $version);
                    } else {
                        $parseStr .= sprintf('<script src="%s"></script>' . $php_eol, $url . $version);
                    }
                    break;
                case 'css':
                    if ($mode == 'preload') {
                        $parseStr .= sprintf('<link rel="preload" as="style" href="%s">' . $php_eol, $url . $version);
                    } else {
                        $parseStr .= sprintf('<link rel="stylesheet" href="%s">' . $php_eol, $url . $version);
                    }
                    break;
                case 'ico':
                    $parseStr .= sprintf('<link rel="shortcut icon" href="%s">' . $php_eol, $url . $version);
                    break;
                case 'php':
                    $parseStr .= sprintf('<?php include "%s"; ?>', $file);
                    break;
                default:
                    $parseStr .= $url . $version;
            }
        }
        return $parseStr;
    }
    protected static function filterUrl($url)
    {
        $uri = new Uri($url);
        $pars = [];
        foreach (explode('/', $uri->getPath()) as $value) {
            if (!$value) {
                continue;
            }
            $pars[] = $value;
        }
        return $uri->withPath(empty($pars) ? '' : '/' . implode('/', $pars));
    }
}