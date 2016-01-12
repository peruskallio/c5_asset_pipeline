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

Within your theme files you can use the following snippet:

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

Within your theme files you can use the following snippet:

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

For adding a new filter, you can take a look at the
`Concrete\Package\AssetPipeline\Src\FilterProvider` class which registers and
builds the currently available built-in filters. All filters need to implement
the `Assetic\Filter\FilterInterface` or one of its extensions in order to work
properly with [Assetic](https://github.com/kriswallsmith/assetic) which handles
the file output filtering.

For any filter related issues or guidance, you can refer to the
[Assetic's documentation](https://github.com/kriswallsmith/assetic).

The closure callbacks for the IoC container with the filter's associated key
(such as `assets/filter/less`) will get the `$assets` variable as the second
argument which is an instance of
`Concrete\Package\AssetPipeline\Src\Service\Assets`. This object can provide
more information about the asset compilation which may be required when
initiating the filter. For example, you can get the theme customization's
active variables by calling `$assets->getStyleSheetVariables()`. For an
example, please see the existing closure callback implementations within the
`Concrete\Package\AssetPipeline\Src\FilterProvider` class.

In addition, you will also need to tell the system that the new filters exist
in order for them to be available for actual use. You can do this either by
calling setting the filters directly to the
`Concrete\Package\AssetPipeline\Src\Asset\Manager` class or alternatively by
editing the the `app.asset_filters` configuration variable.

For an example on how to call the `Asset\Manager` directly, take a look at the
`FilterProvider`'s method named `setFilters` and how it defines the available
filters for the manager.

For directly editing the `app.asset_filters` configuration variable, you can
use your `application/config/app.php` configuration file. The following example
configuration can be used in that file for instance. The provided example sets
the exact same filters as the default configurations provide:

```php
<?php
// Within your 'application/config/app.php' configuration file
return array(
    // ... you may have other configuration variables here ...
    'asset' => array(
        'filters' => array(
            'less' => array(
                'applyTo' => '\.less$',
                'customizableStyles' => true,
            ),
            'scss' => array(
                'applyTo' => '\.scss$',
                'customizableStyles' => true,
            ),
            'js' => array(
                'applyTo' => '\.js$',
            ),
        )
    ),
    // ... and you may have other configuration variables also here ...
);
```

The configuration option values you can give to each filter in the filters
configuration array are the following:

- `applyTo` - A string that defines a regular expression which is used to match
  against the file paths of the files to be filtered. If the regular expression
  matches with the file path, the filter with the defined key will be searched
  from the IoC container (`assets/filter/**key**`) and applied to the matching
  file.
- `customizableStyles` - A boolean that defines whether the filter can provide
  customizable styles or not to be used with concrete5 themes. In case this
  configuration variable is set to true, also a value extractor needs to be
  defined for the IoC container with the filter's key
  (`assets/value/extractor/**key**`) in order for the values to be extractable
  from the defined file type inside the theme's `css/presets` folder. For an
  example on implementing a value extractor, please take a look at the existing
  implementations within the `src/StyleCustomizer/Style/Value/Extractor` folder
  within this package.

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
