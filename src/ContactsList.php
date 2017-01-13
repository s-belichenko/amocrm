<?php
/**
 * Поиск контактов
 *
 * ContactsList.php
 * Date: 13.01.2017
 * Time: 12:05
 * Author: Maksim Klimenko
 * Email: mavsan@gmail.com
 */

namespace AmoCRM;


class ContactsList
{
    protected $_founded = [];
    
    /**
     * Найти контакты по строке запроса. Возвращает:
     * - boolean false, если ничего не найдено;
     * - массив объектов типа Contact, если что-то найдено.
     *
     * @param \AmoCRM\Handler $handler
     * @param string          $query
     *
     * @return array|false
     */
    public function getByQuery(Handler $handler, $query)
    {
        $handler->request(new Request(Request::GET, ['query' => $query],
            ['contacts', 'list']));
    
        $this->analyzeSearchResult($handler);
        
        return $this->_founded;
    }
    
    /**
     * Найти контакты по строке запроса. Возвращает:
     * - boolean false, если ничего не найдено;
     * - массив объектов типа Contact, если что-то найдено.
     *
     * @param \AmoCRM\Handler $handler
     * @param string          $id
     *
     * @return array|false
     */
    public function getByID(Handler $handler, $id)
    {
        $handler->request(new Request(Request::GET, ['id' => $id],
            ['contacts', 'list']));
        
        $this->analyzeSearchResult($handler);
    
        return $this->_founded;
    }
    
    /**
     * Анализ ответа
     * @param \AmoCRM\Handler $handler
     */
    protected function analyzeSearchResult(Handler $handler)
    {
        if($handler->result === false) {
            $this->_founded = false;
            return;
        }
        
        $result = $handler->result;
    
        $handler->result = $result;
    
        foreach ($handler->result->contacts as $contact) {
            $this->_founded[] = new Contact($contact);
        }
    }
}