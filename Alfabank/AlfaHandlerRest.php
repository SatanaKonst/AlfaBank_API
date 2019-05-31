<?php

namespace Alfabank;


class AlfaHandlerRest
{
    protected $USERNAME;
    protected $PASSWORD;
    protected $RETURN_URL;
    protected $client;

    public function __construct($USERNAME,$PASSWORD,$RETURN_URL)
    {
        if(empty($USERNAME) || empty($PASSWORD) || empty($RETURN_URL)){
            throw new \Exception('Empty requared parameters');
        }
        $this->USERNAME = $USERNAME;
        $this->PASSWORD = $PASSWORD;
        $this->RETURN_URL = $RETURN_URL;
        $this->client = new GatewayRest($this->USERNAME,$this->PASSWORD);
    }

    /**
     * ЗАПРОС РЕГИСТРАЦИИ ОДНОСТАДИЙНОГО ПЛАТЕЖА В ПЛАТЕЖНОМ ШЛЮЗЕ
     *      register.do
     *
     * ПАРАМЕТРЫ
     *      userName        Логин магазина.
     *      password        Пароль магазина.
     *      orderNumber     Уникальный идентификатор заказа в магазине.
     *      amount          Сумма заказа в копейках.
     *      returnUrl       Адрес, на который надо перенаправить пользователя в случае успешной оплаты.
     *
     * ОТВЕТ
     *      В случае ошибки:
     *          errorCode       Код ошибки. Список возможных значений приведен в таблице ниже.
     *          errorMessage    Описание ошибки.
     *
     *      В случае успешной регистрации:
     *          orderId         Номер заказа в платежной системе. Уникален в пределах системы.
     *          formUrl         URL платежной формы, на который надо перенаправить браузер клиента.
     *
     *  Код ошибки      Описание
     *      0           Обработка запроса прошла без системных ошибок.
     *      1           Заказ с таким номером уже зарегистрирован в системе.
     *      3           Неизвестная (запрещенная) валюта.
     *      4           Отсутствует обязательный параметр запроса.
     *      5           Ошибка значения параметра запроса.
     *      7           Системная ошибка.
     */
    public function createOrderSinglePayment($orderNumber,$amount,$lang='ru',$currency='',$returnPaymentOrderId=false,$prod=false)
    {
        $params = array(
            'userName' => $this->USERNAME,
            'password' => $this->PASSWORD,
            'orderNumber' => urlencode($orderNumber),
            'amount' => urlencode($amount),
            'returnUrl' => $this->RETURN_URL,
            'language'=>$lang
        );
        if (!empty($currency)) {
            $params['currency'] = $currency;
        }
        return $this->client->registerDo($params,$returnPaymentOrderId,$prod);
    }

    //Создание двухстадийного платежа
    /**
     * РЕГИСТРАЦИЯ ДВУХСТАДИЙНОГО ПЛАТЕЖА В ПЛАТЕЖНОМ ШЛЮЗЕ
     *      registerOrder
     *
     * ПАРАМЕТРЫ
     *      merchantOrderNumber     Уникальный идентификатор заказа в магазине.
     *      amount                  Сумма заказа.
     *      returnUrl               Адрес, на который надо перенаправить пользователя в случае успешной оплаты.
     *
     * ОТВЕТ
     *      В случае ошибки:
     *          errorCode           Код ошибки. Список возможных значений приведен в таблице ниже.
     *          errorMessage        Описание ошибки.
     *
     *      В случае успешной регистрации:
     *          orderId             Номер заказа в платежной системе. Уникален в пределах системы.
     *          formUrl             URL платежной формы, на который надо перенаправить браузер клиента.
     *
     *  Код ошибки      Описание
     *      0           Обработка запроса прошла без системных ошибок.
     *      1           Заказ с таким номером уже зарегистрирован в системе;
     *                  Неверный номер заказа.
     *      3           Неизвестная (запрещенная) валюта.
     *      4           Отсутствует обязательный параметр запроса.
     *      5           Ошибка значения параметра запроса.
     *      7           Системная ошибка.
     */
    /**
     * @param $orderNumber
     * @param $amount (Минимальная единица валюты. Пример 1 копейка. Минимальный размер платежа 1 единица валюты - 1 рубль)
     * @param string $lang в формате 'ru','en'
     * @param string $currency код валюты по ISO 4217
     * @param string $returnPaymentOrderId
     * @return mixed (в случае успеха возвращает ссылку на форму оплаты или ID в платежной системе если установлен $returnPaymentOrderId = true)
     * @throws \Exception
     */
    public function createOrderDoublePayment($orderNumber,$amount,$lang='ru',$currency='',$returnPaymentOrderId=false,$prod=false)
    {
        $params = array(
            'userName' => $this->USERNAME,
            'password' => $this->PASSWORD,
            'orderNumber' => urlencode($orderNumber),
            'amount' => urlencode($amount),
            'returnUrl' => $this->RETURN_URL,
            'language'=>$lang
        );
        if (!empty($currency)) {
            $params['currency'] = $currency;
        }

        return $this->client->registerPreAuth($params,$returnPaymentOrderId,$prod);

    }




    //Получить данные после платежной формы
    /**
     * ЗАПРОС СОСТОЯНИЯ ЗАКАЗА
     *      getOrderStatus
     *
     * ПАРАМЕТРЫ
     *      orderId         Номер заказа в платежной системе. Уникален в пределах системы.
     *
     * ОТВЕТ
     *      ErrorCode       Код ошибки. Список возможных значений приведен в таблице ниже.
     *      OrderStatus     По значению этого параметра определяется состояние заказа в платежной системе.
     *                      Список возможных значений приведен в таблице ниже. Отсутствует, если заказ не был найден.
     *
     *  Код ошибки      Описание
     *      0           Обработка запроса прошла без системных ошибок.
     *      2           Заказ отклонен по причине ошибки в реквизитах платежа.
     *      5           Доступ запрещён;
     *                  Пользователь должен сменить свой пароль;
     *                  Номер заказа не указан.
     *      6           Неизвестный номер заказа.
     *      7           Системная ошибка.
     *
     *  Статус заказа   Описание
     *      0           Заказ зарегистрирован, но не оплачен.
     *      1           Предавторизованная сумма захолдирована (для двухстадийных платежей).
     *      2           Проведена полная авторизация суммы заказа.
     *      3           Авторизация отменена.
     *      4           По транзакции была проведена операция возврата.
     *      5           Инициирована авторизация через ACS банка-эмитента.
     *      6           Авторизация отклонена.
     */
    /**
     * @param $orderId
     * @return array
     * @throws \Exception
     */
    public function getOrderInfo($orderId,$prod=false)
    {
        if (empty($orderId)) {
            throw new \Exception('Empty orderId');
        }
        $data = array(
            'userName' => $this->USERNAME,
            'password' => $this->PASSWORD,
            'orderId' => $orderId
        );

        return $this->client->getOrderInfo($data,$prod);

    }

}