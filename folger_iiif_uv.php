<?php
/*
Plugin Name:  Folger IIIF UV WP Plugin
Plugin URI:   https://parsonstko.com/
Description:  Plugin for embeding UV in Wordpress pages/posts.
Version:      0.1.0
Author:       Krzysztof Sabat at ParsonsTKO
Author URI:   https://parsonstko.com/
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html

folger_iiif_uv is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

folger_iiif_uv is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with folger_iiif_uv. If not, see https://parsonstko.com/.
*/

defined("ABSPATH") or die("Nothing to see here.");

class PTKO_folgerUVPlugin
{
	private $uvClass;
	private $admin;
	private $pluginPath;
	private $pluginFileSystemLocation;

    function activate()
    {
         if (current_user_can("activate_plugins") === false) {
             wp_die("You do not have the necessary access permissions to activate plugins.");
         };

    }

    function deactivate()
    {
        if (current_user_can("activate_plugins") === false) {
            wp_die("You do not have the necessary access permissions to deactivate plugins.");
        };

        deactivate_plugins(plugin_basename(__FILE__));
    }

    function uninstall()
    {

    }

    function setAdmin($admin)
    {
    	$this->admin = $admin;

    }

    function getAdmin()
    {
		return $this->admin;
    }

    function setUVPluginClass($inClass)
    {
	    $this->uvClass = $inClass;
    }

	function getUVPluginClass()
	{
		return $this->uvClass;
	}

	function setPluginPath($f = __FILE__)
	{
		$this->pluginPath = plugin_dir_url($f);
	}

	function getPluginPath()
	{
		return $this->pluginPath;
	}

	function setPluginFSPath($f = __FILE__)
	{
		$this->pluginFileSystemLocation = plugin_dir_path($f);
	}

	function getPluginFSPath()
	{
		return $this->pluginFileSystemLocation;
	}

    function __construct()
    {

        require_once(__DIR__ . "/admin/ptkoFolgerIIIFUVAdmin.php");
        require_once(__DIR__ . "/public/ptkoFolgerIIIFUV.php");

	    $this->setPluginPath(__FILE__);
	    $this->setPluginFSPath(__FILE__);

        $this->setUVPluginClass(new ptkoFolgerIIIFUV($this));
        $this->setAdmin(new ptkoFolgerIIIFUVAdmin($this));

        register_activation_hook(__FILE__, array($this, "activate"));

        add_shortcode("miranda_uv", array($this->uvClass, "UVEmbedShortcode") );

    }
};

$PTKO_folgerIIIFUVPlugin = new PTKO_folgerUVPlugin();
