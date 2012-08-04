<?php

$urlmap = array();

$urlmap['homepage'] = array (
	'sdescription'	=> 'hello world home page',
	'capsule'		=> 'home',
	'urlschema' 	=> '/',
	'params'			=>	'');
	
$urlmap['guide'] = array (
	'sdescription'	=> 'single page with standard headers and footer',
	'capsule'		=> 'staticpage',
	'urlschema' 	=> '/guide/[name]',
	'params'			=>	'');

$urlmap['printable'] = array (
	'sdescription'	=> 'single page with no headers',
	'capsule'		=> 'staticcontent',
	'urlschema' 	=> '/printable/[name]',
	'params'			=>	'');

	