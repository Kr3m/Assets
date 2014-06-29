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

    protected $files = [];

    //--------------------------------------------------------------------

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
     * Converts custom tags (like {theme:x}) into the appropriate
     * filepath.
     *
     * @param $filename
     * @return string
     */
    private function translateTags ($filename)
    {
        // Theme
        if (preg_match('/{theme:[a-zA-Z]+}/', $filename, $matches) !== false)
        {
            $theme_path = APPPATH .'../themes/';
            $theme = trim( str_replace('theme:', '', $matches[0]), '{} ');

            return $theme_path . $theme .'/'. str_replace($matches[0] .'/', '', $filename);
        }
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