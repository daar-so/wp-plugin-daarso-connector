<?php

/**
* Plugin name: Daar[so] management plugin
* Version: 1.3.3
**/

namespace Daarso;

class DaarsoPlugin
{

	private $varnishServers = ['10.4.0.1', '10.4.0.2'];
	private $urls = [];

	public function __construct()
	{
		add_filter('parse_request', [$this, 'handleParseRequest']);
		add_filter('rest_user_query', [$this, 'handleRestUserQuery']);
		
		if (!isset($_SERVER['HTTP_X_VARNISH']) && !wp_doing_cron()) {
			return;
		}
		add_action('init', [$this, 'init']);
		add_action('init', [$this, 'adminInit']);
	}
	
	public function adminInit()
	{
		if (get_site_option('permalink_structure') === '') {
			add_action('admin_notices', [$this, 'requirePrettyPermalinksNotice']);
		}
	}

	public function init()
	{
		$actions = [
			'autoptimize_action_cachepurged',
			'delete_attachment',
			'deleted_post',
			'edit_post',
			'import_start',
			'import_end',
			'save_post',
			'switch_theme',
			'trashed_post',
			'deactivated_plugin',
			'activated_plugin',
			'upgrader_process_complete',
			'updraftplus_restored_db',
			'updraftplus_restored_themes',
			'updraftplus_restored_uploads',
			'updraftplus_restored_wpcore',
		];
		
		$actions_noIds = [
			'autoptimize_action_cachepurged',
			'import_start',
			'import_end',
			'switch_theme',
			'activated_plugin',
			'deactivated_plugin',
			'upgrader_process_complete',
			'updraftplus_restored_db',
			'updraftplus_restored_themes',
			'updraftplus_restored_uploads',
			'updraftplus_restored_wpcore'
		];
		foreach ($actions as $action) {
			if (in_array($action, $actions_noIds)) {
				add_action($action, [$this, 'purge_noId'], 99);
			} else {
				add_action($action, [$this, 'purgePost'], 10, 2);
			}
		}
		add_action('shutdown', [$this, 'doPurge'], 99);
		
		if (isset($_GET['varnish_flush_do']) && check_admin_referer('varnish_flush_do')) {
			add_action('admin_notices', [$this, 'messagePurged']);
		}
		add_action('admin_bar_menu', [$this, 'adminBar'], 100);
		add_action( 'admin_enqueue_scripts', [$this, 'custom_css'] );
		add_action( 'wp_enqueue_scripts', [$this, 'custom_css']);

	}
	
	public function adminBar($adminbar)
	{
		global $wp;
		$args = [];
		if ((!is_admin() && get_post() !== false && current_user_can('edit_published_posts')) || current_user_can('activate_plugins')) {
			$args[] = [
				'id' => 'purge-varnish-cache',
				'title' => '<span class="ab-icon" style="background-image: url(' . self::get_icon_svg() . ') !important;"></span>',
				'meta' => [
					'class' => 'purge-varnish-cache'
				],
			];
		}
		if (current_user_can('activate_plugins')) {
			$args[]= [
				'parent' => 'purge-varnish-cache',
				'id' => 'purge-varnish-all',
				'title' => 'Varnish cache legen (alle)',
				'href' => wp_nonce_url(add_query_arg('varnish_flush_do', 'all'), 'varnish_flush_do'),
				'meta' => [
					'title' => 'Leeg de Varnish cache voor deze website'
				]	
			];
		}
		if (!is_admin() && get_post() !== false && current_user_can('edit_published_posts')) {
			$args[]= [
				'parent' => 'purge-varnish-cache',
				'id' => 'purge-varnish-this',
				'title' => 'Varnish cache legen (deze pagina)',
				'href' => wp_nonce_url(add_query_arg('varnish_flush_do', esc_url(home_url($wp->request)) . '/'), 'varnish_flush_do'),
				'meta' => [
					'title' => 'Verwijder cache voor deze pagina'
				]	
			];
		}
			
		
		foreach ($args as $arg) {
			$adminbar->add_node($arg);
		}
	}

	public static function get_icon_svg($base64 = true)
	{
		$svg = '<svg version="1.0" xmlns="http://www.w3.org/2000/svg"
			viewBox="0 0 192.000000 192.000000"
			preserveAspectRatio="xMidYMid meet">
			<g transform="translate(0.000000,192.000000) scale(0.100000,-0.100000)"
			fill="#e05616" stroke="none">
			<path d="M775 1910 c-16 -5 -57 -16 -89 -25 -166 -46 -359 -180 -471 -325
			-141 -183 -200 -359 -200 -600 0 -149 19 -256 61 -348 27 -61 29 -47 -32 -262
			-21 -74 -39 -163 -42 -197 -3 -56 -2 -63 15 -63 15 0 107 24 327 85 l48 13 67
			-42 c73 -46 189 -101 236 -111 17 -4 49 -13 73 -21 29 -10 94 -14 210 -14 151
			0 176 3 257 26 313 91 546 308 656 608 l29 80 0 246 0 246 -29 80 c-105 286
			-323 499 -611 596 -92 32 -100 33 -285 35 -104 1 -203 -2 -220 -7z m340 -232
			c18 -16 30 -42 41 -90 15 -66 15 -67 62 -82 l46 -16 53 35 c82 54 108 50 194
			-32 61 -59 71 -74 76 -111 5 -38 1 -49 -32 -98 l-38 -55 21 -44 c18 -38 27
			-45 59 -50 68 -12 103 -26 119 -50 19 -30 28 -109 20 -178 -10 -75 -37 -102
			-118 -116 -33 -6 -62 -12 -62 -13 -1 -2 -10 -22 -21 -46 l-18 -42 32 -45 c63
			-87 53 -134 -49 -228 -76 -71 -105 -74 -185 -21 l-54 36 -44 -17 c-40 -15 -45
			-21 -51 -56 -10 -64 -35 -115 -62 -128 -32 -14 -196 -14 -227 0 -29 13 -40 35
			-57 111 -12 53 -15 57 -56 73 l-43 16 -53 -35 c-81 -55 -111 -51 -192 25 -96
			89 -105 139 -42 222 30 40 31 44 10 96 -15 37 -19 40 -67 46 -61 9 -102 28
			-117 54 -5 11 -10 65 -10 121 0 98 1 102 28 130 21 20 43 30 78 35 70 9 69 9
			88 58 l17 46 -35 52 c-55 79 -51 115 22 191 94 99 128 108 209 53 29 -19 56
			-35 60 -35 4 0 26 7 49 16 38 14 42 19 48 60 8 63 32 113 58 124 13 5 67 9
			120 9 87 1 100 -1 123 -21z"/>
			<path d="M915 1544 c-11 -91 -15 -96 -102 -132 -44 -17 -90 -32 -103 -32 -13
			0 -48 17 -77 37 l-54 37 -44 -44 -44 -43 39 -57 c22 -31 40 -65 40 -77 0 -12
			-11 -45 -25 -75 -14 -29 -25 -61 -25 -70 0 -25 -36 -45 -108 -59 l-63 -12 3
			-60 3 -60 73 -17 c40 -10 74 -19 76 -21 3 -3 15 -31 68 -160 10 -23 6 -33 -33
			-88 l-43 -61 45 -45 45 -45 54 40 c29 22 63 40 74 40 22 0 164 -56 180 -71 5
			-4 14 -39 20 -76 l11 -68 65 0 65 0 11 68 c6 37 15 72 20 76 17 16 158 71 182
			71 14 0 46 -16 72 -35 26 -19 51 -35 56 -35 5 0 27 19 51 42 l42 41 -33 46
			c-56 78 -59 86 -39 123 10 18 27 58 38 88 21 59 21 59 148 85 26 5 27 8 27 65
			0 57 -1 60 -27 66 -136 31 -126 25 -150 90 -11 32 -28 72 -36 88 -18 34 -15
			45 34 112 l38 51 -43 42 c-23 22 -45 41 -50 41 -5 0 -30 -16 -56 -35 -57 -42
			-79 -43 -156 -10 -114 50 -108 43 -119 120 l-10 70 -66 3 -67 3 -7 -57z m222
			-294 c115 -57 166 -139 171 -277 3 -81 1 -98 -21 -147 -32 -68 -96 -132 -167
			-164 -68 -31 -182 -34 -245 -7 -69 30 -129 81 -165 141 -34 57 -35 61 -35 164
			0 101 1 107 33 162 88 153 268 207 429 128z"/>
			<path d="M895 1161 c-55 -25 -101 -81 -120 -146 -15 -50 -15 -60 -1 -107 59
			-203 321 -230 419 -43 56 106 0 251 -115 299 -50 21 -134 20 -183 -3z"/>
			</g>
			</svg>';
		if ($base64) {
			return 'data:image/svg+xml;base64,' . base64_encode($svg);
		}
		return $svg;
	}

	public function doPurge()
	{
		$urls = array_unique($this->urls);
		if (!$urls && (isset($_GET['varnish_flush_do']) && check_admin_referer('varnish_flush_do'))) {
			if ($_GET['varnish_flush_do'] == 'all') {
				$this->purgeUrl(home_url() . '/?purge-regex');
			} else {
				$p = wp_parse_url(esc_url_raw(wp_unslash($_GET['varnish_flush_do'])));
				if (!isset($p['host'])) {
					return;
				}
				$this->purgeUrl(esc_url_raw(wp_unslash($_GET['varnish_flush_do'])));
			}
		} else {
			foreach ($urls as $url) {
				$this->purgeUrl($url);
			}
		}
	}
	
	public function purgeUrl($url) 
	{
		$p = wp_parse_url($url);
		$pregex = '';

		if (!isset($p['host'])) {
			return;
		}
		
		
		$path = '/';
		if (isset($p['path'])) {
			$path = $p['path'];
		}
		$host = $p['host'];
		$meth = 'default';
		if (isset($p['query'])) {
			if ($p['query'] == 'purge-regex') {
				$meth = 'regex';
				$pregex = '.*';
			} else {
				$path .= '?' . $p['query']; 
			}
		}
		
		foreach ($this->varnishServers as $server) {
			$fp = @fsockopen($server, 6081, $errno, $errstr, 5);
			if (!$fp) {
				continue;
			}
			@fwrite($fp, sprintf("BAN %s HTTP/1.0\r\n", $path . $pregex));
			@fwrite($fp, sprintf("Host: %s\r\n", $host));
			@fwrite($fp, sprintf("X-Purge-Method: %s\r\n", $meth));
			@fwrite($fp, "Connection: close\r\n\r\n");
			@fread($fp, 2048);
			fclose($fp);
		}
	}
	
	public function custom_css() 
	{
		if ( is_user_logged_in() && is_admin_bar_showing() ) {
			wp_register_style( 'varnish_http_purge', plugins_url( 'style.css', __FILE__ ) );
			wp_enqueue_style( 'varnish_http_purge' );
		}
	}
	
	public function purge_noId()
	{
		$this->urls = [home_url() . '/?purge-regex'];
	}
	
	public function purgePost($postId)
	{
		$validPostStatus = ['publish', 'private', 'trash'];
		$thisPostStatus = get_post_status($postId);
		$invalidPostType = ['nav_menu_item', 'revision'];
		$noArchivePostType = ['post', 'page'];
		$thisPostType = get_post_type($postId);
		$urlList = [];
		if (get_permalink($postId) === false || !in_array($thisPostStatus, $validPostStatus, true) || in_array($thisPostType, $invalidPostType, true)) {
			if ($thisPostType == 'nav_menu_item') {
				$this->purge_noId();
			}
			return;
		}
		
		$urlList[] = get_permalink($postId);
		
		if ($thisPostStatus === 'trash') {
			$trashPost = get_permalink($postId);
			$trashPost = str_replace('__trashed', '', $trashPost);
			$urlList[] = $trashPost;
			$urlList[] = $trashPost . 'feed/';
		}
		
		$categories = get_the_category($postId);
		if ($categories) {
			foreach ($categories as $cat) {
				$urlList[] = get_category_link($cat->term_id);
			}
		}
		
		$tags = get_the_tags($postId);
		if ($tags) {
			foreach ($tags as $tag) {
				$urlList[] = get_tag_link($tag->term_id);
			}
		}
		
		$taxonomies = get_post_taxonomies($postId);
		if ($taxonomies) {
			foreach ($taxonomies as $taxonomy) {
				$features = (array)get_taxonomy($taxonomy);
				if ($features['public']) {
					$terms = wp_get_post_terms($postId, $taxonomy);
					foreach ($terms as $term) {
						$urlList[] = get_term_link($term);
					}
				}
			}
		}
		
		if ($thisPostType && $thisPostType == 'post') {
			$authorId = get_post_field('post_author', $postId);
			$urlList[] = get_author_posts_url($authorId);
			$urlList[] = get_author_feed_link($authorId);
			$urlList[] = get_bloginfo_rss('rdf_url');
			$urlList[] = get_bloginfo_rss('rss_url');
			$urlList[] = get_bloginfo_rss('rss2_url');
			$urlList[] = get_bloginfo_rss('atom_url');
			$urlList[] = get_bloginfo_rss('comments_rss2_url');
			$urlList[] = get_post_comments_feed_link($postId);
		}
		
		if ($thisPostType && !in_array($thisPostType, $noArchivePostType, true)) {
			$urlList[] = get_post_type_archive_link(get_post_type($postId));
			$urlList[] = get_post_type_archive_feed_link(get_post_type($postId));
		}
		
		if (get_site_option('show_on_front') == 'page') {
			if (get_site_option('page_for_posts')) {
				$urlList[] = get_permalink(get_site_option('page_for_posts'));
			}
		} else {
			$urlList[] = home_url() . '/';
		}
		
		foreach ($urlList as $url) {
			$url = strtok($url, '?');
			$this->urls[] = $url;
		}
	}
	
	private function bailUserEnum($is_json)
	{
		if ($is_json) {
			header('HTTP/1.1 403 Forbidden');
			exit;
		}
		wp_die('Forbidden', 'Forbidden', ['response' => 403]);
	}
	
	public function requirePrettyPermalinksNotice()
	{
		echo wp_kses_post('<div id="message" class="error"><p>' . sprintf('Om Varnish correct te kunnen gebruiken moet de permalink structuur ingesteld worden. Ga naar <a href="%s">Permalink opties</a> om dit in te stellen.', esc_url(admin_url('options-permalink.php'))) . '</p></div>');
	}
	
	public function  messagePurged()
	{
		echo '<div id="message" class="notice notice-success fade is-dismissible"><p><strong>Varnish cache geleegd!</strong></p></div>';
	}
	
	public function handleParseRequest($query)
	{
		if (!current_user_can('list_users') && intval(@$query->query_vars['author'])) {
			return $this->bailUserEnum(false);
		}
		return $query;
	}
	
	public function handleRestUserQuery($prepared_args)
	{
		if (!current_user_can('list_users')) {
			return $this->bailUserEnum(true);
		}
		return $prepared_args;
	}
}

new DaarsoPlugin();