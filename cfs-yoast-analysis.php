<?php
/**
 * Plugin Name: CFS Yoast Analysis
 * Version: 1.0.0
 * Plugin URI: https://github.com/jchristopher/cfs-yoast-analysis/
 * Description: Integrate Custom Field Suite content in Yoast WordPress SEO analysis
 * Author: Jonathan Christopher
 * Author URI: https://mondaybynoon.com/
 * Text Domain: cfs-yoast-analysis
 * License: GPL v3
 */

/**
 * CFS Yoast Analysis
 * Copyright (C) 2017, Jonathan Christopher - jonathan@mondaybynoon.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'CFS_YOAST_ANALYSIS_FILE' ) ) {
	define( 'CFS_YOAST_ANALYSIS_FILE', __FILE__ );
}

class CFS_Yoast_Analysis {

	const VERSION = '1.0.0';

	function __construct() {
		add_filter( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 999 );
		add_filter( 'wpseo_post_content_for_recalculation', array( $this, 'add_recalculation_data_to_post_content' ) );
	}

	function enqueue_scripts() {

		// If the Asset Manager exists then we need to use a different prefix.
		$script_prefix = ( class_exists( 'WPSEO_Admin_Asset_Manager' ) ? 'yoast-seo' : 'wp-seo' );

		if ( wp_script_is( $script_prefix . '-post-scraper', 'enqueued' ) ) {
			wp_enqueue_script(
				'cfs-yoast-analysis-post',
				plugins_url( '/javascripts/cfs-yoast-analysis.js', CFS_YOAST_ANALYSIS_FILE ),
				array( 'jquery', $script_prefix . '-post-scraper' ),
				self::VERSION
			);
		}
	}

	/**
	 * Add CFS data to post content
	 *
	 * @param string  $content String of the content to add data to.
	 * @param WP_Post $post    Item the content belongs to.
	 *
	 * @return string Content with added CFS data.
	 */
	public function add_recalculation_data_to_post_content( $content, $post ) {
		// ACF defines this function.
		if ( ! function_exists( 'CFS' ) ) {
			return '';
		}

		if ( false === ( $post instanceof WP_Post ) ) {
			return '';
		}

		$post_cfs_fields = CFS()->get( false, $post->ID );
		$cfs_content = $this->get_field_data( $post_cfs_fields );

		return trim( $content . ' ' . $cfs_content );
	}

	/**
	 * Filter what CFS Fields not to score
	 *
	 * @param array $fields CFS Fields to parse.
	 *
	 * @return string Content of all CFS fields combined.
	 */
	private function get_field_data( $fields ) {
		$output = '';

		if ( ! is_array( $fields ) ) {
			return $output;
		}

		foreach ( $fields as $key => $field ) {
			switch ( gettype( $field ) ) {
				case 'string':
					$output .= ' ' . $field;
					break;

				case 'array':
					$output .= ' ' . $this->get_field_data( $field );
					break;
			}
		}

		return trim( $output );
	}
}

new CFS_Yoast_Analysis();
