<?php

namespace Syltaen;

class Syltaen {

	const PATHS = [
		"functions"   => "_1_functions",
			"config"     => "_1_functions/_1_config",
			"vendors"    => "_1_functions/_2_vendors",
			"tools"      => "_1_functions/_3_tools",
			"generator"  => "_1_functions/_4_generators",
			"hooks"      => "_1_functions/_5_hooks",
			"ajax"       => "_1_functions/_6_ajax",
		"assets"      => "_2_assets",
		"scripts"     => "_3_scripts",
		"styles"      => "_4_styles",
		"models"      => "_5_models",
		"views"       => "_6_views",
		"controllers" => "_7_controllers"
	];

	// ==================================================
	// > FILE LOADER
	// ==================================================
	public static function load($folder, $files = false) {
		if (is_array($files)) {
			foreach ($files as $file) {
				require_once(self::path($folder) . $file . ".php");
			}
		} else {
			require_once(self::path($folder) . $files . ".php");
		}
	}

	// ==================================================
	// > FOLDER RESOLUTION
	// ==================================================
	public static function folder($key) {
		return self::PATHS[$key];
	}

	// ==================================================
	// > PATH RESOLUTION
	// ==================================================
	public static function path($key) {
		return get_stylesheet_directory() . "/" . self::folder($key) . "/";
	}

	// ==================================================
	// > CLASS AUTOLOADER
	// ==================================================
	public static function autoload($class) {
		if (preg_match('/Syltaen/', $class)) {

			$class = substr( $class, 8);

			if (preg_match('/Controller/', $class)) {
				self::load("controllers", $class);
			} else {
				self::load("models", $class);
			}
		}
	}

}

