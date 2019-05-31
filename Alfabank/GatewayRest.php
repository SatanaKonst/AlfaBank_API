<?php

namespace Alfabank;


class GatewayRest
{

    protected $GATEWAY_URL_DBG='https://web.rbsuat.com/ab/rest/';
    protected $GATEWAY_URL_PROD='https://pay.alfabank.ru/payment/rest/';


    /**
     * ФУНКЦИЯ ДЛЯ ВЗАИМОДЕЙСТВИЯ С ПЛАТЕЖНЫМ ШЛЮЗОМ
     *
     * Для отправки POST запросов на платежный шлюз используется
     * стандартная библиотека cURL.
     *
     * ПАРАМЕТРЫ
     *      method      Метод из API.
     *      data        Массив данных.
     *
     * ОТВЕТ
     *      response    Ответ.
     */
    private function gateway($method, $data, $prod=false) {
        $curl = curl_init(); // Инициализируем запрос
        $url = $this->GATEWAY_URL_DBG;
        if($prod===true){
            $url = $this->GATEWAY_URL_PROD;
        }
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url.$method, // Полный адрес метода
            CURLOPT_RETURNTRANSFER => true, // Возвращать ответ
            CURLOPT_POST => true, // Метод POST
            CURLOPT_POSTFIELDS => http_build_query($data) // Данные в запросе
        ));
        $response = curl_exec($curl); // Выполняем запрос

        $response = json_decode($response, true); // Декодируем из JSON в массив
        curl_close($curl); // Закрываем соединение
        return $response; // Возвращаем ответ
    }



    //Одностадийный платеж
    public function registerDo($data,$returnPaymentOrderId=false,$prod=false){
        if(count($data)==0){
            throw new \Exception('Empty parametrs');
        }
        $response = $this->gateway('register.do', $data,$prod);
        if (isset($response['errorCode'])) { // В случае ошибки вывести ее
            throw new \Exception( $response['errorCode'] . ': ' . $response['errorMessage']);
        } else { // В случае успеха перенаправить пользователя на платежную форму
            if($returnPaymentOrderId===false){
                return $response['formUrl'];
            }else{
                return $response['orderId'];
            }

        }
    }

    //Двухстадийный плтеж
    public function registerPreAuth($data,$returnPaymentOrderId=false){
        $response = $this->gateway('registerPreAuth.do', $data);
        if (isset($response['errorCode'])) { // В случае ошибки вывести ее
            throw new \Exception( $response['errorCode'] . ': ' . $response['errorMessage']);
        } else { // В случае успеха перенаправить пользователя на платежную форму
            if($returnPaymentOrderId===false){
                return $response['formUrl'];
            }else{
                return $response['orderId'];
            }

        }
    }

    //Получить статус платежа
    public function getOrderInfo($data,$prod=false)
    {
        if (count($data)==0) {
            throw new \Exception('Empty orderId');
        }
        $response = $this->gateway('getOrderStatus.do', $data,$prod);
        $responseErrorCode = $response['ErrorCode'];
        $responseOrderStatus = $response['OrderStatus'];

        switch ($responseErrorCode){
            case 0: $errorMsg = 'Обработка запроса прошла без системных ошибок'; break;
            case 2: $errorMsg = 'Заказ отклонен по причине ошибки в реквизитах платежа'; break;
            case 5: $errorMsg = 'Доступ запрещён, пользователь должен сменить свой пароль или номер заказа не указан'; break;
            case 6: $errorMsg = 'Неизвестный номер заказа'; break;
            case 7: $errorMsg = 'Системная ошибка'; break;
        }

        switch ($responseOrderStatus){
            case 0: $statusMsg = 'Заказ зарегистрирован, но не оплачен'; break;
            case 1: $statusMsg = 'Предавторизованная сумма захолдирована (для двухстадийных платежей)'; break;
            case 2: $statusMsg = 'Проведена полная авторизация суммы заказа'; break;
            case 3: $statusMsg = 'Авторизация отменена'; break;
            case 4: $statusMsg = 'По транзакции была проведена операция возврата'; break;
            case 5: $statusMsg = 'Инициирована авторизация через ACS банка-эмитента'; break;
            case 6: $statusMsg = 'Авторизация отклонена'; break;
        }

        return array(
            'error'=>array(
                'code'=>$responseErrorCode,
                'msg'=> $errorMsg
            ),
            'status'=>array(
                'code'=>$responseOrderStatus,
                'msg'=>$statusMsg
            )
        );
    }


}