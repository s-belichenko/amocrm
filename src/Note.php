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
