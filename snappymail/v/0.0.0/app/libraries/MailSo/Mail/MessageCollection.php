<?php

/*
 * This file is part of MailSo.
 *
 * (c) 2014 Usenko Timur
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MailSo\Mail;

/**
 * @category MailSo
 * @package Mail
 */
class MessageCollection extends \MailSo\Base\Collection
{
	/**
	 * @var string
	 */
	public $FolderHash = '';

	/**
	 * @var int
	 */
	public $MessageCount = 0;

	/**
	 * @var int
	 */
	public $MessageUnseenCount = 0;

	/**
	 * @var int
	 */
	public $MessageResultCount = 0;

	/**
	 * @var string
	 */
	public $FolderName = '';

	/**
	 * @var int
	 */
	public $Offset = 0;

	/**
	 * @var int
	 */
	public $Limit = 0;

	/**
	 * @var string
	 */
	public $Search = '';

	/**
	 * @var int
	 */
	public $UidNext = 0;

	/**
	 * @var int
	 */
	public $ThreadUid = 0;

	/**
	 * @var array
	 */
	public $NewMessages = array();

	/**
	 * @var bool
	 */
	public $Filtered = false;

	public function append($oMessage, bool $bToTop = false) : void
	{
		assert($oMessage instanceof Message);
		parent::append($oMessage, $bToTop);
	}

	public function Clear() : void
	{
		throw new \BadMethodCallException('disallowed');
	}

	#[\ReturnTypeWillChange]
	public function jsonSerialize()
	{
		return array_merge(parent::jsonSerialize(), array(
			'MessageCount' => $this->MessageCount,
			'MessageUnseenCount' => $this->MessageUnseenCount,
			'MessageResultCount' => $this->MessageResultCount,
			'Folder' => $this->FolderName,
			'FolderHash' => $this->FolderHash,
			'UidNext' => $this->UidNext,
			'ThreadUid' => $this->ThreadUid,
			'NewMessages' => $this->NewMessages,
			'Filtered' => $this->Filtered,
			'Offset' => $this->Offset,
			'Limit' => $this->Limit,
			'Search' => $this->Search
		));
	}
}
