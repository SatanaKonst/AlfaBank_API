<?php
namespace Alfabank;


/**
 * КЛАСС ДЛЯ ВЗАИМОДЕЙСТВИЯ С ПЛАТЕЖНЫМ ШЛЮЗОМ
 * Класс наследуется от стандартного класса SoapClient.
 */
class Gateway extends \SoapClient {

    protected $USERNAME;
    protected $PASSWORD;

    public function initAuth($login,$pass){
        if(empty($login) || empty($pass)){
            throw new \Exception('Empty Login or Pass');
        }

        $this->USERNAME = $login;
        $this->PASSWORD = $pass;
    }

    /**
     * АВТОРИЗАЦИЯ В ПЛАТЕЖНОМ ШЛЮЗЕ
     * Генерация SOAP-заголовка для WS_Security.
     *
     * ОТВЕТ
     *      SoapHeader      SOAP-заголовок для авторизации
     */
    private function generateWSSecurityHeader() {
        $xml = '
            <wsse:Security SOAP-ENV:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
                <wsse:UsernameToken>
                    <wsse:Username>' . $this->USERNAME . '</wsse:Username>
                    <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">' . $this->PASSWORD . '</wsse:Password>
                    <wsse:Nonce EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary">' . sha1(mt_rand()) . '</wsse:Nonce>
                </wsse:UsernameToken>
            </wsse:Security>';

        return new \SoapHeader('http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd', 'Security', new \SoapVar($xml, XSD_ANYXML), true);
    }

    /**
     * ВЫЗОВ МЕТОДА ПЛАТЕЖНОГО ШЛЮЗА
     * Переопределение функции SoapClient::__call().
     *
     * ПАРАМЕТРЫ
     *      method      Метод из API.
     *      data        Массив данных.
     *
     * ОТВЕТ
     *      response    Ответ.
     */
    public function __call($method, $data) {
        $this->__setSoapHeaders($this->generateWSSecurityHeader()); // Устанавливаем заголовок для авторизации
        return parent::__call($method, $data); // Возвращаем результат метода SoapClient::__call()
    }
}