<?php
/**
 * Add theme dashboard page
 *
 * @package Greentech Lite
 */

/**
 * Dashboard class.
 */
class Greentech_Lite_Dashboard {
	/**
	 * Store the theme data.
	 *
	 * @var WP_Theme Theme data.
	 */
	private $theme;

	/**
	 * Theme slug.
	 *
	 * @var string Theme slug.
	 */
	private $slug;

	/**
	 * Pro Version.
	 *
	 * @var string Pro version slug.
	 */
	private $pro_slug;

	/**
	 * Pro Version.
	 *
	 * @var string Pro version name.
	 */
	private $pro_name;

	/**
	 * UTM link.
	 *
	 * @var string UTM link.
	 */
	private $utm;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->theme    = wp_get_theme();
		$this->slug     = $this->theme->template;
		$this->pro_name = str_replace( ' Lite', '', $this->theme->name );
		$this->pro_slug = str_replace( '-lite', '', $this->slug );
		$this->lite_slug = $this->slug . '-lite';
		$this->utm      = '?utm_source=WordPress&utm_medium=link&utm_campaign=' . $this->slug;

		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_notices', array( $this, 'notice' ) );
		add_filter( 'wpforms_shareasale_id', array( $this, 'wpforms_shareasale_id' ) );
	}

	/**
	 * Add theme dashboard page.
	 */
	public function add_menu() {
		$page = add_theme_page(
			$this->theme->name,
			$this->theme->name,
			'edit_theme_options',
			$this->slug,
			array( $this, 'render' )
		);
		add_action( "admin_print_styles-$page", array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Show dashboard page.
	 */
	public function render() {
		add_action( 'admin_footer_text', array( $this, 'footer_text' ) );
		?>
		<div class="wrap">
			<div id="poststuff">
				<div id="post-body" class="columns-2">
					<div id="post-body-content">
						<div class="about-wrap">
							<?php include get_template_directory() . '/inc/dashboard/sections/welcome.php'; ?>
							<?php include get_template_directory() . '/inc/dashboard/sections/tabs.php'; ?>
							<?php include get_template_directory() . '/inc/dashboard/sections/getting-started.php'; ?>
							<?php include get_template_directory() . '/inc/dashboard/sections/actions.php'; ?>
							<?php include get_template_directory() . '/inc/dashboard/sections/pro.php'; ?>
						</div>
						<div id="postbox-container-1" class="postbox-container">
							<?php include get_template_directory() . '/inc/dashboard/sections/upgrade.php'; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Enqueue scripts for dashboard page.
	 */
	public function enqueue_scripts() {
		wp_enqueue_style( 'slick', get_template_directory_uri() . '/inc/dashboard/css/slick.css' );
		wp_enqueue_style( "{$this->slug}-dashboard-style", get_template_directory_uri() . '/inc/dashboard/css/dashboard-style.css' );
		wp_enqueue_script( 'slick', get_template_directory_uri() . '/inc/dashboard/js/slick.js', array( 'jquery' ), '1.8.0', true );
		wp_enqueue_script( "{$this->slug}-dashboard-script", get_template_directory_uri() . '/inc/dashboard/js/script.js', array( 'slick' ), '', true );
	}

	/**
	 *
	 * Change footer text in admin
	 */
	public function footer_text() {
		// Translators: theme name and theme slug.
		echo wp_kses_post( sprintf( __( 'Please rate <strong>%1$s</strong> <a href="https://wordpress.org/support/theme/%2$s/reviews/?filter=5" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a> on <a href="https://wordpress.org/support/theme/%2$s/reviews/?filter=5" target="_blank">WordPress.org</a> to help us spread the word. Thank you from GretaThemes!', 'greentech-lite' ), $this->pro_name, $this->lite_slug ) );
	}

	/**
	 * Recommended Plugin Action.
	 */
	public function recommended_plugins_action() {
		$plugins        = greentech_lite_required_plugins();
		$plugins_number = count( $plugins );
		$installer      = TGM_Plugin_Activation::get_instance();
		$action         = array();

		if ( $plugins_number > 1 ) {
			$action['title'] = esc_html__( 'Install The Recommended Plugins', 'greentech-lite' );
			/* translators: theme name. */
			$action['body']  = sprintf( esc_html__( '%s needs some plugins to working properly. Please install and activate our required plugins.', 'greentech-lite' ), $this->theme->name );
			$action['button_text'] = esc_html__( 'Install Plugins', 'greentech-lite' );
		} else {
			$plugin_name = $plugins[0]['name'];
			/* translators: theme name. */
			$action['body']        = sprintf( __( '%1$s needs %2$s to working properly. Please install and activate it.', 'greentech-lite' ), $this->theme->name, $plugin_name );
			/* translators: plugin name. */
			$action['button_text'] = sprintf( esc_html__( 'Install %s', 'greentech-lite' ), $plugin_name );
			$action['title']       = $action['button_text'];

		}

		if ( $installer->is_tgmpa_complete() ) {
			if ( $plugins_number > 1 ) {
				$action['body'] = '<strong>' . esc_html__( 'You have installed and active all required plugins', 'greentech-lite' ) . '</strong>';
			} else {
				/* translators: plugin name. */
				$action['body'] = sprintf( __( '<strong>%s has been installed and activated</strong>', 'greentech-lite' ), $plugin_name );
			}
			$action['button_text'] = '';
		}
		return $action;
	}

	/**
	 * Check if Jetpack is recommended
	 */
	public function jetpack_is_recommended() {
		$plugins = greentech_lite_required_plugins();
		foreach ( $plugins as $plugin ) {
			if ( 'jetpack' === $plugin['slug'] ) {
				return true;
			}
		}
	}

	/**
	 * Set the WPForms ShareASale ID.
	 *
	 * @param string $shareasale_id The the default ShareASale ID.
	 *
	 * @return string $shareasale_id
	 */
	public function wpforms_shareasale_id( $shareasale_id ) {
		$id = '424629';

		if ( ! empty( $shareasale_id ) && $shareasale_id == $id ) {
			return $shareasale_id;
		}

		update_option( 'wpforms_shareasale_id', $id );

		return $id;
	}

		/**
	 * Add a notice after theme activation.
	 */
	public function notice() {
		global $pagenow;
		if ( is_admin() && isset( $_GET['activated'] ) && 'themes.php' === $pagenow ) {
			?>
			<div class="updated notice notice-success is-dismissible">
				<p>
					<?php
					// Translators: theme name and welcome page.
					echo wp_kses_post( sprintf( __( 'Welcome! Thank you for choosing %1$s. To get started, visit our <a href="%2$s">welcome page</a>.', 'greentech-lite' ), $this->theme->name, esc_url( admin_url( 'themes.php?page=' . $this->slug ) ) ) );
					?>
				</p>
				<p>
					<a class="button" href="<?php echo esc_url( admin_url( 'themes.php?page=' . $this->slug ) ); ?>">
						<?php
						// Translators: theme name.
						echo esc_html( sprintf( __( 'Get started with %s', 'greentech-lite' ), $this->theme->name ) );
						?>
					</a>
				</p>
			</div>
			<?php
		}
	}
}
