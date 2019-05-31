# AlfaBank_API
Класс из документации альфабанка (https://pay.alfabank.ru/ecommerce/instructions/merchantManual/pages/index.html)

Работать можно как по REST API либо по WS (Методы одинаковые)

<h3>Вызов страницы оплаты</h3>
<pre>
$handler = new \Alfabank\AlfaHandlerRest(ALFA_USERNAME,ALFA_PASSWORD,$returnURL);
$createOrder = $handler->createOrderSinglePayment(
    intval($Order_ID),
    intval($priceRub),
    $lang,
    $currency,
    $returnPaymentOrderId,
    $prod
);
</pre>
<blockquote>
$Order_ID - ID заказа <br>
$priceRub - Цена в минимальной еденице валюты <br>
$lang - Язык страницы оплаты <br>
$currency - Валюта (Код валюты платежа ISO 4217. Если не указан, считается равным 810 (российские рубли)) <br>
$returnPaymentOrderId - Вернуть id заказа (по дефолту возвращает Url формы оплаты) <br>
</blockquote>

<h3>Проверка статуса олаты</h3>

<pre>
$handler = new \Alfabank\AlfaHandlerRest(ALFA_USERNAME,ALFA_PASSWORD,$returnURL);
$orderInfo = $handler->getOrderInfo($_GET['orderId'],ALFA_PAY_PROD);
</pre>


Так же есть возможность загрузить актуальный курс валюты с cbr.ru

<pre>
$exchange = new \Alfabank\ExchangeRates();
if($exchange->loadExchangeRates()) {
    $euroCost = ($exchange->getExchangeRateByCharCode('EUR'))['VALUE'];
}
</pre>
