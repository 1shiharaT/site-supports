<?php

/**
 * Class Ggs_Admin
 * 管理画面用クラス
 */
class Ggs_Admin {

	/** @var string 設定ページのスラッグ */
	public $option_page_slug = '';

	public $option_setting_slug = '';

	public $prefix = '';

	/**
	 * 初期化
	 */
	public function __construct() {
		$this->option_setting_slug = 'ggsupports_settings';
		$this->option_page_slug    = 'ggsupports_options_page';
		$this->prefix              = 'ggsupports_';
		add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widgets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'load-index.php', array( $this, 'hide_welcome_panel' ) );
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'settings_init' ) );
		add_action( 'wp_ajax_update_ggsupports_option', array( $this, 'update_ggsupports_option' ) );
	}

	/**
	 * ダッシュボードに登録
	 */
	public function add_dashboard_widgets() {
		if ( Ggs_Helper::get_ggs_options( 'ggsupports_dashboard_dashboard_disp' ) ) {
			wp_add_dashboard_widget( 'ggs_dashboard_widget', get_bloginfo( 'title' ), function () {
				include( 'views/dashboard.php' );
			} );
			global $wp_meta_boxes;
			$normal_dashboard      = $wp_meta_boxes['dashboard']['normal']['core'];
			$example_widget_backup = array( 'ggs_dashboard_widget' => $normal_dashboard['ggs_dashboard_widget'] );
			unset( $normal_dashboard['ggs_dashboard_widget'] );
			$sorted_dashboard                             = array_merge( $example_widget_backup, $normal_dashboard );
			$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
		}
	}


	/**
	 * ウェルカムパネルを非表示に
	 * @return void
	 */
	function hide_welcome_panel() {
		$user_id = get_current_user_id();

		if ( 1 == get_user_meta( $user_id, 'show_welcome_panel', true ) ) {
			update_user_meta( $user_id, 'show_welcome_panel', 0 );
		}
	}

	/**
	 * 楽天API設定画面を作成
	 * @return void
	 */
	public function settings_init() {

		// 設定項目を登録
		register_setting( 'ggsupports_settings', 'ggsupports_options' );

		/**
		 * 1. サイト設定
		 */
		$this->add_section( 'general', '' );
		$this->add_field(
			'feed_links',
			__( 'フィードリンク (RSS)', 'ggsupports' ),
			function () {
				$args = array(
					'id'      => 'ggsupports_general_feed_links',
					'default' => 0,
				);
				Ggs_Helper::radiobox( $args );
			},
			'general'
		);

		$this->add_field(
			'wp_generator',
			__( 'ジェネレーターメタタグの出力 (バージョン情報の出力)', 'ggsupports' ),
			function () {
				$args = array(
					'id'      => 'ggsupports_general_wp_generator',
					'default' => 0,
				);
				Ggs_Helper::radiobox( $args );
			},
			'general'
		);

		$this->add_field(
			'wp_shortlink_wp_head',
			__( 'ショートリンクの出力', 'ggsupports' ),
			function () {
				$args = array(
					'id'      => 'ggsupports_general_wp_shortlink_wp_head',
					'default' => 0,
				);
				Ggs_Helper::radiobox( $args );
			},
			'general'
		);


		$this->add_field(
			'wpautop',
			__( 'ショートリンクの出力', 'ggsupports' ),
			function () {
				$args = array(
					'id'      => 'ggsupports_general_wpautop',
					'default' => 0,
					'desc'    => '',
				);
				Ggs_Helper::radiobox( $args );
			},
			'general'
		);
		$this->add_field(
			'wpautop',
			__( '自動整形の停止', 'ggsupports' ),
			function () {
				$args = array(
					'id'      => 'ggsupports_general_wpautop',
					'default' => 0,
					'desc'    => __( '記事を出力する際の自動整形を停止します。', 'ggsupports' ),
				);
				Ggs_Helper::radiobox( $args );
			},
			'general'
		);
		$this->add_field(
			'revision',
			__( 'リビジョンコントロールの停止', 'ggsupports' ),
			function () {
				$args = array(
					'id'      => 'ggsupports_general_revision',
					'default' => 0,
					'desc'    => __( '投稿、固定ページのリビジョン管理のを無効にすることができます。', 'ggsupports' ),
				);
				Ggs_Helper::radiobox( $args );
			},
			'general'
		);
		$this->add_field(
			'jquery',
			__( 'jQueryライブラリの読み込み', 'ggsupports' ),
			function () {
				$args = array(
					'id'      => 'ggsupports_general_jquery',
					'default' => 0,
					'desc'    => __( 'WordPressに内包されているjQueryライブラリを読み込みます。', 'ggsupports' ),
				);
				Ggs_Helper::radiobox( $args );
			},
			'general'
		);

		$this->add_field(
			'bootstrap',
			__( 'Bootstrap3フレームワークの読み込み', 'ggsupports' ),
			function () {
				$args = array(
					'id'      => __( 'ggsupports_general_bootstrap', 'ggsupports' ),
					'default' => 0,
				);
				Ggs_Helper::radiobox( $args );
			},
			'general',
			'Bootstrap フレームワークを自動的に読み込みます。'
		);

		$this->add_field(
			'xmlrpc',
			__( 'xmlrpcの停止', 'ggsupports' ),
			function () {
				$args = array(
					'id'      => 'ggsupports_general_xmlrpc',
					'default' => 0,
					'desc'    => __( 'セキュリティ対策としてxmlrpcを無効にします。', 'ggsupports' ),
				);
				Ggs_Helper::radiobox( $args );
			},
			'general'
		);

		$this->add_field(
			'author_archive',
			__( '著者アーカイブの無効', 'ggsupports' ),
			function () {
				$args = array(
					'id'      => 'ggsupports_general_author_archive',
					'default' => 0,
					'desc'    => __( 'セキュリティ対策として著者アーカイブを無効にします。', 'ggsupports' ),
				);
				Ggs_Helper::radiobox( $args );
			},
			'general'
		);

		$this->add_field(
			'disable_update',
			__( '自動更新の無効化', 'ggsupports' ),
			function () {
				$args = array(
					'id'      => 'ggsupports_general_disable_update',
					'default' => 0,
					'desc'    => __( 'WordPress本体、プラグインの更新を停止し非表示にします。', 'ggsupports' ),
				);
				Ggs_Helper::radiobox( $args );
			},
			'general'
		);
		$this->add_field(
			'show_current_template',
			__( '現在のテンプレート名を管理バーに表示', 'ggsupports' ),
			function () {
				$args = array(
					'id'      => 'ggsupports_general_show_current_template',
					'default' => 1,
					'desc'    => __( 'サイトフロント画面にて、現在表示されているテンプレート名を出力します。', 'ggsupports' ),
				);
				Ggs_Helper::radiobox( $args );
			},
			'general'
		);
		$this->add_field(
			'jetpack_dev_mode',
			__( 'Jetpackの開発者モードを有効化', 'ggsupports' ),
			function () {
				$args = array(
					'id'      => 'ggsupports_general_jetpack_dev_mode',
					'default' => 0,
					'desc'    => __( 'jetpackの開発者モードを有効化し、認証なしで複数の機能を有効化します。', 'ggsupports' ),
				);
				Ggs_Helper::radiobox( $args );
			},
			'general'
		);
		/**
		 * 2 ダッシュボードウィジェット
		 */
		$this->add_section( 'dashboard', function () {
			echo __( 'ダッシュボードウィジェットに表示するコンテンツを入力してください。', 'ggsupports' );
		} );

		$this->add_field(
			'dashboard_disp',
			__( 'ダッシュボードウィジェットの有効化', 'ggsupports' ),
			function () {
				$args = array(
					'id'      => 'ggsupports_dashboard_dashboard_disp',
					'default' => 0,
					'desc'    => '',
				);
				Ggs_Helper::radiobox( $args );
			},
			'dashboard'
		);

		$this->add_field(
			'contents',
			__( '', 'ggsupports' ),
			function () {
				_e( '<p>ダッシュボードに表示させるコンテンツを入力してください。</p>', 'ggsupports' );
				$contents           = ( get_option( 'ggsupports_options' ) ) ? get_option( 'ggsupports_options' ) : '';
				$dashboard_contents = ( isset( $contents['ggsupports_dashboard_contents'] ) ? $contents['ggsupports_dashboard_contents'] : '' );
				$editor_settings    = array(
					'wpautop'             => false,
					'media_buttons'       => true,
					'default_editor'      => '',
					'drag_drop_upload'    => true,
					'textarea_name'       => 'ggsupports_dashboard_contents',
					'textarea_rows'       => 50,
					'tabindex'            => '',
					'tabfocus_elements'   => ':prev,:next',
					'editor_css'          => '',
					'editor_class'        => '',
					'teeny'               => false,
					'dfw'                 => true,
					'_content_editor_dfw' => false,
					'tinymce'             => true,
					'quicktags'           => true
				);
				wp_editor( $dashboard_contents, 'ggsupports_options_ggsupports_dashboard_contents', $editor_settings );
			},
			'dashboard'
		);

		/**
		 * 3 管理メニューの設定
		 */
		$this->add_section( 'admin_menu', function () {
			echo '管理メニューの設定';
		} );

		$this->add_field(
			'admin_menu_user',
			__( 'アカウントの選択', 'ggsupports' ),
			function () {
				_e( '管理メニュー変更を適用させるアカウントを選択して下さい。<br />shiftキーを押しながら選択することで複数選択できます。', 'ggsupports' );
				echo '<br />';
				$selected = Ggs_Helper::get_ggs_options( 'ggsupports_admin_menu_user' );
				$this->dropdown_users( array(
					'name'     => 'ggsupports_admin_menu_user[]',
					'id'       => 'ggsupports_admin_menu_user',
					'selected' => $selected
				) ); ?>
			<?php
			},
			'admin_menu'
		);

		$this->add_field(
			'admin_menu',
			__( 'サイドメニュー一覧', 'ggsupports' ),
			function () {
				$checked_admin_menus = Ggs_Helper::get_ggs_options( 'ggsupports_admin_menu' );
				_e( '非表示にする管理メニューを選択をしてください。', 'ggsupports' );
				?>

				<div id="ggs_admin_menus"></div>
				<input type="hidden" id="ggsupports_admin_menu_hidden" value="<?php echo $checked_admin_menus; ?>" name="ggsupports_admin_menu"/>
			<?php
			},
			'admin_menu'
		);


	}

	/**
	 * Setting APIを使用したオプションページの作成
	 * @return void
	 */
	public function add_admin_menu() {

		add_menu_page(
			__( '制作サポート', 'ggsupports' ),
			__( '制作サポート', 'ggsupports' ),
			'manage_options',
			$this->option_page_slug,
			array(
				$this,
				'option_page'
			)
		);

		add_submenu_page(
			$this->option_page_slug,
			__( 'サイト設定', 'ggsupports' ),
			__( 'サイト設定', 'ggsupports' ),
			'manage_options',
			$this->option_page_slug,
			function(){

			}
		);

		add_submenu_page(
			$this->option_page_slug,
			__( 'DW設定', 'ggsupports' ),
			__( 'DW設定', 'ggsupports' ),
			'manage_options',
			$this->option_page_slug . '#ggs-dashboard-setting',
			function(){

			}
		);

		add_submenu_page(
			$this->option_page_slug,
			__( '管理メニュー設定', 'ggsupports' ),
			__( '管理メニュー設定', 'ggsupports' ),
			'manage_options',
			$this->option_page_slug . '#ggs-admin_menu-setting',
			function(){
			}
		);

	}

	/**
	 * オプションページのレンダリング
	 * @return void
	 */
	public function option_page() {
		$nonce = wp_create_nonce( __FILE__ );
		include "views/options.php";
	}

	/**
	 * ダッシュボード用のjsを埋め込み
	 *
	 * @param  string $hook 呼び出されるファイル名
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts( $hook ) {

		switch ( $hook ) {
			case 'index.php' :
				wp_enqueue_script( 'ggs_dashboard_widget', plugins_url( 'assets/js/dashboard.js', __FILE__ ), array( 'jquery' ), false );
				break;
			case 'toplevel_page_ggsupports_options_page' :
				wp_enqueue_script( 'ggs_admin_scripts', plugins_url( 'assets/js/scripts.js', __FILE__ ), array(
					'jquery',
					'jquery-ui-tabs',
					'jquery-ui-button',
					'jquery-ui-accordion',
					'jquery-ui-droppable',
					'jquery-ui-draggable',
				), false );
				wp_enqueue_style( 'jquery-ui-smoothness', plugins_url( 'assets/css/jquery-ui.css', __FILE__ ), false, null );
				wp_localize_script( 'ggs_admin_scripts', 'GGSSETTINGS', array(
					'action'    => 'update_ggsupports_option',
					'_wp_nonce' => wp_create_nonce( __FILE__ )
				) );
				break;
		}

	}

	/**
	 * SettingAPI add_settings_section のラッパー
	 *
	 * @param $name
	 * @param $title
	 */
	public function add_section( $name, $title ) {
		add_settings_section(
			$this->prefix . $name . '_section',
			$title,
			'',
			$this->option_page_slug
		);
	}

	/**
	 * SettingAPI add_setting_field のワッパー
	 *
	 * @param $name
	 * @param $title
	 * @param $callback
	 * @param $section
	 *
	 * @param string $desc
	 */
	public function add_field( $name, $title, $callback, $section, $desc = '' ) {
		if ( $title ) {
			$title = '<h3><span class="dashicons dashicons-arrow-right-alt2"></span> ' . $title . '</h3>';
		}
		add_settings_field(
			$this->prefix . $section . '_' . $name,
			$title,
			$callback,
			$this->option_page_slug,
			$this->prefix . $section . '_section'
		);
	}

	public function dropdown_users( $args = '' ) {
		$defaults = array(
			'show_option_all'         => '',
			'show_option_none'        => '',
			'hide_if_only_one_author' => '',
			'orderby'                 => 'display_name',
			'order'                   => 'ASC',
			'include'                 => '',
			'exclude'                 => '',
			'multi'                   => 0,
			'show'                    => 'display_name',
			'echo'                    => 1,
			'selected'                => 0,
			'name'                    => 'user',
			'class'                   => '',
			'id'                      => '',
			'blog_id'                 => $GLOBALS['blog_id'],
			'who'                     => '',
			'include_selected'        => false,
			'option_none_value'       => - 1
		);

		$defaults['selected'] = is_author() ? get_query_var( 'author' ) : 0;

		$r                 = wp_parse_args( $args, $defaults );
		$show              = $r['show'];
		$show_option_all   = $r['show_option_all'];
		$show_option_none  = $r['show_option_none'];
		$option_none_value = $r['option_none_value'];

		$query_args           = wp_array_slice_assoc( $r, array(
			'blog_id',
			'include',
			'exclude',
			'orderby',
			'order',
			'who'
		) );
		$query_args['fields'] = array( 'ID', 'user_login', $show );
		$users                = get_users( $query_args );

		$output = '';
		if ( ! empty( $users ) && ( empty( $r['hide_if_only_one_author'] ) || count( $users ) > 1 ) ) {
			$name = esc_attr( $r['name'] );
			if ( $r['multi'] && ! $r['id'] ) {
				$id = '';
			} else {
				$id = $r['id'] ? " id='" . esc_attr( $r['id'] ) . "'" : " id='$name'";
			}
			$output = "<select name='{$name}'{$id} class='" . $r['class'] . "' multiple>\n";

			if ( $show_option_all ) {
				$output .= "\t<option value='0'>$show_option_all</option>\n";
			}

			if ( $show_option_none ) {
				$_selected = selected( $option_none_value, $r['selected'], false );
				$output .= "\t<option value='" . esc_attr( $option_none_value ) . "'$_selected>$show_option_none</option>\n";
			}

			$found_selected = false;
			$i              = 0;
			foreach ( (array) $users as $user ) {
				$user->ID = (int) $user->ID;
				if ( is_array( $r['selected'] )
				     && in_array( $user->ID, $r['selected'] )
				) {
					$_selected = ' selected="selected"';
				} else {
					$_selected = "";
				}

				if ( $_selected ) {
					$found_selected = true;
				}
				$display = ! empty( $user->$show ) ? $user->$show : '(' . $user->user_login . ')';
				$output .= "\t<option value='$user->ID'$_selected>" . esc_html( $display ) . "</option>\n";
				$i ++;
			}

			if ( $r['include_selected'] && ! $found_selected && ( $r['selected'] > 0 ) ) {
				$user      = get_userdata( $r['selected'] );
				$_selected = selected( $user->ID, $r['selected'], false );
				$display   = ! empty( $user->$show ) ? $user->$show : '(' . $user->user_login . ')';
				$output .= "\t<option value='$user->ID'$_selected>" . esc_html( $display ) . "</option>\n";
			}

			$output .= "</select>";
		}

		$html = $output;

		if ( $r['echo'] ) {
			echo $html;
		}

		return $html;
	}

	/**
	 * Ajax で受けた情報を保存
	 * @return void
	 */
	public function update_ggsupports_option() {
		if ( ! wp_verify_nonce( $_REQUEST['_wp_nonce'], __FILE__ ) ) {
			echo false;
			exit();
		}
		$form_str = urldecode( $_REQUEST['form'] );

		parse_str( $form_str, $form_array );

		if ( $form_array ) {
			$settings = array_map( array( $this, 'sanitizes_fields' ), $form_array );
			echo update_option( 'ggsupports_options', $settings );
		}
		exit();
	}

	/**
	 * 無害化
	 *
	 * @param $fields
	 *
	 * @return array|string
	 */
	public function sanitizes_fields( $fields ) {
		if ( is_array( $fields ) ) {
			return array_map( 'sanitize_text_field', $fields );
		}

		return sanitize_text_field( $fields );
	}


}