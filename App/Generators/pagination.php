<?php

class Pagination {

	function __construct( $post_type = "post", $per_page = 10 ) {
		$this->post_type   = $post_type;
		$this->total_post  = wp_count_posts( $this->post_type )->publish;

		$this->per_page    = $per_page;

		$this->current     = get_query_var('page') == 0 ? 1 : get_query_var('page');

		$this->max         = ceil($this->total_post / $this->per_page);

		$this->querystring = $_SERVER['QUERY_STRING'] ? "?".$_SERVER['QUERY_STRING'] : "";
	}

	// ==================================================
	// > Get link
	// ==================================================
	function get_link($page) {
		// if ($pa <= 0) return "";
		// if ($pa >= $max) return "";
		return get_the_permalink(). '' .$page. '/' .$this->querystring;
	}

	// ==================================================
	// > Format number
	// ==================================================
	static function format($page) {
		return $page < 10 ? "0".$page : $page;
	}

	// ==================================================
	// > Reset page number
	// ==================================================
	function setTotalPosts($total) {
		$this->total_post = $total;
		$this->max = ceil($this->total_post / $this->per_page);
	}

	// ==================================================
	// > Generate Walker
	// ==================================================
	function walker($id = false, $class = false) {
		ob_start();
		?>
			<nav class="pagination-walker row <?= $class;?>" <?= $id?"id='$id'":"";?>>
				<div class="pagination-walker__pagelistwrapper gr-8 gr-12-xs">
					<ul class="pagination-walker__pagelist">

						<?php for($p = 1; $p <= $this->max; $p++): ?>
							<li <?= $p==$this->current?"class='current'":""; ?>>
								<a href="<?= $this->get_link($p); ?>"><?= $this->format($p); ?></a>
							</li>
						<?php endfor; ?>

					</ul>
				</div>
				<div class="gr-4 gr-12-xs">
					<a href="<?= $this->get_link($this->current-1); ?>" class="pagination-walker__direction pagination-walker__direction--previous <?= $this->current<=1?'disabled':''; ?>" title="Page précédente">Page précédente</a>
					<a href="<?= $this->get_link($this->current+1); ?>" class="pagination-walker__direction pagination-walker__direction--next <?= $this->current>=$this->max?'disabled':''; ?>" title="Page suivante">Page suivante</a>
				</div>
			</nav>
		<?php
		return ob_get_clean();
	}
}

