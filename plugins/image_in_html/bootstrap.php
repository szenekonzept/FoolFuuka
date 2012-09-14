<?php

if (!defined('DOCROOT'))
	exit('No direct script access allowed');

\Autoloader::add_classes(array(
	'Foolfuuka\\Plugins\\Image_In_Html\\Controller_Plugin_Fu_Image_In_Html_Chan' 
		=> __DIR__.'/classes/controller/chan.php'
));

\Router::add('(?!(admin|_))(\w+)/image_html/(:any)', 'plugin/fu/image_in_html/chan/$2/image_html/$3', true);


\Plugins::register_hook('foolfuuka\\model\\media.get_link.call.replace', function($element, $thumbnail = false, $direct = false) {

	if ($direct === true || $thumbnail === true)
	{
		return array('return' => null);
	}

	// this function must NOT run for the radix_full_image function
	if (\Radix::get_by_shortname(\Uri::segment(1)) && \Uri::segment(2) === 'full_image')
	{
		return array('return' => null);
	}
	
	try
	{
		$element->p_get_link($thumbnail);
	}
	catch (\Foolfuuka\Model\MediaNotFoundException $e)
	{
		return array('return' => null);
	}
	
	return array('return' => \Uri::create(array($element->board->shortname, 'image_html')).$element->media);
}, 4);
