<?php

namespace AmoCRM;

class Contact extends Entity implements \JsonSerializable
{
    protected $name;
    protected $company_name;
    protected $linked_company_id = 0;
    protected $responsible_user_id;
    protected $tags;
    protected $linked_leads_id;
    protected $custom_fields;
    
    protected $tags_array;
    
    public function __construct(\stdClass $searchResult = null)
    {
        $this->key_name = 'contacts';
        $this->url_name = $this->key_name;
        $this->linked_leads_id = [];
        $this->custom_fields = [];
        $this->tags_array = [];
        
        if (!is_null($searchResult)) {
            // экземпляр создается на основе найденного результата
            $this->fillDataFromSearchResult($searchResult);
        }
    }
    
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'last_modified' => $this->last_modified,
            'responsible_user_id' => $this->responsible_user_id,
            'company_name' => $this->company_name,
            'linked_company_id' => $this->linked_company_id,
            'linked_leads_id' => $this->linked_leads_id,
            'tags' => $this->tags,
            'custom_fields' => $this->custom_fields,
        ];
    }
    
    /**
     * Создание класса из данных найденного контакта
     *
     * @param \stdClass $searchResult
     */
    protected function fillDataFromSearchResult(\stdClass $searchResult)
    {
        $this->id = $searchResult->id;
        $this->name = $searchResult->name;
        $this->last_modified = $searchResult->last_modified;
        $this->responsible_user_id = $searchResult->responsible_user_id;
        $this->company_name = $searchResult->company_name;
        $this->linked_company_id = $searchResult->linked_company_id;
        $this->linked_leads_id = $searchResult->linked_leads_id;
        
        $this->fillDataFromSearchResultTags($searchResult);
        $this->fillDataFromSearchResultCustomFields($searchResult);
    }
    
    /**
     * Создание класса из данных найденного контакта, теги
     *
     * @param \stdClass $searchResult
     */
    protected function fillDataFromSearchResultTags(\stdClass $searchResult)
    {
        $tags = [];
        if (property_exists($searchResult, 'tags')) {
            foreach ((array)$searchResult->tags as $tag) {
                $tags[] = $tag->name;
            }
        }
        
        $this->setTags($tags);
    }
    
    /**
     * Создание класса из данных найденного контакта, дополнительные поля
     *
     * @param \stdClass $searchResult
     */
    protected function fillDataFromSearchResultCustomFields(
        \stdClass $searchResult
    ) {
        if (property_exists($searchResult, 'custom_fields')) {
            foreach ((array)$searchResult->custom_fields as $custom_field) {
                $cf = new CustomField($custom_field->id);
                $cf->setValues($custom_field->values);
                
                $this->custom_fields[] = $cf;
            }
        }
    }
    
    /**
     * Имя контакта
     *
     * @param $value
     *
     * @return $this
     */
    public function setName($value)
    {
        $this->name = $value;
        
        return $this;
    }
    
    /**
     * Название компании
     *
     * @param $value
     *
     * @return $this
     */
    public function setCompanyName($value)
    {
        $this->company_name = $value;
        
        return $this;
    }
    
    /**
     * Уникальный идентификатор компании для связи с сделкой
     *
     * @param int $linked_company_id
     *
     * @return $this
     */
    public function setLinkedCompanyId($linked_company_id)
    {
        $this->linked_company_id = $linked_company_id;
        
        return $this;
    }
    
    /**
     * Ответственный пользователь
     *
     * @param $value
     *
     * @return $this
     */
    public function setResponsibleUserId($value)
    {
        $this->responsible_user_id = $value;
        
        return $this;
    }
    
    /**
     * Добавление связи со сделкой
     *
     * @param $value
     *
     * @return $this
     */
    public function setLinkedLeadsId($value)
    {
        if (!is_array($value)) {
            $value = [$value];
        }
        
        $this->linked_leads_id = array_merge($this->linked_leads_id, $value);
        
        return $this;
    }
    
    /**
     * Добавление тега, добавлять под одному, а не как в документации к API -
     * через запятую
     *
     * @param $value
     *
     * @return $this
     */
    public function setTags($value)
    {
        if (!is_array($value)) {
            $value = [$value];
        }
        
        $this->tags_array = array_merge($this->tags_array, $value);
        $this->tags = implode(',', $this->tags_array);
        
        return $this;
    }
    
    /**
     * Добавление дополнительного поля. ИД, и тип значения см. в информации о
     * аккаунте
     *
     * @param string $customFieldID ид дополнительного поля
     * @param string $value         значение
     * @param bool   $enum          тип значения
     *
     * @return $this
     */
    public function setCustomField($customFieldID, $value, $enum = false)
    {
        $cf = new CustomField($customFieldID);
        $presentID = -1;
        
        /** @var CustomField $custom_field */
        foreach ((array)$this->custom_fields as $id => $custom_field)
        {
            if($custom_field->getId() == $customFieldID) {
                $cf = $custom_field;
                $presentID = $id;
                break;
            }
        }
        
        $cf->setValue($value, $enum);
        
        $presentID > -1
            ? $this->custom_fields[$presentID] = $cf
            : $this->custom_fields[] = $cf;
        
        return $this;
    }
}
