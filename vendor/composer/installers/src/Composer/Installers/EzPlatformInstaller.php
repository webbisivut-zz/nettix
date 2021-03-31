<?php
namespace Composer\Installers;

class EzPlatformInstaller extends BaseInstaller
{
    protected $locations = array(
        'meta-assets' => 'web/dist/ezplatform/',
        'assets' => 'web/dist/ezplatform/{$name}/',
    );
}
