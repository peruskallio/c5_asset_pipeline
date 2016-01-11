# Asset Pipeline for concrete5

The purpose of this concrete5 add-on is to provide a proof of concept of an
Asset Pipeline implementation for concrete5. This would help a lot of
developers to properly handle their site's static assets.

The purpose of an Asset Pipeline is to have a general way for developers to
include different static assets (CSS and JS) to their views and handle their
preprocessing correctly. While the latest and greatest concrete5 already
handles asset preprocessing, it is currently not implemented in a very general
way. The CSS preprocessing language is tied into LESS and e.g. JavaScript
cannot be written with the CoffeeScript syntax directly throug the tools
provided by concrete5.

This package attempts to solve these problems by providing a general pipeline
through which different types of assets can be preprocessed, combined and
served to the client in an easy way. The asset preprocessing is handled by
Assetic which allows developers to easily add their preferred preprocessors for
the assets now and also in the future if and when new such preprocessing
languages arise.

This is currently **work in progress**! More guidance and instructions are
added once this becomes a fully working version.

## How to install?

1. Make sure you have Composer installed. Really, do not proceed if you do not
   know what Composer is (check the link).
2. Download or clone this repository and extract it into the /packages folder
   of your concrete5 installation.
3. Rename the folder to `asset_pipeline`.
4. Open up your console and navigate to the package's directory you just
   renamed. Run `composer install` to install all the composer packages.
5. Open up your web browser and navigate to the Dashboard of the website.
   Install the add-on through the

## How to use?

In your theme's view files you have a new variable available named `$assets`.
This is an instance of `Concrete\Package\AssetPipeline\Src\Service\Assets` and
you can call any methods available in that class by calling them against this
object in your theme's view files.

### Example of outputting a URL for preprocessed CSS files

```php
<?php echo $assets->css(array(
    'main.less',
    'second.scss',
    'third.scss',
)) ?>
```

This will reference three CSS files (specified above) within your theme's `css`
folder, preprocess them and combine them into a single CSS file that is
downloaded by the client. You may have also noticed that in the file list
above there are both, Less and SCSS, files. Other file types also may be added
by providing the preprocessing filters for them. This package only comes with
Less and SCSS preprocessing filters.

### Example of outputting a URL for preprocessed JS files

```php
<?php echo $assets->js(array(
    'site.js',
    'second.js',
    'third.js',
)) ?>
```

This will reference three JS files (specified above) within your theme's `js`
folder, preprocess them and combine them into a single JS file that is
downloaded by the client. In this example, we only define plain JS files but
other formats, such as CoffeeScript could be also used for JS in case the
proper filters are provided for the `.coffee` file extension. This package does
not come with that filter built-in since there is currently no plain PHP
implementation for that language and enabling that filter would require
installing external components on the computer which might require some level
of technical expertise.

## Filters

Currently this package provides the followign filters:

* SCSS preprocessing for CSS through `leafo/scssphp`. Applies to all the files
  with the `.scss` extension.
* Less preprocessing for CSS through `oyejorge/less.php`. Applies to all the
  files with the `.less` extension.
* TODO: Plain CSS file minification...
* JavaScript combination and minification for JS through `tedivm/jshrink`.
  Applies for all the files with the `.js` extension.

New filters (e.g. for CoffeeScript) can be added through configuration.

### Adding new filters

New filters may be easily added for this package.

Currently you can take a look at the
`Concrete\Package\AssetPipeline\Src\FilterProvider` class which registers the
currently available built-in filters.

In addition, you will also need to edit the `assets.filters` configuration
variable in order to tell the system that the new filter exists for the
specified file extension. For an example on how to modify that configuration
value, please take a look at the package's `controller.php` and follow what
has been done in the default setup.

More guidance and specific documentation on the setup will be added here later
on when this package matures.

## Few notes

This package needs to override a couple of the core components to make this
abstraction layer fully compatible with all the concrete5's internal
components. Mostly, this means making the style/theme customization compatible
with this layer and adding the possibility to have the style customization
preset definitions in any possible CSS preprocessing syntax.

These overrides are automatically applied to the concrete5 installation upon
the installation of this package and therefore do not require any additional
installation steps from the user. They are provided by registering a special
autoloader that overrides the specific class locations for the core to load
them from. Technically they are also separated into their own folder
(`src/Core`) in order to make it easier to understand for developers.

## License

Licensed under the MIT license. See LICENSE for more information.

Copyright (c) 2015 Mainio Tech Ltd.
