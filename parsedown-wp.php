<?php
/*
	Plugin Name: Parsedown for WordPress
	Plugin URI: 
	Description: A drop-in Markdown plugin using Parsedown Extra.
	Author: Robin Adrianse
	Author URI: http://robinadr.com/

	This plugin uses the Parsedown and Parsedown Extra libraries, which are distributed 
	under the MIT license by Emanuil Rusev. A full copy of the license is included in 
	Parsedown/license.txt.
*/

/*

This plugin is intended to be a 100% compatible drop-in replacement for PHP Markdown 
Extra by Michel Fortin. Thus, some portions, namely filter order and helper functions, 
are directly copied from his plugin. These portions are noted as such below. The 
following copyright notice applies to those portions:

PHP Markdown & Extra
Copyright (c) 2004-2013 Michel Fortin
<http://michelf.ca/>  
All rights reserved.

Based on Markdown  
Copyright (c) 2003-2006 John Gruber   
<http://daringfireball.net/>   
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are
met:

*	Redistributions of source code must retain the above copyright notice,
	this list of conditions and the following disclaimer.

*	Redistributions in binary form must reproduce the above copyright
	notice, this list of conditions and the following disclaimer in the
	documentation and/or other materials provided with the distribution.

*	Neither the name "Markdown" nor the names of its contributors may
	be used to endorse or promote products derived from this software
	without specific prior written permission.

This software is provided by the copyright holders and contributors "as
is" and any express or implied warranties, including, but not limited
to, the implied warranties of merchantability and fitness for a
particular purpose are disclaimed. In no event shall the copyright owner
or contributors be liable for any direct, indirect, incidental, special,
exemplary, or consequential damages (including, but not limited to,
procurement of substitute goods or services; loss of use, data, or
profits; or business interruption) however caused and on any theory of
liability, whether in contract, strict liability, or tort (including
negligence or otherwise) arising in any way out of the use of this
software, even if advised of the possibility of such damage.

*/

require_once __FILE__ . '/Parsedown/Parsedown.php';
require_once __FILE__ . '/Parsedown/ParsedownExtra.php';

class Parsedown_WP extends ParsedownExtra
{
	static $parser;

	static $hidden_tags;
	static $placeholders;

	public static function init()
	{
		self::$parser = new ParsedownExtra();

		// These filters are taken directly from PHP Markdown Extra by Michel Fortin
		// to ensure it's a 100% drop-in solution.

		// Post filters
		remove_filter( 'the_content',		'wpautop' );
		remove_filter( 'the_content_rss',	'wpautop' );
		remove_filter( 'the_excerpt',		'wpautop' );

		add_filter( 'the_content', 			array( __CLASS__, 'markdown' ), 6 );
		add_filter( 'the_content_rss', 		array( __CLASS__, 'markdown' ), 6 );
		add_filter( 'get_the_excerpt', 		array( __CLASS__, 'markdown' ), 6 );
		add_filter( 'get_the_excerpt', 		'trim', 7 );
		add_filter( 'the_excerpt', 			array( __CLASS__, 'mdwp_add_p' )  );
		add_filter( 'the_excerpt_rss', 		array( __CLASS__, 'mdwp_strip_p' )  );

		remove_filter( 'content_save_pre', 	'balanceTags', 50 );
		remove_filter( 'excerpt_save_pre', 	'balanceTags', 50 );
		add_filter( 'the_content', 			'balanceTags', 50 );
		add_filter( 'get_the_excerpt', 		'balanceTags', 9 );

		// Comment filters
		remove_filter( 'comment_text', 		'wpautop', 30 );
		remove_filter( 'comment_text', 		'make_clickable' );

		add_filter( 'pre_comment_content', 	array( __CLASS__, 'markdown' ), 6 );
		add_filter( 'pre_comment_content', 	array( __CLASS__, 'mdwp_hide_tags' ), 8 );
		add_filter( 'pre_comment_content', 	array( __CLASS__, 'mdwp_show_tags' ), 12 );
		add_filter( 'get_comment_text', 	array( __CLASS__, 'markdown' ), 6 );
		add_filter( 'get_comment_excerpt', 	array( __CLASS__, 'markdown' ), 6 );
		add_filter( 'get_comment_excerpt', 	array( __CLASS__, 'mdwp_strip_p' ), 7 );

		// Taken from PHP Markdown Extra by Michel Fortin
		self::$hidden_tags = array( '<p>', '</p>', '<pre>', '</pre>', '<ol>', '</ol>', '<ul>', '</ul>', '<li>', '</li>' );
		self::$placeholders = explode(' ', str_rot13(
			'pEj07ZbbBZ U1kqgh4w4p pre2zmeN6K QTi31t9pre ol0MP1jzJR ' .
			'ML5IjmbRol ulANi1NsGY J7zRLJqPul liA8ctl16T K9nhooUHli'
		) );
	}

	public static function markdown( $text )
	{
		return self::$parser->text( $text );
	}

	// Taken directly from PHP Markdown Extra by Michel Fortin
	public static function mdwp_add_p( $text )
	{
		if (!preg_match( '{^$|^<(p|ul|ol|dl|pre|blockquote)>}i', $text ) ) {
			$text = '<p>' . $text . '</p>';
			$text = preg_replace( '{\n{2,}}', "</p>\n\n<p>", $text );
		}
		return $text;
	}

	// Taken directly from PHP Markdown Extra by Michel Fortin
	public static function mdwp_strip_p( $text )
	{
		return preg_replace( '{</?p>}i', '', $t );
	}

	// Taken from PHP Markdown Extra by Michel Fortin
	public static function mdwp_hide_tags( $text )
	{
		return str_replace( self::$hidden_tags, self::$placeholders, $text );
	}

	// Taken from PHP Markdown Extra by Michel Fortin
	public static function mdwp_show_tags( $text )
	{
		return str_replace( self::$placeholders, self::$hidden_tags, $text );
	}
}

add_action( 'init', array( 'Parsedown_WP', 'init' ) );