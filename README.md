<img src="assets/immonex-os-logo-small.png" width="230" height="48" align="right" alt="immonex Open Source Software" >

# immonex WP Free Plugin Core

This lightweight **PHP library** provides shared basic functionality for free **immonex WordPress plugins**, i.a.

- consistent initialization
- autoloading of CSS and JS files
- option handling / shared settings pages
- geocoding
- simple templating
- special string checking and manipulation
- color calculations

**immonex**® is an umbrella brand for various **real estate related software** solutions and services with a focus on german-speaking countries/users.

## Installation

### Via Composer

```bash
$ composer require immonex/wp-free-plugin-core
```

## Basic Usage

In most cases, a boilerplate template will be used to kickstart plugin development based on this library. Anyway, here comes a basic working example...

The [example plugin folder](examples/myimmonex-example-plugin):
```
myimmonex-example-plugin
├── includes
│   └── class-example-plugin.php
├── languages
├── [vendor]
├── autoload.php
├── composer.json
└── myimmonex-example-plugin.php
```

With the [Composer-based installation](#via-composer), the plugin core library gets added to the **require section** in `composer.json`:

```json
    "require": {
        "immonex/wp-free-plugin-core": "^1.2.1"
    },
```

`myimmonex-example-plugin.php` is the **main plugin file** in which the central autoloader file is being included and the main plugin object gets instantiated:

```php
require_once __DIR__ . '/autoload.php';

$myimmonex_example_plugin = new My_Plugin( basename( __FILE__, '.php' ) );
$myimmonex_example_plugin->init();
```

The **main plugin class** is located in the file `includes/class-example-plugin.php`. It is derived from the latest **core Base class**:

```php
class Example_Plugin extends \immonex\WordPressFreePluginCore\V1_2_1\Base {

	const
		PLUGIN_NAME = 'My immonex Plugin',
		PLUGIN_PREFIX = 'myplugin_',
		PUBLIC_PREFIX = 'myplugin-',
		PLUGIN_VERSION = '1.1.0',
		OPTIONS_LINK_MENU_LOCATION = 'settings';

	...

} // class Example_Plugin
```

That's it!

## Folder Based Versioning

The `src` folder usually contains two "version branch" folders for the latest development (`DEV_[0-9]+`) and production release (`VX_X_X`) versions. It **may** optionally contain multiple production release folders.

```
src
├── DEV <────┐ Development Branch (DB), NS: immonex\WordPressFreePluginCore\DEV
├── V1_0_0   │ optional PB
├── V1_1_0   │ optional PB
└── V1_1_7 ──┘ Latest Production Branch (PB), NS: immonex\WordPressFreePluginCore\V1_1_7
```

The folder names are also part of the related PHP namespaces in the included files, e.g. `immonex\WordPressFreePluginCore\V1_0_1`.

An arbitrary number and an underscore may be added to the folder name and class namespaces of the `DEV` folder to ensure uniqueness during development, e.g. `immonex\WordPressFreePluginCore\DEV12_3`.

**Public (production) releases** of plugins that use this library always refer to the latest **production branch**.

### Background

Multiple immonex plugins that possibly require **different versions** of the core library can be active in the **same WordPress installation**. As these plugins are - more or less - independent components, the Composer dependency management does not work here. Ergo: Each plugin must ensure itself that the used core library files exactly match the required version.

The **autoloading chain** supplied by this lib avoids incompatibilities that can occur, for example, if an incompatible version has already been loaded by another active immonex plugin.

## Development

### Requirements

- [Git](https://git-scm.com/book/en/v2/Getting-Started-Installing-Git)
- [npm (Node.js)](https://www.npmjs.com/get-npm)
- [Composer](https://getcomposer.org/)
- [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer)
- [WordPress Coding Standards for PHP_CodeSniffer](https://github.com/WordPress/WordPress-Coding-Standards)

### Setup

Setting up a simple development environment starts by cloning this repository and installing dependencies:

```bash
$ cd ~/projects
$ git clone git@github.com:immonex/wp-free-plugin-core.git immonex-wp-free-plugin-core
$ cd immonex-wp-free-plugin-core
$ npm install
$ composer install
```

> :warning: PHP_CodeSniffer and the related WP sniffs are **not** part of the default dependencies and should be [installed globally](https://github.com/WordPress/WordPress-Coding-Standards#composer).

### Git

- Branching strategy: [GitHub flow](https://guides.github.com/introduction/flow/)
- Commit messages: [Conventional Commits](https://www.conventionalcommits.org/)

### PHP compatibility

5.6+ (switch to 7.6+ envisaged for future releases)

### Coding Standard

The source code formatting corresponds to the [WordPress PHP Coding Standards](https://make.wordpress.org/core/handbook/best-practices/coding-standards/php/).

The source files can be checked with PHP_CodeSniffer (if, as recommended, installed globally as described [here](https://github.com/WordPress/WordPress-Coding-Standards#composer)):

```bash
$ phpcs
```

To fix violations automatically as far as possible:

```bash
$ phpcbf
```

### API Documentation

The API documentation based on the sources can be generated with the following command and is available in the `apidoc` folder afterwards:

```bash
$ npm run apidoc
```

To view it using a local webserver:

```bash
$ npm run apidoc:view
```

If these docs are not needed anymore, the respective folders can be deleted with this command:

```bash
$ npm run apidoc:delete
```

(The folder `apidoc` is meant to be used locally, it should **not** a part of any repository.)

### Testing

Locally running unit tests ([PHPUnit](https://phpunit.de/)) for plugins usually requires a temporary WordPress installation (see [infos on make.wordpress.org](https://make.wordpress.org/cli/handbook/plugin-unit-tests/#running-tests-locally)). To use the test install script included in this repository, the file `.env` containing credentials of a local test database has to be created first (see [.env.example](.env.example)).

After that, the temporary testing environment can be installed:

```bash
$ npm run test:install
```

Running tests in the `tests` folder:

```bash
$ npm run test
```

### Translations

The core classes of this library do **and should** only include a few strings that have to be translated. Translations (PO/MO files) that are distributed as part of this library are provided in the `languages` subfolders of the version branch directories. These folders also contain a current POT file as base for custom translations that can be updated with the following command.

```bash
$ npm run pot
```

Copies of the the **german default translation files** (MO/*de_DE*) for the WP locales *de_DE_formal*, *de_AT*, *de_CH* and *ch_CH_informal* in all dev and production folders can be created with:

```bash
$ npm run copy-mo
```

(Existing translation files will **not** be overwritten.)

## License

[GPLv2 or later](LICENSE)

Copyright (C) 2014, 2020 [inveris OHG](https://inveris.de/) / [immonex](https://immonex.dev/)

This library is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
