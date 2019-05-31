<?php

namespace Alfabank;


class ExchangeRates
{
    private $exchange_rates;
    private $urlCurses = 'https://www.cbr.ru/scripts/XML_daily.asp';

    //Загрузить курсы валют
    /**
     * @return array|bool
     */
    public function loadExchangeRates(){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->urlCurses);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $xmlstr = curl_exec($ch);
        curl_close($ch);
        $parse = simplexml_load_string($xmlstr);


        //валюты
        $cursess = array();
        foreach ($parse->Valute as $item) {
            $cursess[strval($item->CharCode)] = array(
                'ID'=> strval($item['ID']),
                'CODE'=>strval($item->NumCode),
                'CHAR_CODE'=>strval($item->CharCode),
                'NOMINAL'=>intval($item->Nominal),
                'NAME'=>strval($item->Name),
                'VALUE'=>doubleval(str_replace(',','.',$item->Value))
            );
        }
        $this->exchange_rates = $cursess;
        if(count($this->exchange_rates)>0){
            return $this->exchange_rates;
        }

        return false;
    }

    //Получить все курсы валют
    /**
     * @return mixed
     */
    public function getAllExchangeRates(){
            return $this->exchange_rates;
    }

    //Получить курс валюты по CharCode
    /**
     * @param $code
     * @return mixed
     */
    public function getExchangeRateByCharCode($charCode){
        if(empty($charCode)){
            throw new \Exception('Empty CharCode parametr');
        }
        return $this->exchange_rates[$charCode];
    }

    //Получить курс валюты по ID
    /**
     * @param $id
     * @return bool
     */
    public function getExchangeRateById($id){
        if(empty($id)){
            throw new \Exception('Empty ID parametr');
        }
        $key = array_search($id, array_column($this->exchange_rates, 'ID'));
        if($key!==false){
            return $this->exchange_rates[$key];
        }
        return false;
    }

    //Получить курс валюты по CODE
    /**
     * @param $code
     * @return bool
     */
    public function getExchangeRateByCode($code){
        if(empty($code)){
            throw new \Exception('Empty CODE parametr');
        }
        $key = array_search($code, array_column($this->exchange_rates, 'CODE'));
        if($key!==false){
            return $this->exchange_rates[$key];
        }
        return false;
    }

    //Получить курс валюты по Nameы
    /**
     * @param $name
     * @return bool
     */
    public function getExchangeRateByName($name){
        if(empty($name)){
            throw new \Exception('Empty NAME parametr');
        }
        $key = array_search($name, array_column($this->exchange_rates, 'NAME'));
        if($key!==false){
            return $this->exchange_rates[$key];
        }
        return false;
    }
}