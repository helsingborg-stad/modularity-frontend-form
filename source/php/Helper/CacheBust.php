<?php

namespace ModularityFrontendForm\Helper;

class CacheBust
{
    /**
     * Returns the revved/cache-busted file name of an asset.
     * @param string $name Asset name (array key) from rev-mainfest.json
     * @return string filename of the asset (including directory above)
     */
    public function name($name)
    {
        $jsonPath = MODULARITYFRONTENDFORM_PATH . apply_filters(
            'ModularityFrontendForm/Helper/CacheBust/RevManifestPath',
            'dist/manifest.json'
        );

        $revManifest = [];
        if (file_exists($jsonPath)) {
            $revManifest = json_decode(file_get_contents($jsonPath), true);
        } elseif ($this->isDebug()) {
            echo '<div style="color:red">Error: Assets not built. Go to ' . MODULARITYFRONTENDFORM_PATH . ' and run `php build.php`. See ' . MODULARITYFRONTENDFORM_PATH . 'README.md for more info.</div>';
        }

        if (!isset($revManifest[$name])) {
            return;
        }

        return $revManifest[$name];
    }

    /**
     * Check if debug mode, Remove constant dependency in tests.
     */
    public function isDebug()
    {
        return defined('WP_DEBUG') && WP_DEBUG;
    }
}
