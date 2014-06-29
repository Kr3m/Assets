<?php

namespace Bonfire\Assets;

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local as Adapter;

class AssetPipeline
{

    protected $environment = 'production';

    protected $live_assets_folder = 'assets/';

    protected $asset_folders = array();

    protected $asset_url = '/assets';

    protected $asset_type_folders = array(
        'stylesheets' => 'css',
        'javascript'  => 'js',
        'images'      => 'img',
        'audio'       => 'audio',
        'video'       => 'video',
        'flash'       => 'flash'
    );

    protected $mime_types = array(
        'stylesheets' => array(
            '.css',
            '.css.less',
            '.css.scss',
            '.less',
            '.scss',
            'min.css'
        ),
        'javascript'  => array(
            '.js',
            '.js.coffee',
            '.coffee',
            '.min.js'
        )
    );

    protected $filters = array();

    protected $filename = null;

    //--------------------------------------------------------------------

    public function __construct ($filename=null, $config = NULL, $filters = array())
    {
        if (is_array($config) && count($config))
        {
            array_walk($config, function ($item, $key)
            {
                if (isset($this->{$key}))
                {
                    $this->{$key} = $item;
                }
            });
        }

        $this->filters = $filters;

        $this->filename = $filename;

        if (is_null($filename))
        {
            $this->detectFile();
        }
    }

    //--------------------------------------------------------------------

    /**
     * Creates an Asset object from the file, processes it according to
     * the filters the user passed in, and returns the content.
     *
     * @return null
     */
    public function process ()
    {
        $asset = new Asset( $this->filename, $this->asset_folders, $this->mime_types, $this->asset_type_folders);

        $this->applyFilters($asset);

        return $asset->getContent();
    }

    //--------------------------------------------------------------------

    /**
     * Applies any specified filters to the contents of the
     *
     * @param Asset $asset
     */
    public function applyFilters (Asset $asset)
    {
        // Are there any available filters listed for this type of asset?
        if (! isset($this->filters[ $asset->getExtension() ]) ||
            ! is_array( $this->filters[ $asset->getExtension() ] ))
        {
            return;
        }

        // Run the filters on the asset's content, modifying it as we go...
        foreach ( $this->filters[ $asset->getExtension() ] as $filter)
        {
            // When parameters are passed as an array,
            // the first is the name of the class,
            // while the others are parameters that are passed
            // into the class constructor
            if (is_array($filter))
            {
                $class_name = $filter[0];

                if (! isset($filter[1]))
                {
                    $filter[1] = null;
                }

                $class = new $class_name( $filter[1] );
            }
            else
            {
                $class = new $filter();
            }

            // The classes are responsible for modifying the classes contents.
            $class->run( $asset );

            unset($class);
        }
    }

    //--------------------------------------------------------------------


    //--------------------------------------------------------------------
    // Private Methods
    //--------------------------------------------------------------------

    /**
     * Examines the current request URI and grabs our file information
     * from it and stores for use by the rest of the library.
     *
     * Called during the constructor.
     */
    private function detectFile ($file=null)
    {
        // Get our Request URI and strip any $_GET vars
        $uri = basename( $_SERVER['REQUEST_URI'] );

        if (empty($uri))
        {
            return;
        }

        $parts = pathinfo( $uri );

        if (isset($parts['basename']) && ! empty($parts['basename']))
        {
            $this->filename = $parts['basename'];
        }

        unset($parts);
    }

    //--------------------------------------------------------------------

}