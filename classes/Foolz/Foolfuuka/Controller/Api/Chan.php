<?php

namespace Foolz\Foolfuuka\Controller\Api;

use \Foolz\Inet\Inet;

class Chan extends \Controller_Rest
{
	protected $_radix = null;
	protected $_theme = null;
	protected $format = 'json';

	public function before()
	{
		parent::before();

		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Credentials: true');
		header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
		header('Access-Control-Max-Age: 604800');

		// this has already been forged in the foolfuuka bootstrap
		$theme_instance = \Foolz\Theme\Loader::forge('foolfuuka');

		if (\Input::get('theme'))
		{
			try
			{
				$theme_name = \Input::get('theme', \Cookie::get('theme')) ? : \Preferences::get('foolfuuka.theme.default');
				$theme = $theme_instance->get('foolz', $theme_name);
				if ( ! isset($theme->enabled) || ! $theme->enabled)
				{
					throw new \OutOfBoundsException;
				}
				$this->_theme = $theme;
			}
			catch (\OutOfBoundsException $e)
			{
				$theme_name = 'foolz/foolfuuka-theme-foolfuuka';
				$this->_theme = $theme_instance->get('foolz', $theme_name);
			}
		}

		// already done in the foolfuuka bootstrap
		// $this->_theme->bootstrap();

		if ( ! \Input::get('board') && ! \Input::get('action') && ! \Input::post('board') && ! \Input::post('action'))
		{
			$segments = \Uri::segments();
			$uri = \Uri::base().'_'.
				'/'.array_shift($segments).'/'.array_shift($segments).'/'.array_shift($segments).'/?';


			foreach ($segments as $key => $segment)
			{
				if ($key % 2 == 0)
				{
					$uri .= urlencode($segment).'=';
				}
				else
				{
					$uri .= urlencode($segment).'&';
				}
			}

			\Response::redirect($uri);
		}
	}

	/**
	 * Commodity to check that the shortname is not wrong and return a coherent error
	 */
	protected function check_board()
	{
		$board = \Input::get('board');

		if ( ! $board)
		{
			$board = \Input::post('board');
		}

		if ( ! $board)
		{
			//$this->response(['error' => __('You didn\'t select a board')], 404);
			return false;
		}

		if ( ! $this->_radix = \Radix::setSelectedByShortname($board))
		{
			//$this->response(['error' => __('The board you selected doesn\'t exist')], 404);
			return false;
		}


		return true;
	}

	/**
	 * Returns a thread
	 *
	 * Available filters: num (required)
	 *
	 * @author Woxxy
	 */
	public function get_thread()
	{
		if ( ! $this->check_board())
		{
			return $this->response(['error' => __('No board selected.')], 404);
		}

		$num = \Input::get('num');
		$latest_doc_id = \Input::get('latest_doc_id');

		if ( ! $num)
		{
			return $this->response(['error' => __('The "num" parameter is missing.')], 404);
		}

		if ( ! ctype_digit((string) $num))
		{
			return $this->response(['error' => __('The value for "num" is invalid.')], 404);
		}

		$num = intval($num);

		try
		{
			// build an array if we have more specifications
			if ($latest_doc_id)
			{
				if ( ! ctype_digit((string) $latest_doc_id))
				{
					return $this->response(['error' => __('The value for "latest_doc_id" is malformed.')], 404);
				}

				$options = [
					'type' => 'from_doc_id',
					'latest_doc_id' => $latest_doc_id,
					'realtime' => true,
					'controller_method' =>
					ctype_digit((string) \Input::get('last_limit')) ? 'last/'.\Input::get('last_limit') : 'thread'
				];

				$board = \Board::forge()
					->getThread($num)
					->setRadix($this->_radix)
					->setApi(['theme' => $this->_theme, 'board' => false])
					->setOptions($options);

				$comments = $board->getComments();

				return $this->response($comments, 200);
			}
			else
			{
				$options = [
					'type' => 'thread',
				];

				$board = \Board::forge()
					->getThread($num)
					->setRadix($this->_radix)
					->setApi(['theme' => $this->_theme, 'board' => false])
					->setOptions($options);

				$comments = $board->getComments();

				return $this->response($comments, 200);
			}

		}
		catch (\Foolz\Foolfuuka\Model\BoardThreadNotFoundException $e)
		{
			return $this->response(['error' => __('Thread not found.')], 200);
		}
		catch (\Foolz\Foolfuuka\Model\BoardException $e)
		{
			return $this->response(['error' => __('Encountered an unknown error.')], 500);
		}
	}

	public function get_post()
	{
		if ( ! $this->check_board())
		{
			return $this->response(['error' => __('No board was selected.')], 404);
		}

		$num = \Input::get('num');

		if ( ! $num)
		{
			return $this->response(['error' => __('The "num" parameter is missing.')], 404);
		}

		if ( ! \Board::isValidPostNumber($num))
		{
			return $this->response(['error' => __('The value for "num" is invalid.')], 404);
		}

		try
		{
			$board = \Board::forge()
				->getPost($num)
				->setRadix($this->_radix)
				->setApi(['board' => false, 'theme' => $this->_theme]);

			$comment = current($board->getComments());

			// no index for the single post
			$this->response($comment, 200);
		}
		catch (\Foolz\Foolfuuka\Model\BoardPostNotFoundException $e)
		{
			return $this->response(['error' => __('Post not found.')], 200);
		}
		catch (\Foolz\Foolfuuka\Model\BoardException $e)
		{
			return $this->response(['error' => $e->getMessage()], 404);
		}
	}

	public function post_user_actions()
	{
		if ( ! \Security::check_token())
		{
			return $this->response(['error' => __('The security token was not found. Please try again.')]);
		}

		if ( ! $this->check_board())
		{
			return $this->response(['error' => __('No board was selected.')], 404);
		}

		if (\Input::post('action') === 'report')
		{
			try
			{
				\Report::add($this->_radix, \Input::post('doc_id'), \Input::post('reason'));
			}
			catch (\Foolz\Foolfuuka\Model\ReportException $e)
			{
				return $this->response(['error' => $e->getMessage()], 200);
			}

			return $this->response(['success' => __('You have successfully submitted a report for this post.')], 200);
		}

		if (\Input::post('action') === 'report_media')
		{
			try
			{
				\Report::add($this->_radix, \Input::post('media_id'), \Input::post('reason'), null, 'media_id');
			}
			catch (\Foolz\Foolfuuka\Model\ReportException $e)
			{
				return $this->response(['error' => $e->getMessage()], 200);
			}

			return $this->response(['success' => __('This media was reported.')], 200);
		}

		if (\Input::post('action') === 'delete')
		{
			try
			{
				$comments = \Board::forge()
					->getPost()
					->setOptions('doc_id', \Input::post('doc_id'))
					->setCommentOptions('clean', false)
					->setRadix($this->_radix)
					->getComments();

				$comment = current($comments);
				$comment->delete(\Input::post('password'));
			}
			catch (\Foolz\Foolfuuka\Model\BoardException $e)
			{
				return $this->response(['error' => $e->getMessage()], 200);
			}
			catch (\Foolz\Foolfuuka\Model\CommentDeleteWrongPassException $e)
			{
				return $this->response(['error' => $e->getMessage()], 200);
			}

			return $this->response(['success' => __('This post was deleted.')], 200);
		}
	}

	public function post_mod_actions()
	{
		if ( ! \Security::check_token())
		{
			return $this->response(['error' => __('The security token was not found. Please try again.')]);
		}

		if ( ! \Auth::has_access('comment.mod_capcode'))
		{
			return $this->response(['error' => __('Access Denied.')], 403);
		}

		if ( ! $this->check_board())
		{
			return $this->response(['error' => __('No board was selected.')], 404);
		}

		if (\Input::post('action') === 'delete_report')
		{
			try
			{
				\Report::delete(\Input::post('id'));
			}
			catch (\Foolz\Foolfuuka\Model\ReportException $e)
			{
				return $this->response(['error' => $e->getMessage()], 404);
			}

			return $this->response(['success' => __('The report was deleted.')], 200);
		}

		if (\Input::post('action') === 'delete_post')
		{
			try
			{
				$comments = \Board::forge()
					->getPost()
					->setOptions('doc_id', \Input::post('id'))
					->setRadix($this->_radix)
					->getComments();

				$comment = current($comments);
				$comment->delete();
			}
			catch (\Foolz\Foolfuuka\Model\BoardException $e)
			{
				return $this->response(['error' => $e->getMessage()], 404);
			}

			return $this->response(['success' => __('This post was deleted.')], 200);
		}

		if (\Input::post('action') === 'delete_image')
		{
			try
			{
				\Media::getByMediaId($this->_radix, \Input::post('id'))->delete(true, true, true);
			}
			catch (\Foolz\Foolfuuka\Model\MediaNotFoundException $e)
			{
				return $this->response(['error' => $e->getMessage()], 404);
			}

			return $this->response(['success' => __('This image was deleted.')], 200);
		}

		if (\Input::post('action') === 'ban_image_local' || \Input::post('action') === 'ban_image_global')
		{
			$global = false;
			if (\Input::post('action') === 'ban_image_global')
			{
				$global = true;
			}

			try
			{
				\Media::getByMediaId($this->_radix, \Input::post('id'))->ban($global);
			}
			catch (\Foolz\Foolfuuka\Model\MediaNotFoundException $e)
			{
				return $this->response(['error' => $e->getMessage()], 404);
			}

			return $this->response(['success' => __('This image was banned.')], 200);
		}

		if (\Input::post('action') === 'ban_user')
		{
			try
			{
				\Ban::add(\Foolz\Inet\Inet::ptod(\Input::post('ip')),
					\Input::post('reason'),
					\Input::post('length'),
					\Input::post('board_ban') === 'global' ? array() : array($this->_radix->id)
				);
			}
			catch (\Foolz\Foolfuuka\Model\BanException $e)
			{
				return $this->response(['error' => $e->getMessage()], 404);
			}

			return $this->response(['success' => __('This user was banned.')], 200);
		}
	}
}