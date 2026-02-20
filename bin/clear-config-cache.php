<?php

declare(strict_types=1);

chdir(__DIR__ . '/../');

require 'vendor/autoload.php';

$config = include 'config/config.php';

if (! isset($config['config_cache_path'])) {
    echo "No configuration cache path found" . PHP_EOL;
    exit(0);
}

if (is_array($config['config_cache_path'])) {
    foreach ($config['config_cache_path'] as $cachePath) {
        if (file_exists($cachePath)) {
            if (false === unlink($cachePath)) {
                printf(
                    "Error removing config cache file '%s'%s",
                    $cachePath,
                    PHP_EOL
                );
                exit(1);
            }

            printf(
                "Removed configured config cache file '%s'%s",
                $cachePath,
                PHP_EOL
            );
        }
    }
}else {
    if (! file_exists($config['config_cache_path'])) {
        printf(
            "Configured config cache file '%s' not found%s",
            $config['config_cache_path'],
            PHP_EOL
        );
        exit(0);
    }

    if (false === unlink($config['config_cache_path'])) {
        printf(
            "Error removing config cache file '%s'%s",
            $config['config_cache_path'],
            PHP_EOL
        );
        exit(1);
    }

    printf(
        "Removed configured config cache file '%s'%s",
        $config['config_cache_path'],
        PHP_EOL
    );
    exit(0);
}


