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

This is currently `work in progress`! More guidance and instructions are added
once this becomes a fully working version.

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

TODO

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

TODO

