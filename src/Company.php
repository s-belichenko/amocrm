<?php

namespace AmoCRM;

class Company
{
	public $name;
	public $responsible_user_id;
	public $tags;
	public $linked_leads_id = [];
	public $custom_fields = [];
	public $id;
	public $last_modified;
	public $_name = 'contacts';

	private $tags_array = [];

	public function setName($value)
	{
		$this->name = $value;

		return $this;
	}



	public function setResponsibleUserId($value)
	{
		$this->responsible_user_id = $value;

		return $this;
	}

	public function setLinkedLeadsId($value)
	{
		if (!is_array($value)) {
			$value = [$value];
		}

		$this->linked_leads_id = array_merge($this->linked_leads_id, $value);

		return $this;
	}

	public function setTags($value)
	{
		if (!is_array($value)) {
			$value = [$value];
		}

		$this->tags_array = array_merge($this->tags_array, $value);
		$this->tags = implode(',', $this->tags_array);

		return $this;
	}

	public function setCustomField($name, $value, $enum = false)
	{
		$field = [
			'id' => $name,
			'values' => []
		];

		$field_value = [];
		$field_value['value'] = $value;

		if ($enum) {
			$field_value['enum'] = $enum;
		}

		$field['values'][] = $field_value;

		$this->custom_fields[] = $field;

		return $this;
	}

	public function setUpdate($id, $last_modified)
	{
		$this->id = $id;
		$this->last_modified = $last_modified;

		return $this;
	}
}
