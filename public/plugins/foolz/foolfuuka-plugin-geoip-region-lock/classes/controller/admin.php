<?php

namespace Foolz\Foolframe\Controller\Admin\Plugins\FU;

class GeoipRegionLock extends \Foolz\Foolframe\Controller\Admin
{

	public function before()
	{
		if ( ! \Auth::has_access('maccess.admin'))
		{
			\Response::redirect('admin');
		}

		parent::before();

		$this->_views['controller_title'] = __('GeoIP Region Lock');
	}

	public function action_manage()
	{
		$this->_views['method_title'] = 'Manage';

		$form = [];

		$form['open'] = [
			'type' => 'open'
		];

		$form['paragraph'] = [
			'type' => 'paragraph',
			'help' => __('You can add board-specific locks by browsing the board preferences.')
		];

		$form['foolfuuka.plugins.geoip_region_lock.allow_comment'] = [
			'label' => _('Countries allowed to post'),
			'type' => 'textarea',
			'preferences' => true,
			'validation' => 'trim',
			'class' => 'span6',
			'style' => 'height:60px',
			'help' => __('Comma separated list of GeoIP 2-letter nation codes.') . ' ' . __('If you allow a nation, all other nations won\'t be able to comment.'),
		];

		$form['foolfuuka.plugins.geoip_region_lock.disallow_comment'] = [
			'label' => _('Countries disallowed to post'),
			'type' => 'textarea',
			'preferences' => true,
			'validation' => 'trim',
			'class' => 'span6',
			'style' => 'height:60px',
			'help' => __('Comma separated list of GeoIP 2-letter nation codes.') . ' ' . __('Disallowed nations won\'t be able to comment.'),
		];

		$form['foolfuuka.plugins.geoip_region_lock.allow_view'] = [
			'label' => _('Countries allowed to view the site'),
			'type' => 'textarea',
			'preferences' => true,
			'validation' => 'trim',
			'class' => 'span6',
			'style' => 'height:60px',
			'help' => __('Comma separated list of GeoIP 2-letter nation codes.') . ' ' . __('If you allow a nation, all other nations won\'t be able to reach the interface.'),
		];

		$form['foolfuuka.plugins.geoip_region_lock.disallow_view'] = [
			'label' => _('Countries disallowed to view the site.'),
			'type' => 'textarea',
			'preferences' => true,
			'validation' => 'trim',
			'class' => 'span6',
			'style' => 'height:60px',
			'help' => __('Comma separated list of GeoIP 2-letter nation codes.') . ' ' . __('Disallowed nations won\'t be able to reach the interface.'),
		];

		$form['separator-1'] = [
			'type' => 'separator'
		];

		$form['foolfuuka.plugins.geoip_region_lock.allow_logged_in'] = [
			'label' => _('Allow logged in users to post regardless.'),
			'type' => 'checkbox',
			'preferences' => true,
			'help' => __('Allow all logged in users to post regardless of region lock? (Mods and Admins are always allowed to post)'),
		];

		$form['separator'] = [
			'type' => 'separator'
		];

		$form['submit'] = [
			'type' => 'submit',
			'value' => __('Submit'),
			'class' => 'btn btn-primary'
		];

		$form['close'] = [
			'type' => 'close'
		];


		$data['form'] = $form;

		\Preferences::submit_auto($data['form']);

		// create a form
		$this->_views["main_content_view"] = \View::forge("foolz/foolframe::admin/form_creator", $data);
		return \Response::forge(\View::forge("foolz/foolframe::admin/default", $this->_views));
	}
}