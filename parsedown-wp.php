<?php
/*
	Plugin Name: Parsedown for WordPress
	Plugin URI: https://wordpress.org/plugins/parsedown-wp/
	Description: A drop-in Markdown plugin using Parsedown Extra.
	Version: 0.3
	Author: Robin Adrianse
	Author URI: http://robinadr.com/

	This plugin uses the Parsedown and Parsedown Extra libraries, which are 
	distributed under the MIT license by Emanuil Rusev. A full copy of the 
	license is included in Parsedown/license.txt.
*/

/*
This plugin is intended to be a 100% compatible drop-in replacement for PHP 
Markdown Extra by Michel Fortin. Thus, some portions, namely filter order and 
helper functions, are directly copied from his plugin. These portions are noted 
as such below. The following copyright notice applies to those portions:

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

require_once __DIR__ . '/Parsedown/Parsedown.php';
require_once __DIR__ . '/Parsedown/ParsedownExtra.php';

class Parsedown_WP_Parser extends ParsedownExtra
{
	protected function inlineSpecialCharacter( $text )
	{
		// Do nothing. WordPress handles HTML special characters
		// and curly quotes by default.
	}
}

class Parsedown_WP
{
	private $parser;

	private $hidden_tags;
	private $placeholders;

	public function __construct()
	{
		add_action( 'init', array( $this, 'init' ) );
	}

	public function init()
	{
		$this->parser = new Parsedown_WP_Parser();

		// These filters are taken directly from PHP Markdown Extra by Michel 
		// Fortin to ensure it's a 100% drop-in solution.

		// Post filters
		remove_filter( 'the_content',		'wpautop' );
		remove_filter( 'the_content_rss',	'wpautop' );
		remove_filter( 'the_excerpt',		'wpautop' );

		add_filter( 'the_content', 		array( $this, 'markdown' ), 6 );
		add_filter( 'the_content_rss', 	array( $this, 'markdown' ), 6 );
		add_filter( 'get_the_excerpt', 	array( $this, 'markdown' ), 6 );
		add_filter( 'get_the_excerpt', 	'trim', 7 );
		add_filter( 'the_excerpt', 		array( $this, 'add_p' )  );
		add_filter( 'the_excerpt_rss', 	array( $this, 'strip_p' )  );

		remove_filter( 'content_save_pre', 	'balanceTags', 50 );
		remove_filter( 'excerpt_save_pre', 	'balanceTags', 50 );

		add_filter( 'the_content', 		'balanceTags', 50 );
		add_filter( 'get_the_excerpt', 	'balanceTags', 9 );

		// Comment filters
		remove_filter( 'comment_text', 	'wpautop', 30 );
		remove_filter( 'comment_text', 	'make_clickable' );

		add_filter( 'pre_comment_content', 	array( $this, 'markdown' ), 6 );
		add_filter( 'pre_comment_content', 	array( $this, 'hide_tags' ), 8 );
		add_filter( 'pre_comment_content', 	array( $this, 'show_tags' ), 12 );
		add_filter( 'get_comment_text', 	array( $this, 'markdown' ), 6 );
		add_filter( 'get_comment_excerpt', 	array( $this, 'markdown' ), 6 );
		add_filter( 'get_comment_excerpt', 	array( $this, 'strip_p' ), 7 );

		// Taken from PHP Markdown Extra by Michel Fortin
		$this->hidden_tags = array(
			'<p>', '</p>', '<pre>', '</pre>', '<ol>', '</ol>', 
			'<ul>', '</ul>', '<li>', '</li>'
		);
		$this->hidden_tags = apply_filters( 'pdwp_hidden_tags', $this->hidden_tags );

		$this->placeholders = explode(' ', str_rot13(
			'pEj07ZbbBZ U1kqgh4w4p pre2zmeN6K QTi31t9pre ol0MP1jzJR ' .
			'ML5IjmbRol ulANi1NsGY J7zRLJqPul liA8ctl16T K9nhooUHli'
		) );
		$this->placeholders = apply_filters( 'pdwp_placeholders', $this->placeholders );
	}

	public function markdown( $text )
	{
		return apply_filters( 'pdwp_markdown', $this->parser->text( $text ) );
	}

	// Taken from PHP Markdown Extra by Michel Fortin
	public function add_p( $text )
	{
		$regex = apply_filters( 'pdwp_add_p_regex', '{^$|^<(p|ul|ol|dl|pre|blockquote)>}i' );

		if ( !preg_match( $regex, $text ) ) {
			$text = '<p>' . $text . '</p>';
			$text = preg_replace( '{\n{2,}}', "</p>\n\n<p>", $text );
		}

		return apply_filters( 'pdwp_add_p', $text );
	}

	// Taken from PHP Markdown Extra by Michel Fortin
	public function strip_p( $text )
	{
		return apply_filters( 'pdwp_strip_p', preg_replace( '{</?p>}i', '', $text ) );
	}

	// Taken from PHP Markdown Extra by Michel Fortin
	public function hide_tags( $text )
	{
		return str_replace( $this->hidden_tags, $this->placeholders, $text );
	}

	// Taken from PHP Markdown Extra by Michel Fortin
	public function show_tags( $text )
	{
		return str_replace( $this->placeholders, $this->hidden_tags, $text );
	}
}

$parsedown_wp = new Parsedown_WP();