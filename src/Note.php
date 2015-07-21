<?php

namespace AmoCRM;

class Note
{
	public $element_id;
	public $element_type;
	public $note_type;
	public $text;
	public $id;
	public $last_modified;
	public $_name = 'notes';

	const DEAL_CREATED = 1;
	const CONTACT_CREATED = 2;
	const DEAL_STATUS_CHANGED = 3;
	const COMMON = 4;
	const ATTACHEMENT = 5;
	const CALL = 6;
	const MAIL_MESSAGE = 7;
	const MAIL_MESSAGE_ATTACHMENT = 8;
	const EXTERNAL_ATTACH = 9;
	const CALL_IN = 10;
	const CALL_OUT = 11;
	const COMPANY_CREATED = 12;
	const TASK_RESULT = 13;
	const MAX_SYSTEM = 99;
	const DROPBOX = 101;
	const SMS_IN = 102;
	const SMS_OUT = 103;

	const TYPE_CONTACT = 1;
	const TYPE_LEAD = 2;

	public function setElementId($value)
	{
		$this->element_id = $value;

		return $this;
	}

	public function setElementType($value)
	{
		$this->element_type = $value;

		return $this;
	}

	public function setNoteType($value)
	{
		$this->note_type = $value;

		return $this;
	}

	public function setText($value)
	{
		$this->text = $value;

		return $this;
	}

	public function setUpdate($id, $lat_modified)
	{
		$this->id = $id;
		$this->last_modified = $last_modified;

		return $this;
	}
}
