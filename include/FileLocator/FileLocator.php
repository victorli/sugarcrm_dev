<?php
/*********************************************************************************
 * SugarCRM Community Edition is a customer relationship management program developed by
 * SugarCRM, Inc. Copyright (C) 2004-2012 SugarCRM Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SUGARCRM, SUGARCRM DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact SugarCRM, Inc. headquarters at 10050 North Wolfe Road,
 * SW2-130, Cupertino, CA 95014, USA. or at email address contact@sugarcrm.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * SugarCRM" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by SugarCRM".
 ********************************************************************************/


require_once 'include/FileLocator/FileLocatorInterface.php';

/**
 * A simple File Locator class. When created you pass in an array of paths for it to search.
 *
 */
class FileLocator implements FileLocatorInterface
{
    /**
     * Paths to check when we try and locate a file
     *
     * @var array
     */
    private $paths;

    /**
     * Constructor
     *
     * @param array $paths      Where we want to look for files at
     */
    public function __construct(array $paths)
    {
        $this->paths = (array)$paths;
    }


    /**
     * Try and find a file in the paths that were passed in when the object was created
     *
     * @param string $name          Name of the file we are looking for
     * @return bool|string          Returns the path of the file if one is found.  If not boolean FALSE is returned
     */
    public function locate($name)
    {
        if($this->isAbsolutePath($name) && file_exists($name))
        {
          return $name;
        }

        foreach($this->paths as $path)
        {
            $file = $path . DIRECTORY_SEPARATOR . $name;
            if(file_exists($file) && is_file($file))
            {
                return $file;
            }
        }
        return false;
    }

    /**
     * Set new Paths to check
     *
     * @param array $paths
     */
    public function setPaths($paths)
    {
        $this->paths = (array)$paths;
    }

    /**
     * Return the current set paths
     *
     * @return array
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * Check to see if the file contains an absolute path to the file.
     *
     * @param string $file      The file to check if the path is an absolute path or not
     * @return bool             True if file is an Absolute Path; False if not.
     */
    private function isAbsolutePath($file)
    {
        if ($file[0] == '/' || $file[0] == '\\'
            || (strlen($file) > 3 && ctype_alpha($file[0])
                && $file[1] == ':'
                && ($file[2] == '\\' || $file[2] == '/')
            )
        ) {
            return true;
        }

        return false;
    }

}
