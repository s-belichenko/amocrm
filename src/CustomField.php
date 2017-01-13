<?php
/**
 * Работа с дополнительными полями
 *
 * CustomField.php
 * Date: 13.01.2017
 * Time: 10:12
 * Author: Maksim Klimenko
 * Email: mavsan@gmail.com
 */

namespace AmoCRM;


class CustomField implements \JsonSerializable
{
    protected $id;
    
    protected $values = [];
    
    /**
     * CustomField constructor.
     *
     * @param $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }
    
    /**
     * Пакетная установка значений, дублирующиеся значения будут удалены
     * автоматически.
     *
     * Каждый элемент переданного массива может быть экземпляром:
     * - stdClass со свойствами value и enum (не обязательно поле);
     * - ассоциированный массив с полями value и enum (не обязательное поле).
     *
     * @param array $values
     *
     * @return $this
     */
    public function setValues(array $values)
    {
        foreach ($values as $_value) {
            
            if ($_value instanceof \stdClass) {
                $nValue = $_value->value;
                $nType = property_exists($_value, 'enum') ? $_value->enum : '';
            } else {
                $nValue = $_value['value'];
                $nType =
                    array_key_exists('enum', $_value) ? $_value['enum'] : '';
            }
            
            $this->setValue($nValue, $nType);
        }
        
        return $this;
    }
    
    /**
     * Добавление нового значения, если такое значение уже присутствует - новое
     * добавлено не будет (сравнение именно по значению, тип в сравнении не
     * участвует).
     *
     * @param string $value значение
     * @param string $type  тип (см информацию о аккаунте)
     *
     * @return $this
     */
    public function setValue($value, $type = '')
    {
        if (!$this->isValueExists($value)) {
            $data['value'] = $value;
            
            if (!empty($type)) {
                $data['enum'] = $type;
            }
            
            $this->values[] = $data;
        }
        
        return $this;
    }
    
    /**
     * Поиск значения в дополнительном поле
     *
     * @param string $value искомое значение
     *
     * @return bool true - найдено, false - не найдено
     */
    protected function isValueExists($value)
    {
        foreach ((array)$this->values as $_value) {
            if ($value == $_value['value']) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Возвращает данные дополнительного поля
     *
     * @return array
     */
    public function getCustomField()
    {
        return [
            'id'     => $this->id,
            'values' => $this->values,
        ];
    }
    
    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
    
    function jsonSerialize()
    {
        return $this->getCustomField();
    }
}