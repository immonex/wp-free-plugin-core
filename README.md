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

**immonex** is an umbrella brand for various **real estate related software** solutions and services with a focus on german-speaking countries/users.

## Installation

### Via Composer

```bash
$ composer require immonex/wp-free-plugin-core
```

## Basic Usage

In most cases, a boilerplate template will be used to kickstart plugin development based on this library. Anyway, here comes a basic working example...

The [example plugin folder](examples/my-immonex-plugin):
```
my-immonex-plugin
├── includes
│   └── class-my-plugin.php
├── languages
├── [vendor]
├── autoload.php
├── composer.json
└── my-immonex-plugin.php
```

With the [Composer-based installation](#via-composer), the plugin core library gets added to the **require section** in `composer.json`:

```json
    "require": {
        "immonex/wp-free-plugin-core": "^1.0.0"
    },
```

`my-immonex-plugin.php` is the **main plugin file** in which the central autoloader file is being included and the main plugin object gets instantiated:

```php
require_once __DIR__ . '/autoload.php';

$my_immonex_plugin = new My_Plugin( basename( __FILE__, '.php' ) );
$my_immonex_plugin->init();
```

The **main plugin class** is located in the file `includes/class-my-plugin.php`. It is derived from the latest **core Base class**:

```php
class My_Plugin extends \immonex\WordPressFreePluginCore\V1_0_0\Base {

	const
		PLUGIN_NAME = 'My immonex Plugin',
		PLUGIN_PREFIX = 'myplugin_',
		PUBLIC_PREFIX = 'myplugin-',
		PLUGIN_VERSION = '1.0.0',
		OPTIONS_LINK_MENU_LOCATION = 'settings';

	...

} // class My_Plugin
```

That's it!

## Folder Based Versioning

The `src` folder **may** contain multiple version branches:

```
src
├── V0_9 <───┐ Development Branch (DB), NS: immonex\WordPressFreePluginCore\V0_9
├── V0_9_0   │ PB
├── V0_9_2   │ PB
├── V0_9_4   │ PB
├── V0_9_7 ──┘ Production Branch (PB), NS: immonex\WordPressFreePluginCore\V0_9_7
├── V1_0 <───┐ DB
├── V1_0_0   │ PB
├── V1_0_1   │ PB
└── V1_0_5 ──┘ PB
```

The folder names are also part of the related PHP namespaces in the included files, e.g. `immonex\WordPressFreePluginCore\V1_0_1`.

Folders without patch level in their name and namespaces (`VX_Y`) are **development branches** that always contain classes of the **latest patch level** of the respective major/minor version.

**Public (production) releases** of plugins that use this library always refer to the latest **production branch** (including patch level).

### Background

Multiple immonex plugins that possibly require **different versions** of the core library can be active in the **same WordPress installation**. As these plugins are - more or less - independent components, the Composer dependency management does not work here. Ergo: Each plugin must ensure itself that the used core library files exactly match the required version. This avoids incompatibilities that can occur, for example, if an incompatible version has already been loaded by another active immonex plugin.

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

### Coding Standard

The source code formatting corresponds to the [WordPress PHP Coding Standards](https://make.wordpress.org/core/handbook/best-practices/coding-standards/php/).

It can be checked with PHP_CodeSniffer (if installed globally as described [here](https://github.com/WordPress/WordPress-Coding-Standards#composer) - recommended):

```bash
$ phpcs
```

To fix violations automatically as far as possible:

```bash
$ phpcbf
```

### API Documentation

The API documentation based on the sources can be generated with the following command and is available in the [apidoc folder](apidoc) afterwards:

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

The core classes of this library do **and should** only include a few strings that have to be translated. Translations (PO/MO files) can be provided in the `languages` folder (or via another WordPress conform way). This folder also contains a current POT file as base for own translations that can be updated with the following command.

```bash
$ npm run pot
```

## License

[GPLv2 or later](LICENSE)

Copyright (C) 2014, 2020 [inveris OHG](https://inveris.de/) / [immonex](https://immonex.dev/)

This library is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
