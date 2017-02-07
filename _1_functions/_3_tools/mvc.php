<?php

use Pug\Pug;
$renderer = new Pug(array(
	// 'prettyprint' => true,
	'extension' => '.pug',
	// 'cache' => 'pathto/writable/cachefolder/'
));

// ==================================================
// > VIEWS RENDERING
// ==================================================
function render($files, $data = array(), $noecho = false) {
	global $renderer;

	/*=====  FILE  =====*/
	if (is_array($files)):
		$filename = "";
		for($i = count($files); $i > 0; $i--):
			$filename = get_template_directory() . '/_6_views/'. $files[$i-1].'.pug';
			if (file_exists( $filename )) { break; }
		endfor;
	else:
		$filename =  get_template_directory() . '/_6_views/'. $files . '.pug';
	endif;

	/*=====  ERRORS  =====*/


	/*=====  OUTPUT  =====*/
	if ($noecho) {
		return $renderer->render( $filename, $data );
	} else {
		echo $renderer->render( $filename, $data );
	}
}


// ==================================================
// > DATA MODELS
// ==================================================
function model($files, $spec = array()) {
	global $data, $s, $c;

	/*=====  FILE  =====*/
	if (is_array($files)):
		$filename = "";
		for($i = count($files); $i > 0; $i--):
			$filename = get_template_directory() . '/_5_models/'. $files[$i-1].'.php';
			if (file_exists( $filename )) { break; }
		endfor;
	else:
		$filename =  get_template_directory() . '/_5_models/'. $files . '.php';
	endif;

	/* ========= INCLUDE ========= */
	include($filename);
}


// ==================================================
// > ERRORS LOGING
// ==================================================
set_error_handler(function($errno, $errstr, $errfile, $errline ) {
	ChromePhp::error("PHP ERROR $errno : $errstr");
	return false;
});