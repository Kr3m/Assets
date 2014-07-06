<?php

namespace Bonfire\Assets\Directives;

/**
 * Class DirectivesParser
 *
 * Responsible for parsing the contents of a file and invoking
 * any required
 *
 * @package Bonfire\Assets
 */
class DirectivesParser {

    /**
     * Stores the array of files that we've found
     * and will be returning.
     * @var array
     */
    protected $files = [];

    /**
     * Stores the {tags} that can
     * be detected withing directives
     * for path manipulations.
     *
     * @var array
     */
    protected $tags = [];

    //--------------------------------------------------------------------

    /**
     * The main entry point to the class. Does the actual parsing
     * of directives from the lines of a file.
     *
     * @param $lines
     * @param $filename
     * @return array
     */
    public function parse ( $lines, $filename )
    {
        $included = [];
        $excluded = [];

        // If we're one long string, bust us up
        // into lines that we can parse.
        if (is_string($lines))
        {
            $lines = explode("\n", $lines);
        }

        if (! is_array($lines))
        {
            $lines = array($lines);
        }

        if (! count($lines))
        {
            return [];
        }

        foreach ($lines as $line)
        {
            list ($include, $exclude) = $this->processDirectiveFromLine($line, $filename);

            $included = array_merge($included, $include);
            $excluded = array_merge($excluded, $exclude);
        }

        $this->files = array_unique( array_diff($included, $excluded) );

        return $this->files;
    }

    //--------------------------------------------------------------------

    /**
     * Given an individual line, attempts to find a directive within it.
     * Allows for a few placeholder tags to be used to build a base path:
     *
     *      {theme:name}    - replace 'name' with the theme's name
     *      {module:name}   - replace 'name' with the desired module's name.
     *
     * @param       $line
     * @param       $filename
     * @param array $tokens
     * @return array
     */
    public function processDirectiveFromLine ($line, $filename, $tokens = ['//= ', '/*= ', '#= '])
    {
        $line = ltrim($line);

        if (! $line)
        {
            return $this->structureDirectiveResults( [] );
        }

        foreach ($tokens as $token)
        {
            if (strpos($line, $token) === 0)
            {
                $directive = trim(substr($line, strlen($token)));
                $results = $this->processDirective($directive, $filename);

                return $this->structureDirectiveResults($results);
            }
        }

        return $this->structureDirectiveResults( [] );
    }

    //--------------------------------------------------------------------

    /**
     * Returns an array of files based on the directive.
     *
     * @param $line
     * @param $filename
     * @return array|null
     */
    public function processDirective ($line, $filename)
    {
        if (strpos($line, ' ') === false)
        {
            return null;
        }

        list($directive, $d_file) = explode(' ', $line);

        // Translate special tags
        $d_file = $this->translateTags($d_file);

        $results = [
            'include' => [],
            'exclude' => []
        ];

        switch (trim($directive))
        {
            case 'require':
                $results['include'] = array_merge($results['include'], $this->d_require($d_file));
                break;
            case 'require_tree':
                $files = $this->d_require_directory($d_file, true);
                break;
            case 'require_directory':
                $files = $this->d_require_directory($d_file, false);
                break;
            case 'exclude':
                $results['exclude'] = array_merge($results['exclude'], $this->d_exclude($d_file));
                break;
        }

        return $results;
    }
    
    //--------------------------------------------------------------------

    /**
     * Returns a structured array from $results, to be used like:
     *
     *      list($include, $exclude) = $this->structureDirectiveResults($results);
     *
     * @param $results
     * @return array
     */
    public function structureDirectiveResults ($results)
    {
        $data = [ [], [] ];

        if (!is_array($results))
        {
            return $data;
        }

        // Do we already have an include array?
        if (array_key_exists('include', $results))
        {
            $data[0] = $results['include'];
        }

        // Do we already have an exclude array?
        if (array_key_exists('exclude', $results))
        {
            $data[1] = $results['exclude'];
        }

        return $data;
    }

    //--------------------------------------------------------------------

    //--------------------------------------------------------------------
    // Tags
    //--------------------------------------------------------------------

    /**
     * Registers a tag pattern to be used when processing directive paths.
     *
     * The pattern is used as the name when it's stored and can be de-registered
     * by passing the pattern.
     *
     * The callback method must take 1 parameter: the $filename that we're processing
     * and return the processed string.
     *
     * @param         $name         Just something to reference it by if we need to deregister.
     * @param \Closure $callback     The method called on the string.
     * @return $this
     */
    public function registerTag ($name, \Closure $callback)
    {
        // If it already exists, simply overwrite the callback.
        $this->tags[$name] = $callback;

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Removes a tag from the queue to process for each directive.
     *
     * @param $name
     * @return $this
     */
    public function deregisterTag ($name)
    {
        if (isset($this->tags[$name]))
        {
            unset($this->tags[$name]);
        }

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Converts custom tags (like {theme:x}) into the appropriate
     * filepath.
     *
     * @param $filename
     * @return string
     */
    public function translateTags ($filename)
    {
        foreach ($this->tags as $name => $callback)
        {
            $filename = $callback($filename);
        }
        return $filename;
    }

    //--------------------------------------------------------------------


    //--------------------------------------------------------------------
    // The Directives
    //--------------------------------------------------------------------

    /**
     * Return the filename for the 'require' directive.
     *
     * @param $file_path
     * @return array
     */
    protected function d_require ($file_path)
    {
        return array($file_path);
    }

    //--------------------------------------------------------------------

    /**
     * Return the filename for the 'exclude' directive.
     *
     * @param $file_path
     * @return array
     */
    protected function d_exclude ($file_path)
    {
        return array($file_path);
    }

    //--------------------------------------------------------------------

    /**
     * @param      $file_path
     * @param bool $recursive
     */
    protected function d_require_directory ($file_path, $recursive=false)
    {

    }

    //--------------------------------------------------------------------

}