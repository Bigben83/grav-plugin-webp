# Greenrivers Webp Plugin

The **Webp** Plugin is an extension for [Grav CMS](http://github.com/getgrav/grav).

It allows You to conversion images to webp extension.

## Installation

Installing the Webp plugin can be done in one of three ways:
- GPM (Grav Package Manager)
- manual method
- admin method

### GPM Installation (Preferred)

To install the plugin via the [GPM](http://learn.getgrav.org/advanced/grav-gpm), through your system's terminal (also called the command line),
navigate to the root of your Grav-installation, and enter:

    bin/gpm install webp

This will install the Webp plugin into your `/user/plugins`-directory within Grav. Its files can be found under `/your/site/grav/user/plugins/webp`.

### Manual Installation

To install the plugin manually, download the zip-version of this repository and unzip it under `/your/site/grav/user/plugins`.
Then rename the folder to `webp`. You can find these files on [GitHub](https://github.com/greenrivers/grav-plugin-webp) or via [GetGrav.org](http://getgrav.org/downloads/plugins#extras).

You should now have all the plugin files under

    /your/site/grav/user/plugins/webp

> NOTE: This plugin is a modular component for Grav which may require other plugins to operate,
> please see its [blueprints.yaml-file on GitHub](https://github.com/greenrivers/grav-plugin-webp/blob/master/blueprints.yaml).

### Admin Plugin

If you use the Admin Plugin, you can install the plugin directly by browsing the `Plugins`-menu and clicking on the `Add` button.

## Requirements

- Grav 2.0 or newer
- PHP 8.3 or newer

Make sure that You have installed and enabled webp support:

- install system packages: libwebp-dev, webp
- enable GD and configure PHP to enable support for webp format
- install & enable PHP Exif extension

## Configuration

Before configuring this plugin, you should copy the `user/plugins/webp/webp.yaml` to `user/config/plugins/webp.yaml` and only edit that copy.

Here is the default configuration and an explanation of available options:

```yaml
enabled: true
original_path: 0
quality: 100
```

Note that if you use the Admin Plugin, a file with your configuration named webp.yaml will be saved in the `user/config/plugins/`-folder once the configuration is saved in the Admin.

## Usage

#### Plugin config

> NOTE: The **Quality**, **Convert** and **Clear all** controls described below are custom fields for the classic Admin plugin.
> Admin2, the admin panel of Grav 2, doesn't load classic admin field templates, so with Admin2 set `quality` in
> `user/config/plugins/webp.yaml` and use the [console commands](#console-commands) to convert & clear images.

1. Original path
    - Enable to save the webp image in the same directory as the original image
    - Disable to save the webp image in the user/webp directory

![Original path](assets/images/original_path.webp)

2. Quality
    - Set up value on slider range
    - Quality percent value is displayed below the slider

![Quality](assets/images/quality.webp)

3. Conversion
   - Click on **Convert** button from plugin settings in admin menu.
   - Info about number of the converted images appears next to the **Convert** button.

![Convert](assets/images/convert.webp)

4. Clear all
    - Click on **Clear all** button from plugin settings in admin menu.
    - Info about number of the removed webp images appears next to the **Clear all** button.

![Clear all](assets/images/clear_all.webp)

#### Console commands

1. Convert to webp format

- image by given path

```shell
php bin/plugin webp convert --path=<path_to_image> [--original_path=<1,0>] [--quality=<quality>] [--overwrite]
```

- all images

```shell
php bin/plugin webp convert --all [--original_path=<1,0>] [--quality=<quality>]
```

2. Clear

- webp image by given path

```shell
php bin/plugin webp clear --path=<path_to_image>
```

- all webp images

```shell
php bin/plugin webp clear --all
```

#### Notes

Plugin supports following extensions:

- jpg
- jpeg
- png

Better quality causes bigger image size.

<ins>After conversion You must clear cache.</ins>

To display converted image use **webp** filter in twig templates.

### Examples:

Raw image url.

```html
<img alt="webp image" src="{{ '/user/images/my-image.jpg'|webp }}">
```

Source from variable.

```html
<img alt="webp image" src="{{ url(logo)|webp }}">
```

The filters work out of the box in theme and plugin templates. Twig in page content is sandboxed in Grav 2,
so to use them there (pages with `process: twig: true`) allow them in `user/config/security.yaml`:

```yaml
twig_sandbox:
  allowed_filters:
    - webp
    - extension
```

### Issues:

Sometimes after plugin activation You can see text field instead of button & progressbar.<br/>
To resolve it, save config again with **Enabled** status.

![Plugin enabled bug](assets/images/plugin-enabled-bug.webp)

## Testing

#### Unit tests

The committed `vendor` directory only contains the packages the plugin needs at runtime.
Install the development tools first, then run the unit tests from root plugin path (**user/plugins/webp**):

```
composer install
php vendor/bin/codecept run unit
```

Don't commit the `vendor` directory in this state. To restore the runtime-only version (also after changing dependencies), run:

```
composer install --no-dev --optimize-autoloader
```

#### Grav smoke test

The smoke test boots a real Grav 2 installation and checks that the plugin loads,
the twig filters are registered and the console commands convert & clear an image.
Run it from the Grav root path, with the plugin installed in **user/plugins/webp** and enabled:

```
php user/plugins/webp/tests/smoke/smoke.php
```

## Credits

https://www.php.net/manual/en/image.installation.php

https://developers.google.com/speed/webp

## ToDo

- [x] Create **Clear all** option
- [x] Create image quality option
- [x] Add console commands
- [x] Add unit tests
