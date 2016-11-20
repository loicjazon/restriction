<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://loicjazon.com/
 * @since      1.0.0
 * @package    Lj_Restriction
 * @subpackage Lj_Restriction/public
 */

use Carbon\Carbon;

/**
 * The public-facing functionality of the plugin.
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Lj_Restriction
 * @subpackage Lj_Restriction/public
 * @author     Loïc Jazon <contact@loicjazon.com>
 */
class Lj_Restriction_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $plugin_name The name of the plugin.
	 * @param      string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 * An instance of this class should be passed to the run() function
		 * defined in Lj_Restriction_Loader as all of the hooks are defined
		 * in that particular class.
		 * The Lj_Restriction_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/lj-restriction-public.css', [], $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 * An instance of this class should be passed to the run() function
		 * defined in Lj_Restriction_Loader as all of the hooks are defined
		 * in that particular class.
		 * The Lj_Restriction_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/lj-restriction-public.js', [ 'jquery' ], $this->version, false );

	}


	public function add_shortcode() {

		wpcf7_add_shortcode( 'deadline', [ $this, 'handler' ], true );

	}

	public function handler( $tag ) {

		return ( ! is_array( $tag ) ) ? '' : $tag['content'];

	}

	public function validate( $result, $tags ) {
		// retrieve the posted email
		$form = WPCF7_Submission::get_instance();

		// si pas de deadline, on reste dans un formulaire classique
		$deadlines = $this->has_deadline_tag( $tags );

		if ( ! $deadlines or empty( $form->get_posted_data( 'date-demande' ) ) ) {
			return $result;
		}

		// date demandé par l'utilisateur
		$date_event = Carbon::createFromFormat( 'Y-m-d', $form->get_posted_data( 'date-demande' ) );

		// nombre de jours autorisé entre aujourd'hui et la demande
		$delta = $deadlines[0]['options'][0];

		if ( ! $this->validate_deadline( $date_event, $delta ) ) {
			$result->invalidate( 'date-demande', "Votre demande doit être faite {$delta} jours à l'avance." );
		}

		return $result;
	}

	private function has_deadline_tag( $tags ) {
		return array_filter( $tags, function ( $tag ) {
			return $tag['type'] == 'deadline';
		} );
	}

	private function validate_deadline( $date, $delta ) {
		return $delta <= $date->diffInDays( Carbon::today() );
	}

}
