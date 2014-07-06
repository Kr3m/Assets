# Bonfire Assets

[![Build Status](https://travis-ci.org/ci-bonfire/Assets.svg?branch=develop)](https://travis-ci.org/ci-bonfire/Assets)

WARNING: Currently under heavy initial development. More docs and stuff coming soon.

Bonfire's Assets package is a framework-agnostic Asset Pipeline implemetation for PHP 5.4+.  While intended for use within [Bonfire Next](https://github.com/ci-bonfire/Bonfire-Next), it is usable as a standalone package within any PHP application.

* [Asset Pipelines](#pipelines)
* [Installation](#install)
* [Setup](#setup)
* [Serving Assets](#serving)
* [Directives](#directives)
* [Custom Tags](#tags)

<a name="pipeline"></a>
## Asset Pipelines
Inspired by Rail's Asset Pipeline, the project provides runtime tools to help speed up the performance of your application on the client side by processing your application's assets. These can be any sort of asset used within your application, but typically refers to your stylesheets, javascripts and images. 

In the simplest terms, this allows you to specify any actions to be performed on your assets prior to them being served to the public.  This can be combining a number of files into a single file, minifying the resulting file, or even resizing images on the fly.

<a name="install"></a>
## Installation
The package is installed through [Composer](https://getcomposer.org/). Edit your project's `composer.json` file to require the following: 

	"require": {
		"ci-bonfire/assets": "dev-develop",
	}

Next, update Composer from the command line: 

	composer update

## A Brief Overview
When your browser requests an asset, say a stylesheet, it will simply send a request to the web server for that file. If it exists, that file will be served up. This is the fastest way it can retrieve the file, since our application doesn't have to do any processing. However, if the file doesn't exist, then you can setup your application to catch that request and process the file. 

The processing of a CSS file might look like this: 

* **Read the file for any `directives`** These simply provide a few actions to build a list of files that are combined into this single file. By making a single request to the server, this can greatly boost the client-side performance. These files can be scattered throughout your project, in modules, themes, etc and all kept safely out of the web root if desired.
* **Combine the files** The contents of each of the files are combined, one after the other in the order they are read. 
* **Process the Filters** You can specify a list of filters to be applied to every asset type, based on file extensions. Typical processes for a CSS file would be to do some URL rewriting so that all of the assets can be found, images displayed correctly, etc, then minify the resulting content for the smallest file possible, reducing the amount of content that needs to be sent to the client.
* **Cache the results** When in production, the results of the entire process is written to your main assets folder. At this point, the files are static files that will be pulled up directly by the server, not needing our processing anymore.

<a name="setup"></a>
## Setup
This package needs a few pieces of information in place before it can be used. 

### Catching the Asset request
The first thing you must do is to setup your application to catch the request for these assets. This is often done through your framework's `routing` system. You would then need a controller to create an instance of Bonfire's Assets, setup the configuration, and process the request. 

Here are a couple of examples in different popular PHP frameworks. 

**CodeIgniter 3**

	$route['assets/(:any)'] = 'pipeline/$1';
	
**Laravel 4**

	Route::get('/assets/{name}', 'Pipeline@index')->where('name', '[a-zA-Z0-9._-]+');

The `pipeline.php` file included within the library serves as an example of how you would setup your own controller to work with the library.

### Configuration Settings
A configuration array containing the following information must be passed into the contstructor as the first parameter. 

* `environment` - Typically either `development` or `production`. Will effect whether the files are converted to static files or not.
* `live_assets_folder` - the server directory where static assets are saved in. 
* `asset_folders` - An array of paths on the server to look for assets in. 
* `asset_url` - the URI in your domain where assets are expected to be located at. Defaults to `/assets`.
* `asset_type_folders` - an array specifies the names of subfolders within the main asset folders to look for the various asset types. See the provided `pipeline.php` file for example.
* `mime_types` - an array of file extensions that can map to the asset types. This allows you to use custom file extensions (like for SASS or LESS) directly, and still have the system know that they are stylesheet files. See the provided `pipeline.php` file for example.

### Filters
Filters allow you to specify exactly what actions are taken on each file. This is specified *per file extension*. That way you can push a filter on SASS files to compile the SASS, and not have to worry about it accidentally being applied to LESS files, etc. If a file extension is not included in this list, it is not processed and will be passed along as a standard downloadable file.

The filters array is passed into the Assets constructor as the second parameter.

A simple example might be: 

	$filters = [
		'.js'		=> [
			'\Bonfire\Assets\Filters\JSMinPlus`
		],
		'.css'		=> [
			['\Bonfire\Assets\Filters\CSSMin', [ 'filters'=> ['ImportImports' => false]] ],
			'\Bonfire\Assets\Filters\URLRewrite'
		],
	];

For each file extension (which should include the '.'), you can specify as many filters as you desire. They are processed in the order presented in the array. 

The filter passed in is the class that will manipulate the contents. This class must implement the \Bonfire\Assets\FilterInterface.

If the filter needs additional configuration passed along, then an array should be passed as the filter, instead of a string. The first parameter is the class name, just like before. The second parameter is the array of items that should be passed to the class's constructor as parameters that can be set.

<a name="serving"></a>
## Serving Assets
Once your configuration is in place, create a new instance of the AssetPipeline, passing in the $config and $filter arrays as the first and second parameters, respectively. 

	$pipeline = new \Bonfire\Assets\AssetPipeline($config, $filters);
 
One call to the `process()` method and you're done. The library takes care of the rest.

	$pipeline->process();

The script attempts to determine the asset to serve based off `$_SERVER['REQUEST_URI']`. 

<a name="directives"></a>
## Directives
Directives are part of the files being served (only text-based files, like javascripts and stylesheets) that tell the system to pull in other files and combine them into this single file. This makes it simple to always reference a single stylesheet in your application's template, but easily keep those styles in multiple files for ease of programming and semantics. It also allows you to pull in assets from third-party modules that you use, and combine it on the fly not needing to worry if their styles get updated due to additional features, etc. 

Directives are included in the comments of files with the simple addition of an equal sign (=) after the comment marker. 

**Javascript**
	
	//= require jquery.min.js

** Stylesheets

	/*= require print.css

### Supported Directives
Currently, Bonfire Assets supports the following directives: 

* **require** - includes a single file. 
* **require_directory** - includes all files within a single folder. DOES NOT do recursion into subfolders.
* **require_tree** - includes all files within a single folder and all child folders.
* **exclude** - marks a file so that it WILL NOT be included in the final list.

Not that any content within the main file will also be included.

<a name="tags"></a>
## Custom Tags
To allow for more flexibility in the filenames/paths used as part of the directive, you can create custom `tags` that are processed over the filenames/folder names passed to the directive. This can provide a way to include from themes that may change in location, or from modules or third_party code. 

Multiple tags can be contained in a single directive. 

### Registering a Tag
You can register a new tag with the Assets library with the `registerTag()` method. 

	$pipeline->registerTag('themed', function ($str) {
		if (preg_match('/{theme:[a-zA-Z]+}/', $str, $matches) !== false)
            {
                if (isset($matches[0]))
                {
                    $theme_path = 'themes/';
                    $theme = trim( str_replace('theme:', '', $matches[0]), '{} ');

                    return $theme_path . $theme .'/'. str_replace($matches[0] .'/', '', $str);
                }
            }

            return $str;
	});

This example will look for things like `{theme:admin}` and replace it with the path to that theme.