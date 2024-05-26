<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit889ee12c9dae18c00288e1515de54769
{
    public static $files = array (
        'fc73bab8d04e21bcdda37ca319c63800' => __DIR__ . '/..' . '/mikecao/flight/flight/autoload.php',
    );

    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Solakmirnes\\SssdAuth\\' => 21,
        ),
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Solakmirnes\\SssdAuth\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit889ee12c9dae18c00288e1515de54769::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit889ee12c9dae18c00288e1515de54769::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit889ee12c9dae18c00288e1515de54769::$classMap;

        }, null, ClassLoader::class);
    }
}
