<?php

namespace AmoCRM;

class Lead extends Entity {
	public $name;
	public $responsible_user_id;
	public $tags;
	public $status_id;
	public $price;
	public $custom_fields;

	private $tags_array;

	/**
	 * Lead constructor.
	 */
	public function __construct() {
		$this->key_name      = 'leads';
		$this->url_name      = $this->key_name;
		$this->custom_fields = [];
		$this->tags_array    = [];
	}

	/**
	 * @param string $value
	 *
	 * @return $this
	 */
	public function setName( $value ) {
		$this->name = $value;

		return $this;
	}

	/**
	 * @param int $value
	 *
	 * @return $this
	 */
	public function setResponsibleUserId( $value ) {
		$this->responsible_user_id = $value;

		return $this;
	}

    /**
     * @param int $value
     *
     * @return $this
     * @throws \Exception
     */
	public function setStatusId( $value ) {
	    if (!isset($value) || !$value) {
	        throw new \Exception('Необходимо задать корректный ID статуса сущности Lead.');
        }

		$this->status_id = $value;

		return $this;
	}

	/**
	 * @param string|int $value
	 *
	 * @return $this
	 */
	public function setPrice( $value ) {
		$this->price = $value;

		return $this;
	}

	/**
	 * @param string $value
	 *
	 * @return $this
	 */
	public function setTags( $value ) {
		if ( ! is_array( $value ) ) {
			$value = [ $value ];
		}

		$this->tags_array = array_merge( $this->tags_array, $value );
		$this->tags       = implode( ',', $this->tags_array );

		return $this;
	}

	/**
	 * @param string     $name
	 * @param string|int $value
	 * @param bool       $enum
	 *
	 * @return $this
	 */
	public function setCustomField( $name, $value, $enum = false ) {
		$field = [
			'id'     => $name,
			'values' => []
		];

		$field_value          = [];
		$field_value['value'] = $value;

		if ( $enum ) {
			$field_value['enum'] = $enum;
		}

		$field['values'][] = $field_value;

		$this->custom_fields[] = $field;

		return $this;
	}
}
