<?php

namespace ItCpp\Sendrequest;

class SearchString
{
    /**
     * Поисковой запрос адресной строки вида:
     * ?foo=bar&name=Ivan
     * 
     * @var string
     */
    protected $string;

    /**
     * Массив данных
     * 
     * @var array
     */
    protected $data = [];

    /**
     * Инициализация объекта
     * 
     * @param string
     * @return void
     */
    public function __construct($string = "")
    {
        $this->string = str_replace("?", "", $string);
    }

    /**
     * Функиця обработки строки
     * 
     * @return self
     */
    public function parseString()
    {
        $string = urldecode($this->string);
        $params = explode("&", $string);

        foreach ($params as $param) {

            $row = explode("=", $param);

            if (isset($row[0]) and $row[0] != "")
                $this->data[$row[0]] = $row[1] ?? null;
        }

        return $this->data;
    }

    /**
     * Вывод данных
     * 
     * @return array
     */
    public function getArray()
    {
        if (!count($this->data))
            $this->parseString();

        return $this->data;
    }
}
