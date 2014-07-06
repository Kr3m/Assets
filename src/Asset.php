<?php

namespace Bonfire\Assets;

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local as Adapter;

class Asset {

    /**
     * Stores the adjusted output of text-based assets.
     * @var null
     */
    protected $content = null;

    /**
     * The name of the asset file we're working with.
     * @var null
     */
    protected $filename = null;

    /**
     * The path that we found the file in.
     * @var null
     */
    protected $filepath = null;

    protected $file_subfolder = '';

    /**
     * The 'type' of asset, which is divided into 7 categories:
     *
     *  - stylesheet
     *  - javascript
     *  - flash
     *  - audio
     *  - video
     *  - image
     *  - file  (default)
     *
     * @var null
     */
    protected $file_type = null;

    /**
     * The discovered mime type for the file when we send
     * it to the browser.
     * @var null
     */
    protected $file_mime = null;

    protected $file_timestamp = null;

    protected $file_extension = null;

    protected $asset_folders = [];

    protected $asset_type_folders = [
        'stylesheet'        => 'css',
        'javascript'        => 'js',
        'images'            => 'img',
        'audio'             => 'audio',
        'video'             => 'video',
        'flash'             => 'flash'
    ];

    /**
     * Stores any user-contributed 'mime types'
     * for the types listed above.
     * @var array
     */
    protected $user_mime_types = [];

    protected $mime_types = [
        'javascript'    => [
            'js'    => 'application/x-javascript',
        ],
        'stylesheet'    => [
            'css'   => 'text/css',
        ],
        'image'         => [
            'gif'   => 'image/gif',
            'jpg'   => 'image/jpeg',
            'jpeg'  => 'image/jpeg',
            'png'   => 'image/png',
            'tiff'  => 'image/tiff',
            'tif'   => 'image/tiff',
            'bmp'   => 'image/bmp',
            'psd'   => 'application/x-photoshop',
            'ai'    => 'application/postscript'
        ],
        'audio'         => [
            'mid'   => 'audio/midi',
            'midi'  => 'audio/midi',
            'mpga'  => 'audio/mpeg',
            'mp2'   => 'audio/mpeg',
            'mp3'   => 'audio/mp3',
            'aif'   => 'audio/x-aiff',
            'aiff'  => 'audio/x-aiff',
            'aifc'  => 'audio/x-aiff',
            'ram'   => 'audio/x-pn-realaudio',
            'rm'    => 'audio/x-pn-realaudio',
            'rpm'   => 'audio/x-pn-realaudio-plugin',
            'ra'    => 'audio/x-pn-realaudio-plugin',
        ],
        'video'         => [
            'mpeg'  => 'video/mpeg',
            'mpg'   => 'video/mpeg',
            'mpe'   => 'video/mpeg',
            'qt'    => 'video/quicktime',
            'mov'   => 'video/quicktime',
            'avi'   => 'video/msvideo',
            'movie' => 'video/x-sgi-movie',
        ],
        'flash'         => [
            'dcr'   => 'application/x-director',
            'dir'   => 'application/x-director',
            'dxr'   => 'application/x-director',
            'swf'   => 'application/x-shockwave-flash',
        ],
        'file'          => [
            'pdf'   => 'application/pdf',
            'csv'   => 'text/csv',
            'xls'   => 'application/excel',
            'ppt'   => 'application/powerpoint',
            'sit'   => 'application/x-stuffit',
            'tar'   => 'application/x-tar',
            'tgz'   => 'application/gzip',
            'zip'   => 'application/zip',
            'gzip'  => 'multipart/gzip',
            'html'  => 'text/html',
            'txt'   => 'text/plain',
            'md'    => 'text/plain'
        ]
    ];

    protected $tags = array();

    //--------------------------------------------------------------------

    public function __construct ($filename='', $folders = [], $mime_types = [], $asset_type_folders=null)
    {
        if (empty($filename) || !is_string($filename))
        {
            throw new \RuntimeException('A filename must be provided in the Asset constructor.');
        }

        if (! empty($asset_type_folders))
        {
            $this->asset_type_folders = $asset_type_folders;
        }

        $this->filename = $filename;

        $this->asset_folders = $folders;

        $this->user_mime_types = $mime_types;

        $this->locateFile();
    }

    //--------------------------------------------------------------------

    /**
     * Grabs the current state of the Asset's Contents and caches it.
     *
     * For text-based content types (js, css, etc) we'll read the file
     * in and give them that.
     *
     * @return null
     */
    public function getContent ()
    {
        // Have we cached it for this request?
        if (! empty($this->content))
        {
            return $this->content;
        }

        $contents = null;

        $file = $this->filepath . $this->filename;

        if (is_file($file))
        {
            switch ($this->file_type)
            {
                case 'stylesheet':
                case 'javascript':
                    $contents = file_get_contents($file);
                    $contents = $this->handleDirectives($contents);
                    break;
                case 'flash':
                case 'audio':
                case 'video':
                    break;
                case 'image':
                    // Potentially expensive process that helps
                    // when we're not compiling so images can still
                    // be displayed.
                    $contents = file_get_contents($file);
                    break;
                case 'file':
                    // We'll attempt to grab the contents
                    $contents = file_get_contents($file);
                    $contents = $this->handleDirectives($contents);
                    break;
            }
        }

        $this->content = $contents;

        return $this->content;
    }

    //--------------------------------------------------------------------

    /**
     * Holds onto tags while we're waiting
     * to process the directives.
     *
     * @param $tags
     */
    public function registerTags ($tags)
    {
        $this->tags = $tags;
    }

    //--------------------------------------------------------------------

    /**
     * Sets the current contents of the asset.
     *
     * @param $content
     */
    public function setContent ($content)
    {
        $this->content = $content;
    }

    //--------------------------------------------------------------------

    public function getType ()
    {
        return $this->file_type;
    }

    //--------------------------------------------------------------------

    public function getMime ()
    {
        return $this->file_mime;
    }

    //--------------------------------------------------------------------

    public function getExtension ()
    {
        return $this->file_extension;
    }

    //--------------------------------------------------------------------

    /**
     * Searches the asset folders and attempts to locate the file on
     * a first-come, first-served basis.
     *
     * @return bool
     */
    protected function locateFile ()
    {
        $this->file_mime = $this->determineFileMime( $this->filename );

        if (! is_array($this->asset_folders) || ! count($this->asset_folders))
        {
            return false;
        }

        foreach ($this->asset_folders as $folder)
        {
            $folder = realpath($folder) .'/';

            $local = new Filesystem( new Adapter($folder) );

            // Try without the asset-type folder
            if ($local->has($this->filename))
            {
                $this->filepath = $folder;

                return $this->getFileInfo($local, $folder);
            }

            if (! isset($this->asset_type_folders[ $this->file_type ]))
            {
                continue;
            }

            // Not here? Try with an asset folder
            if ($local->has( $this->asset_type_folders[ $this->file_type ] .'/'. $this->filename ))
            {
                $this->filepath = $folder . $this->asset_type_folders[ $this->file_type ] .'/';
                $this->file_subfolder = $this->asset_type_folders[ $this->file_type ];

                return $this->getFileInfo($local, $this->file_subfolder);
            }

            unset($local);
        }

        return false;
    }

    //--------------------------------------------------------------------

    /**
     * Grabs the information about the file, including what 'type' it is
     * as well as the more detailed mime types, etc.
     *
     * @param      $local       An instance of the FlySystem
     */
    protected function getFileInfo ($local)
    {
        $this->file_timestamp = $local->getTimestamp($this->file_subfolder .'/'. $this->filename);

        $this->file_extension = $this->determineFileExtension( $this->filename );

        // Ensure the extension has the leading period.
        $this->file_extension = '.'. ltrim($this->file_extension, '. ');
    }

    //--------------------------------------------------------------------

    protected function determineFileMime ($filename)
    {
        if (empty($filename)) return null;

        // First - check our user mimes to see if we match any of those...
        foreach ($this->user_mime_types as $type => $extensions)
        {
            if (! is_array($extensions)) continue;

            foreach ($extensions as $ext)
            {
                $start = strpos($filename, $ext);

                if (! $start)
                {
                    continue;
                }

                $this->file_extension = $ext;

                $this->file_type = $type;

                if (isset($this->mime_types[$type][ trim($ext, '.')]))
                {
                    return $this->mime_types[$type][ trim($ext, '.')];
                }
            }
        }

        // Still here? Check the generics from the class to find a match

        $ext = substr($filename, strrpos($filename, '.') + 1);

        foreach ($this->mime_types as $group => $extensions)
        {
            if (! is_array($extensions)) continue;

            if (array_key_exists($ext, $extensions))
            {
                $this->file_type = $group;

                return $extensions[$ext];
            }
        }

        // Default to something generic per the HTTP Spec 1.1
        return 'application/octet-stream';
    }

    //--------------------------------------------------------------------

    /**
     * Attempts to determine the correct file extension, accounting for any
     * double/triple extensions that might be included in user_mimes, like
     * .min.js, etc.
     *
     * @param $filename
     * @return mixed
     */
    protected function determineFileExtension ($filename)
    {
        // First, check our user_mimes for custom extensions.
        if (count($this->user_mime_types))
        {
            foreach ($this->user_mime_types as $mime => $filters)
            {
                if (strpos($filename, $mime) !== false)
                {
                    return $mime;
                }
            }
        }

        // Nothing found in the user mimes? then do the simple thing
        return pathinfo($this->filename, PATHINFO_EXTENSION);
    }

    //--------------------------------------------------------------------

    /**
     * Determines if a file is any type of URI (http, https, ftp, etc)
     *
     * @param $path
     * @return bool
     */
    protected function isUri($path)
    {
        return (bool)preg_match('/^[a-z]+:\/\//', $path);
    }

    //--------------------------------------------------------------------

    /**
     * Attempts to parse the file contents (str or array) for any directives.
     * If directives are found, they are parsed and their content added to
     * our current content.
     *
     * Basically, reads in the contents of our different files and joins them.
     *
     * @param $contents
     * @return string
     */
    protected function handleDirectives( $contents )
    {
        $parser = new \Bonfire\Assets\Directives\DirectivesParser();

        if (is_array($this->tags) && count($this->tags))
        {
            $parser->registerTags($tags);
        }

        $files = $parser->parse( $contents, $this->filename );

        if (is_array($files))
        {
            foreach ($files as $file)
            {
                $contents = "\n\n". file_get_contents($file);
            }
        }

        return $contents;
    }

    //--------------------------------------------------------------------

}