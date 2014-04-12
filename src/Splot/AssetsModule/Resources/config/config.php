<?php

return array(
    'application_dir' => 'application/',
    'modules_dir' => 'modules/',
    'overwritten_dir' => 'modulesapp/',
    'minifier' => array(
        'css_enable' => false,
        'css_dir' => '/_min/css/',
        'css_minifier' => 'assets.minifiers.cssmin',
        'css_minifier_worker' => 'assets.minifiers.cssmin',
        'js_enable' => false,
        'js_dir' => '/_min/js/',
        'js_minifier' => 'assets.minifiers.null',
        'js_minifier_worker' => 'assets.minifiers.null',

        // specific minifiers configuration
        'uglifyjs2' => array(
            'bin' => '/usr/local/bin/uglifyjs',
            'node_bin' => '/usr/local/bin/node'
        )
    )
);