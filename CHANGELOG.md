# v2.0.0
##  11/20/2026

1. [](#new)
    * Grav 2 support. The plugin now requires Grav 2.0+ and PHP 8.3+
    * Added a Grav 2 smoke test and a GitHub Actions workflow running the tests
2. [](#improved)
    * Updated Symfony Finder & Filesystem to 7.x, `vendor` no longer contains development dependencies
    * Twig filters are registered with `Twig\TwigFilter`
    * Console commands look up a single image directly instead of scanning the whole Grav root
    * Updated README
3. [](#bugfix)
    * Fixed fatal error for images with mirrored EXIF orientation
    * Fixed `clear --all` error when no image has been converted yet
    * Fixed exit codes of console commands (`0` on success, `1` on failure)
    * Fixed `--path` option of console commands on Windows

# v1.5.0
##  02/21/2025

1. [](#new)
    * Added option to change where webp files are saved [#17](https://github.com/greenrivers/grav-plugin-webp/issues/17)

# v1.4.6
##  02/03/2024

1. [](#bugfix)
    * Fixed custom admin route [#14](https://github.com/greenrivers/grav-plugin-webp/issues/14)

# v1.4.5
##  11/09/2023

1. [](#bugfix)
    * Fixed relative path [#12](https://github.com/greenrivers/grav-plugin-webp/issues/12)

# v1.4.4
##  08/26/2023

1. [](#improved)
    * Updated README

# v1.4.3
##  02/23/2023

1. [](#bugfix)
    * Fixed undefined Orientation index

# v1.4.2
##  12/26/2022

1. [](#bugfix)
    * Fixed incorrect exif orientation [#7](https://github.com/greenrivers/grav-plugin-webp/issues/7)

# v1.4.1
##  10/11/2022

1. [](#bugfix)
    * Fixed progressbar
2. [](#improved)
    * Updated README

# v1.4.0
##  08/16/2022

1. [](#new)
    * Added unit tests
2. [](#improved)
    * Updated README

# v1.3.0
##  04/17/2022

1. [](#new)
    * Added console command for conversion
    * Added console command for clear webp images
2. [](#improved)
    * Updated README

# v1.2.1
##  03/19/2022

1. [](#bugfix)
    * Fixed admin save

# v1.2.0
##  03/19/2022

1. [](#new)
    * Added image quality slider
2. [](#bugfix)
    * Fixed progress for Chrome browser
3. [](#improved)
    * Updated DocBlocks
    * Updated README

# v1.1.0
##  10/08/2021

1. [](#new)
    * Added Clear All button
    * Added label fields translations
2. [](#improved)
    * Updated DocBlocks
    * Updated README

# v1.0.4
##  04/13/2021

1. [](#bugfix)
    * Fixed plugin crashes after enabled [#3](https://github.com/greenrivers/grav-plugin-webp/issues/3)
2. [](#improved)
    * Updated README

# v1.0.3
##  02/28/2021

1. [](#bugfix)
    * Fixed changelog date format

# v1.0.2
##  02/28/2021

1. [](#bugfix)
    * Fixed blueprints release

# v1.0.1
##  02/20/2021

1. [](#bugfix)
    * Fixed plugin crashes after activation [#1](https://github.com/greenrivers/grav-plugin-webp/issues/1)

# v1.0.0
##  01/01/2021

1. [](#new)
    * ChangeLog started...
