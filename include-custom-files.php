<?php
/*
Plugin Name: Include Custom Files
Plugin URI: http://wpprogrammer.com/include-custom-files/
Description: Enables embedding of multiple stylesheets and javascript files on a per-post basis.
Version: 1.0
Author: Utkarsh Kukreti
Author URI: http://utkar.sh

== Release Notes ==
2010-05-12 - v1.0 - First version.

Based on the idea and code by Chris Coyier (http://digwp.com/2010/05/specify-unique-css-file-per-post/)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
Online: http://www.gnu.org/licenses/gpl.txt
*/

IncludeCustomFiles::init();

abstract class IncludeCustomFiles
{	
	static $meta_key = '_custom_', $css_folder = 'css', $js_folder = 'js';
	
	static function init()
	{		
		# Add meta box
		add_action('admin_menu', array('IncludeCustomFiles', 'admin_menu'));
		
		# Save
		add_action('save_post', array('IncludeCustomFiles', 'save_post'));
		
		# Enqueue styles/scripts
		add_action('wp_print_scripts', array('IncludeCustomFiles', 'wp_print_scripts'));
		add_action('wp_print_styles', array('IncludeCustomFiles', 'wp_print_styles'));
	}
	
	# Add meta box
	static function admin_menu()
	{
		add_meta_box('custom_files_css', 'Custom Files', array('IncludeCustomFiles', 'meta_box'), 'post', 'normal', 'high');
		add_meta_box('custom_files_css', 'Custom Files', array('IncludeCustomFiles', 'meta_box'), 'page', 'normal', 'high');
	}
	
	static function meta_box()
	{
		global $post;
		echo '<input type="hidden" name="custom_files_nonce" id="custom_files_noncename" value="'.wp_create_nonce('custom-files').'" />';
		echo '<p>';
		echo '<label for="custom_css">CSS Files</label>';
		echo '<input type="text" name="custom_css" id="custom_css" style="width:100%;" value="'.get_post_meta($post->ID, IncludeCustomFiles::$meta_key . 'css', true).'" />';
		echo '</p>';
		echo '<p>';
		echo '<label for="custom_js">JS Files</label>';
		echo '<input type="text" name="custom_js" id="custom_js" style="width:100%;" value="'.get_post_meta($post->ID, IncludeCustomFiles::$meta_key . 'js', true).'" />';
		echo '</p>';
		echo '<p>Separate multiple file names with a <code>, </code></p>';
		echo '<p>Relative file names will be prefixed by <code>' . get_bloginfo('template_url') . '/' . IncludeCustomFiles::$css_folder . '/</code> and <code>' . get_bloginfo('template_url') . '/' . IncludeCustomFiles::$js_folder . '/</code> respectively.</p>';
		
	}

	# Save
	static function save_post($post_id)
	{
		if (!wp_verify_nonce($_POST['custom_files_nonce'], 'custom-files')) return $post_id;
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post_id;
		$custom_css = $_POST['custom_css'];
		update_post_meta($post_id, IncludeCustomFiles::$meta_key . 'css', $custom_css);
		$custom_js = $_POST['custom_js'];
		update_post_meta($post_id, IncludeCustomFiles::$meta_key . 'js', $custom_js);
	}
	
	# Enqueue styles
	static function wp_print_styles()
	{
		if(is_single() || is_page()) # Load only on frontend
		{
			global $wp_query;
			$post_id = $wp_query->post->ID;

			$custom_css = get_post_meta($post_id, IncludeCustomFiles::$meta_key . 'css', true);
			$custom_css = explode(',', $custom_css);
			
			foreach($custom_css as $cc)
			{
				$cc = trim($cc);
				if(!$cc) continue;
				if(strpos($cc, 'http://') === 0 || strpos($cc, 'https://') === 0 || strpos($cc, '/') === 0)
					wp_enqueue_style(basename($cc), $cc);
				else
					wp_enqueue_style(basename($cc), get_bloginfo('template_url') . '/' . IncludeCustomFiles::$css_folder . '/' . $cc);
			}
		}
	}
	
	# Enqueue scripts
	static function wp_print_scripts()
	{
		if(is_single() || is_page()) # Load only on frontend
		{
			global $wp_query;
			$post_id = $wp_query->post->ID;

			$custom_js = get_post_meta($post_id, IncludeCustomFiles::$meta_key . 'js', true);
			$custom_js = explode(',', $custom_js);

			foreach($custom_js as $cj)
			{
				$cj = trim($cj);
				if(!$cj) continue;
				if(strpos($cj, 'http://') === 0 || strpos($cj, 'https://') === 0 || strpos($cj, '/') === 0)
					wp_enqueue_script(basename($cj), $cj);
				else
					wp_enqueue_script(basename($cj), get_bloginfo('template_url') . '/' . IncludeCustomFiles::$js_folder . '/' . $cj);
			}
		}
	}

}