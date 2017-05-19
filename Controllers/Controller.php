<?php

namespace Syltaen;
use Pug, Timber;

abstract class Controller {

	protected $data;
	private   $renderer;
	static    $default_view = "page";

	// ==================================================
	// > CONSTRUCT
	// ==================================================
	public function __construct( $auto = true ) {
		$this->renderer = new Pug\Pug([
			'extension' => '.pug',
		]);

		$this->data = Timber::get_context();

		if ($auto) {
			$this->populate();
			$this->render();
		}
	}


	// ==================================================
	// > POPULATE
	// ==================================================
	public function populate() {
		global $post;
	}


	// ==================================================
	// > RENDER PUG TEMPLATE
	// ==================================================
	public function render($view = false) {

		$view = $view ? $view : static::$default_view;
		$view = get_template_directory() . "/_6_views/". $view . ".pug";

		if (file_exists( $view )) :
			echo $this->renderer->render( $view, $this->data );
		else :
			$this->render("404");
		endif;

	}

}