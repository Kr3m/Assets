<?php

include '../vendor/autoload.php';

use Bonfire\Assets;

/**
 * Example implementation of the pipeline controller.
 * It's primary job is to setup your configuration and
 * call the AssetPipeline library to do the grunt work.
 *
 * You should route any calls to files within the 'assets'
 * folder (or whichever you specify) to this script so that
 * the files will be served up in the manner specified below.
 *
 * If in production mode, this will auto-generate the files
 * and place them in your 'assets' folder.
 */

$config = array(

    /*
     * The environment impacts the way that files are handled
     * when the pipeline is enabled.
     *
     * 'development' will generate requested files on the fly
     * during every request. This ensures that files which typically
     * change frequently during development will not get cached
     * by the browser if they've changed.
     *
     * 'production' will generate the files and put them into the
     * defined 'assets' folder so they can be served up as static
     * files by the server and have the fastest performance.
     */
    'environment'           => 'development',

    /*
     * The folder, relative to this script, where the assets should
     * be placed when cached or moved for accessibility reasons.
     *
     * WITH a trailing slash.
     */
    'live_assets_folder'    => dirname(__FILE__) .'/assets/',

    /*
     * An array of paths on the server that assets should
     * be scanned for files. This allows files to be stored
     * within the application, or even within third-party folders,
     * and be easily updated as separate entities, and yet still
     * be served by the system.
     */
    'asset_folders'         => array (
        '../fake_assets/'
    ),

    /*
     * The path to be added to the site's domain that is where
     * we expect to find the assets. This is used when determining
     * which parts of the URL to map to, allowing for files in
     * different sub-folders to still work out.
     */
    'asset_url'             => 'assets',

    /*
     * Specifies the names of the sub-folders within the main 'assets'
     * folder that files should be stored in. Customize these to the
     * folder  names that match what you typically use on your projects.
     */
    'asset_type_folders'    => array(
        'stylesheets'       => 'css',
        'javascript'        => 'js',
        'images'            => 'img',
        'audio'             => 'audio',
        'video'             => 'video',
        'flash'             => 'flash'
    ),

    //--------------------------------------------------------------------
    // End Basic Customization Options
    //      The following options should work for most people without
    //      much customization but is presented here so you have the
    //      opportunity to fine-tune for the file types you work with.
    //--------------------------------------------------------------------

    /*
     * Determines how file extensions map to mime types within the system.
     * The system needs to know file extensions for any files that you will
     * be serving, or it will be served as a generic download.
     *
     * Note that common video and image formats will automatically be given
     * the appropriate mime type based on the Config/Mimes file in this app.
     */
    'mime_types' => array(
        'stylesheet'    => array('.css', '.css.less', '.css.scss', '.less', '.scss', 'min.css'),
        'javascript'    => array('.js', '.js.coffee', '.coffee', '.min.js')
    ),
);

//--------------------------------------------------------------------

//--------------------------------------------------------------------
// Filters Configuration
//--------------------------------------------------------------------

/**
 * Filters allow you to specify which actions are placed on each file type,
 * based on their file extension. This allows you to fine-tune how things
 * work on based on your workflow and needs. Common elements are defined
 * below for the provided mime types listed above.
 *
 * If no data is provided for a specific mime type, no action will be taken
 * and the file will be served as-is.
 */
$filters = array(
    '.min.js'       => array(),
    '.min.css'      => array(),
    '.js'           => array(
        '\Bonfire\Assets\Filters\JSMin'
    ),
    '.js.coffee'    => array(),
    '.coffee'       => array(),
    '.css'          => array(
        ['\Bonfire\Assets\Filters\CSSMin', [ 'filters'=> ['ImportImports' => true]] ]
    ),
    '.css.less'     => array(),
    '.css.scss'     => array(),
    '.less'         => array(),
    '.scss'         => array(),
    '.min.css'      => array(),
);

//--------------------------------------------------------------------
// End of Configuration Settings
//--------------------------------------------------------------------

include './AssetPipeline.php';
include './Asset.php';

$pipeline = new \Bonfire\Assets\AssetPipeline($config, $filters);

//echo $pipeline->process();

echo '<pre>'. print_r($pipeline->process( ), true);