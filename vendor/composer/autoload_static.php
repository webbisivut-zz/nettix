<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit9d1421e7aa06374bb94343b612ad7c11
{
    public static $prefixLengthsPsr4 = array (
        'V' => 
        array (
            'VWP\\' => 4,
        ),
        'C' => 
        array (
            'Composer\\Installers\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'VWP\\' => 
        array (
            0 => __DIR__ . '/../..' . '/vwp',
        ),
        'Composer\\Installers\\' => 
        array (
            0 => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit9d1421e7aa06374bb94343b612ad7c11::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit9d1421e7aa06374bb94343b612ad7c11::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
