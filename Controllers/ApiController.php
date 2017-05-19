<?php

namespace Syltaen\Controllers;
use Syltaen\Models;

class ApiController extends Controller {

	static $default_view = false;

	public function __construct() {

	}

	// ==================================================
	// > POPULATE
	// ==================================================
	public function populate() {
		global $post;
	}

	// ==================================================
	// > CSV
	// ==================================================
	public function download_csv($filename = "export.csv", $delimiter = ";") {
		header('Content-Type: application/csv');
		header('Content-Disposition: attachment; filename="'.$filename.'";');
		$f = fopen('php://output', 'w');
		foreach ($array as $line) {
			fputcsv($f, $line, $delimiter);
		}
	}

}