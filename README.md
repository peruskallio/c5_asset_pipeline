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

The output of this call would look something like this:

```html
<link href="/application/files/cache/css/style-18282c71313a64df0dccceacdbb9e563.css" rel="stylesheet" type="text/css" media="all">
```

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

The output of this call would look something like this:

```html
<script type="text/javascript" src="/application/files/cache/js/script-dafa89aba8ca8b9a8ba6b8a89caae77e.js"></script>
```

### Options to be passed to the asset helper

In the above examples the `css` and `js` functions are called against an
instance of `Concrete\Package\AssetPipeline\Src\Service\Assets` which also
allows passing an options array to both of these functions as the second
parameter.

The options that you can currently pass to both of these methods are:

- `name` - The name part of the output cache file where the final asset is
  compiled to. By default for css-files: `style`. By default for js-files:
  `script`. Please note that this does not affect the fingerprint part of the
  final asset's filename. To learn more about the asset fingerprints, please
  read the [Asset fingerprints](#asset_fingerprints) section of this document.

An example of passing the options array to the `css` method follows below. The
options can be passed exactly the same way to the `js` method as well.

```php
<?php echo $assets->css(array(
    'main.less',
    'second.scss',
    'third.scss',
), array(
    'name' => 'site-styles',
)) ?>
```

The output of this call would look something like this:

```html
<link href="/application/files/cache/css/site-styles-18282c71313a64df0dccceacdbb9e563.css" rel="stylesheet" type="text/css" media="all">
```

<a name="asset_fingerprints"></a>
## Asset fingerprints digests

As you may have noticed above, the actual final cached asset file names consist
of two separate parts:

1. The name part which is the first readable part of the file's name
2. The fingerprint digest part which is suffixed into the file name with a dash
  in front of it.

In the following example, the first part (1.) is emphasized by bold characters
and the second part (2.) is emphasized with italics:

**style**-*18282c71313a64df0dccceacdbb9e563.css*

The reason for suffixing the file names with the digest fingerprint is that the
visitor's browser would always be forced to load the latest and greatest
version of the asset file. It is kind of the technical version number for the
cached asset file. This fingerprint is tied into the contents of the asset file
so it will always differ if the file contents have been changed. This assures
that the latest version of the asset file gets always sent to the site visitors
and they will not be using an older version of the file cached locally into
their hard drives.

Technically the digest fingerprint is generated by passing the file to PHP's
`hash_file` function. The used hashing alhorithm is `md5`.

## Interoperability with concrete5's own configuration options

There are some asset specific configuration options that you can currently set
through the concrete5 dashboard at `/dashboard/system/optimization/cache`. Some
of these also affect the functionality of the Asset Pipeline.

- **Theme CSS Cache** - If enabled, the generated CSS cache file is only
  refreshed if it does not already exist. This means that the same way as
  traditionally, with this setting enabled, you will need to clear the site's
  cache in case the CSS files are changed. Suggested to be only used on
  production sites.
- **Compress LESS Output** - If enabled, the preprocessed CSS will be
  compressed, no matter what the preprocessing language is. This option has the
  name **LESS** in it because concrete5 internally uses currently only the LESS
  preprocessing language. Disable this if you want to debug your CSS or find
  any specific sections of your preprocessed CSS more easily.
- **enable source maps in generated CSS files** - If enabled, the Less filter
  will generate the source maps the same was as the internal processing engine
  in concrete5. Please note that this applies **only** to the Less filter and
  is not currently implemented in the SCSS filter!

## Configuration options

Some options can be given through the site's 'application/config/app.php'
configuration file for the specific filters. These options should go into the
`asset_filter_options` configuration block within that file.

The following options may be currently defined in the application configuration
file:

- `less.legacy_url_support` (default `false`)- Defines whether the Less files
  should swap out the relative file paths in the CSS with the full paths that
  they represent in the file system. Default behavior traditionally in
  concrete5 but disabled by default in the Asset Pipeline. To learn why,
  please read the [Relative paths within the CSS](#css_relative_paths) section
  of this document.
- `js.compress` (default `true`) - Defines whether plain JS files should be
  minified or not. Enabled by default.
- `css.compress` (default `true`) - Defines whether plain CSS files should be
  minified or not. Enabled by default.

An example of the actual configuration file might look like this:

```php
<?php
// Within your 'application/config/app.php' configuration file
return array(
    // ... you may have other configuration variables here ...
    'asset_filter_options' => array(
        'less' => array(
            'legacy_url_support' => false, // does not change the default
        ),
        'js' => array(
            'compress' => true, // does not change the default
        ),
        'css' => array(
            'compress' => true, // does not change the default
        ),
    ),
    // ... and you may have other configuration variables also here ...
);
```

<a name="css_relative_paths"></a>
## Relative paths within the CSS

In the CSS you quite often need to reference static files from the file system,
such as images, font files, etc. Traditionally in concrete5, the Less files
within your theme's folder referenced the relative folders within the theme
automatically by replacing all the paths with the relative paths automatically.
However, we think this is a bad design decision as it limits the paths that can
be referenced from the themes (or makes it really awkward to reference them,
e.g. `../../../../../`). Also, this might work differently with different
filters and some filters might not even implement this feature.

Instead, the built-in filters within this package provide an alternative way of
referencing the static assets within the CSS files. This is through custom
functions that can be used within the CSS files. This is implemented for both
filters, the Less filter and the SCSS filter that come with this package.

To reference the static assets in different sections of the system, the
following functions are available within your CSS files (in both `.less` and
`.scss` files):

```scss
.current-theme {
    /* Reference an image within the active theme for the current page */
    background-image: theme-asset-url('images/your-background-image.jpg');
    /* Becomes (the actual path depends on the location of the theme): */
    /* background-image: url(http://site.com/application/themes/active_theme/images/your-background-image.jpg); */
}
.other-theme {
    /* Reference an image within another theme */
    background-image: theme-asset-url('images/your-background-image.jpg', 'theme_handle');
    /* Becomes (the actual path depends on the location of the theme): */
    /* background-image: url(http://site.com/application/themes/theme_handle/images/your-background-image.jpg); */
}
.application {
    /* Reference an image within the application folder */
   background-image: asset-url('images/your-background-image.jpg');
    /* Becomes: */
    /* background-image: url(http://site.com/application/images/your-background-image.jpg); */
}
.core {
    /* Reference an image within the core (concrete) folder */
   background-image: core-asset-url('images/your-background-image.jpg');
    /* Becomes: */
    /* background-image: url(http://site.com/concrete/images/your-background-image.jpg); */
}
.package {
    /* Reference an image within a package folder */
   background-image: package-asset-url('images/your-background-image.jpg', 'package_handle');
    /* Becomes: */
    /* background-image: url(http://site.com/packages/package_handle/images/your-background-image.jpg); */
}
```

In case you somewhere need only the paths of the assets, you can swap the
`-url` suffix with `-path` in the function names to skip printing out the
`url()` part of the resulting string.

### Enabling the relative path replacing in CSS

If you are using the assets pipeline with any existing concrete5 themes, they
might be also relying on the concrete5's internal functionality that it
automatically swaps out all the relative URLs within the Less files. If you
want to enable this feature with the assets pipeline, you can set the following
configuration variable in your `application/config/app.php`:

```php
<?php
// Within your 'application/config/app.php' configuration file
return array(
    // ... you may have other configuration variables here ...
    'asset_filter_options' => array(
        'less' => array(
            'legacy_url_support' => true
        ),
    ),
    // ... and you may have other configuration variables also here ...
);
```

## Filters

Currently this package provides the followign filters:

* SCSS preprocessing for CSS through `leafo/scssphp`. Applies to all the files
  with the `.scss` extension.
* Less preprocessing for CSS through `oyejorge/less.php`. Applies to all the
  files with the `.less` extension.
* Plain CSS file minification through `natxet/CssMin`. Applies for all the
  files with the `.css` extension.
* JavaScript minification for JS through `tedivm/jshrink`. Applies for all the
  files with the `.js` extension.

New filters (e.g. for CoffeeScript) can be added through configuration. For
adding new filters, please read the [Adding new filters](#new_filters) section
of this document.

<a name="new_filters"></a>
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
    'asset_filters' => array(
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
        'css' => array(
            'applyTo' => '\.css$',
        ),
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

## TODO

- Integrate the Asset Pipeline functionality with the internal assets system
  within concrete, e.g. for JavaScript preprocessing purposes.
- Examine if it is possible to implement the source maps also for the SCSS
  filter

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
