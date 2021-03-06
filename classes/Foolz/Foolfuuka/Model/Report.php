<?php

namespace Foolz\Foolfuuka\Model;

use \Foolz\Foolframe\Model\DoctrineConnection as DC;
use \Foolz\Cache\Cache;

/**
 * Generic exception for Report
 */
class ReportException extends \Exception {}

/**
 * Thrown if the exception is not found
 */
class ReportNotFoundException extends ReportException {}

/**
 * Thrown if there's too many character in the reason
 */
class ReportReasonTooLongException extends ReportException {}

/**
 * Thrown if the user sent too many moderation in a timeframe
 */
class ReportSentTooManyException extends ReportException {}

/**
 * Thrown if the comment the user was reporting wasn't found
 */
class ReportCommentNotFoundException extends ReportException {}

/**
 * Thrown if the media the user was reporting wasn't found
 */
class ReportMediaNotFoundException extends ReportException {}

/**
 * Thrown if the report reason is null.
 */
class ReportReasonNullException extends ReportException {}

/**
 * Thrown if the media reporter’s IP has already submitted a report for that post.
 */
class ReportAlreadySubmittedException extends ReportException {}

/**
 * Thrown if the reporter’s IP has been banned.
 */
class ReportSubmitterBannedException extends ReportException {}

/**
 * Manages Reports
 */
class Report
{

	use \Foolz\Plugin\PlugSuit;

	/**
	 * Autoincremented ID
	 *
	 * @var  int
	 */
	public $id = null;

	/**
	 * The ID of the Radix
	 *
	 * @var  int
	 */
	public $board_id = null;

	/**
	 * The ID of the Comment
	 *
	 * @var  int|null  Null if it's not a Comment being reported
	 */
	public $doc_id = null;

	/**
	 * The ID of the Media
	 *
	 * @var  int|null  Null if it's not a Media being reported
	 */
	public $media_id = null;

	/**
	 * The explanation of the report
	 *
	 * @var  string|null
	 */
	public $reason = null;

	/**
	 * The IP of the reporter in decimal format
	 *
	 * @var  string|null
	 */
	public $ip_reporter = null;

	/**
	 * Creation time in UNIX time
	 *
	 * @var  int|null
	 */
	public $created = null;

	/**
	 * The reason escaped for safe echoing in the HTML
	 *
	 * @var  string|null
	 */
	public $reason_processed = null;

	/**
	 * The Radix object
	 *
	 * @var  \Foolz\Foolfuuka\Model\Radix|null
	 */
	public $radix = null;

	/**
	 * The Comment object
	 *
	 * @var  \Foolz\Foolfuuka\Model\Comment|null
	 */
	public $comment = null;

	/**
	 * An array of preloaded moderation
	 *
	 * @var  array|null
	 */
	protected static $preloaded = null;

	/**
	 * Creates a Report object from an associative array
	 *
	 * @param   array  $array  An associative array
	 * @return  \Foolz\Foolfuuka\Model\Report
	 */
	public static function fromArray(array $array)
	{
		$new = new static();
		foreach ($array as $key => $item)
		{
			$new->$key = $item;
		}

		$new->reason_processed = htmlentities(@iconv('UTF-8', 'UTF-8//IGNORE', $new->reason));

		if ( ! isset($new->radix))
		{
			$new->radix = \Radix::getById($new->board_id);
		}

		return $new;
	}

	/**
	 * Takes an array of associative arrays to create an array of Report
	 *
	 * @param   array  $array  An array of associative arrays, typically the result of a getAll
	 * @return  array  An array of Report
	 */
	public static function fromArrayDeep(array $array)
	{
		$result = [];

		foreach ($array as $item)
		{
			$result[] = static::fromArray($item);
		}

		return $result;
	}

	/**
	 * Returns the reason escaped for HTML output
	 *
	 * @return  string
	 */
	public function getReasonProcessed()
	{
		return $this->reason_processed;
	}

	/**
	 * Loads all the moderation from the cache or the database
	 */
	public static function p_preload()
	{
		if (static::$preloaded !== null)
		{
			return;
		}

		try
		{
			static::$preloaded = Cache::item('foolfuuka.model.report.preload.preloaded')->get();
		}
		catch (\OutOfBoundsException $e)
		{
			static::$preloaded = DC::qb()
				->select('*')
				->from(DC::p('reports'), 'r')
				->execute()
				->fetchAll();

			Cache::item('foolfuuka.model.report.preload.preloaded')->set(static::$preloaded, 1800);
		}
	}

	/**
	 * Clears the cached objects for the entire class
	 */
	public static function p_clearCache()
	{
		static::$preloaded = null;
		Cache::item('foolfuuka.model.report.preload.preloaded')->delete();
	}

	/**
	 * Returns an array of Reports by a comment's doc_id
	 *
	 * @param   \Foolz\Foolfuuka\Model\Radix  $board  The Radix on which the Comment resides
	 * @param   int  $doc_id  The doc_id of the Comment
	 *
	 * @return  array  An array of \Foolz\Foolfuuka\Model\Report
	 */
	public static function getByDocId($radix, $doc_id)
	{
		static::preload();
		$result = [];

		foreach(static::$preloaded as $item)
		{
			if ($item['board_id'] === $radix->id && $item['doc_id'] === $doc_id)
			{
				$result[] = $item;
			}
		}

		return static::fromArrayDeep($result);
	}

	/**
	 * Returns an array of Reports by a Media's media_id
	 *
	 * @param   \Foolz\Foolfuuka\Model\Radix  $board  The Radix on which the Comment resides
	 * @param   int  $media_id  The media_id of the Media
	 *
	 * @return  array  An array of \Foolz\Foolfuuka\Model\Report
	 */
	public static function getByMediaId($radix, $media_id)
	{
		static::preload();
		$result = [];

		foreach (static::$preloaded as $item)
		{
			if ($item['board_id'] === $radix->id && $item['media_id'] === $media_id)
			{
				$result[] = $item;
			}
		}

		return static::fromArrayDeep($result);
	}

	/**
	 * Fetches and returns all the Reports
	 *
	 * @return  array  An array of Report
	 */
	public static function getAll()
	{
		static::preload();

		return static::fromArrayDeep(static::$preloaded);
	}

	/**
	 * Returns the number of Reports
	 *
	 * @return  int  The number of Report
	 */
	public static function count()
	{
		static::preload();

		return count(static::$preloaded);
	}

	/**
	 * Adds a new report to the database
	 *
	 * @param   \Foolz\Foolfuuka\Model\Radix  $radix  The Radix to which the Report is referred to
	 * @param   int     $id           The ID of the object being reported (doc_id or media_id)
	 * @param   string  $reason       The reason for the report
	 * @param   string  $ip_reporter  The IP in decimal format
	 * @param   string  $mode         The type of column (doc_id or media_id)
	 *
	 * @return  \Foolz\Foolfuuka\Model\Report   The created report
	 * @throws  ReportMediaNotFoundException    If the reported media_id doesn't exist
	 * @throws  ReportCommentNotFoundException  If the reported doc_id doesn't exist
	 * @throws  ReportReasonTooLongException    If the reason inserted was too long
	 * @throws  ReportSentTooManyException      If the user sent too many moderation in a timeframe
	 * @throws  ReportReasonNullException       If the report reason is null
	 * @throws  ReportAlreadySubmittedException If the reporter’s IP has already submitted a report for the post.
	 * @throws  ReportSubmitterBannedException  If the reporter’s IP has been banned.
	 */
	public static function p_add($radix, $id, $reason, $ip_reporter = null, $mode = 'doc_id')
	{
		$new = new static();
		$new->radix = $radix;
		$new->board_id = $radix->id;

		if ($mode === 'media_id')
		{
			try
			{
				Media::getByMediaId($new->radix, $id);
			}
			catch (MediaNotFoundException $e)
			{
				throw new ReportMediaNotFoundException(__('The media file you are reporting could not be found.'));
			}

			$new->media_id = (int) $id;
		}
		else
		{
			try
			{
				Board::forge()
					->getPost()
					->setRadix($new->radix)
					->setOptions('doc_id', $id)
					->getComments();
			}
			catch (BoardException $e)
			{
				throw new ReportCommentNotFoundException(__('The post you are reporting could not be found.'));
			}

			$new->doc_id =  (int) $id;
		}

		if (trim($reason) === null)
		{
			throw new ReportReasonNullException(__('A reason must be included with your report.'));
		}

		if (mb_strlen($reason) > 2048)
		{
			throw new ReportReasonTooLongException(__('The reason for you report was too long.'));
		}

		$new->reason = $reason;

		if ($new->ip_reporter === null)
		{
			$new->ip_reporter = \Input::ip_decimal();
		}
		else
		{
			$new->ip_reporter = $ip_reporter;
		}

		// check how many moderation have been sent in the last hour to prevent spam
		$row = DC::qb()
			->select('COUNT(*) as count')
			->from(DC::p('reports'), 'r')
			->where('created > :time')
			->andWhere('ip_reporter = :ip_reporter')
			->setParameter(':time', time() - 86400)
			->setParameter(':ip_reporter', $new->ip_reporter)
			->execute()
			->fetch();

		if ($row['count'] > 25)
		{
			throw new ReportSentTooManyException(__('You have submitted too many reports within an hour.'));
		}

		$reported = DC::qb()
			->select('COUNT(*) as count')
			->from(DC::p('reports'), 'r')
			->where('board_id = :board_id')
			->andWhere('ip_reporter = :ip_reporter')
			->andWhere('doc_id = :doc_id')
			->orWhere('media_id = :media_id')
			->setParameters([
				':board_id' => $new->board_id,
				':doc_id' => $new->doc_id,
				':media_id' => $new->media_id,
				':ip_reporter' => $new->ip_reporter
			])
			->execute()
			->fetch();

		if ($reported['count'] > 0)
		{
			throw new ReportSubmitterBannedException(__('You can only submit one report per post.'));
		}

		if ($ban = \Ban::isBanned(\Input::ip_decimal(), $new->radix))
		{
			if ($ban->board_id == 0)
			{
				$banned_string = __('It looks like you were banned on all boards.');
			}
			else
			{
				$banned_string = __('It looks like you were banned on /'.$new->radix->shortname.'/.');
			}

			if ($ban->length)
			{
				$banned_string .= ' '.__('This ban will last until:').' '.date(DATE_COOKIE, $ban->start + $ban->length).'.';
			}
			else
			{
				$banned_string .= ' '.__('This ban will last forever.');
			}

			if ($ban->reason)
			{
				$banned_string .= ' '.__('The reason for this ban is:').' «'.$ban->reason.'».';
			}

			if ($ban->appeal_status == \Ban::APPEAL_NONE)
			{
				$banned_string .= ' '.\Str::tr(__('If you\'d like to appeal to your ban, go to the :appeal page.'),
					['appeal' => '<a href="'.\Uri::create($new->radix->shortname.'/appeal').'">'.__('appeal').'</a>']);
			}
			elseif ($ban->appeal_status == \Ban::APPEAL_PENDING)
			{
				$banned_string .= ' '.__('Your appeal is pending.');
			}

			throw new ReportSubmitterBannedException($banned_string);
		}

		$new->created = time();

		DC::forge()->insert(DC::p('reports'), [
			'board_id' => $new->board_id,
			'doc_id' => $new->doc_id,
			'media_id' => $new->media_id,
			'reason' => $new->reason,
			'ip_reporter' => $new->ip_reporter,
			'created' => $new->created,
		]);

		static::clearCache();

		return $new;
	}

	/**
	 * Deletes a Report
	 *
	 * @param   int  $id  The ID of the Report
	 *
	 * @throws  \Foolz\Foolfuuka\Model\ReportNotFoundException
	 */
	public static function p_delete($id)
	{
		DC::qb()
			->delete(DC::p('reports'))
			->where('id = :id')
			->setParameter(':id', $id)
			->execute();

		static::clearCache();
	}

	/**
	 * Returns the Comment by doc_id or the first Comment found with a matching media_id
	 *
	 * @return  \Foolz\Foolfuuka\Model\Comment
	 * @throws  \Foolz\Foolfuuka\Model\ReportMediaNotFoundException
	 * @throws  \Foolz\Foolfuuka\Model\ReportCommentNotFoundException
	 */
	public function p_getComment()
	{

		if ($this->media_id !== null)
		{
			// custom "get the first doc_id with the media"
			$doc_id_res = DC::qb()
				->select('doc_id')
				->from(\Radix::getById($this->board_id)->getTable(), 'a')
				->where('media_id = :media_id')
				->orderBy('timestamp', 'desc')
				->setParameter('media_id', $this->media_id)
				->execute()
				->fetch();

			if ($doc_id_res !== null)
			{
				$this->doc_id = $doc_id_res->doc_id;
			}
			else
			{
				throw new ReportMediaNotFoundException(__('The reported media file could not be found.'));
			}
		}

		try
		{
			$comments = Board::forge()
				->getPost()
				->setRadix($this->radix)
				->setOptions('doc_id', $this->doc_id)
				->getComments();
			$this->comment = current($comments);
		}
		catch (BoardException $e)
		{
			throw new ReportCommentNotFoundException(__('The reported post could not be found.'));
		}

		return $this->comment;
	}
}