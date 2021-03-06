#yandexmoney-shopcms

Модуль оплаты yandexmoney-shopcms необходим для интеграции с сервисом [Яндекс.Касса](http://kassa.yandex.ru/) на базе CMS ShopCMS. 

Доступные платежные методы, если вы работаете как юридическое лицо:
* **Банковские карты** -  Visa (включая Electron), MasterCard и Maestro любого банка мира
* **Электронные деньги** - Яндекс.Деньги, WebMoney и QIWI Wallet
* **Наличные** - [Более 170 тысяч пунктов](https://money.yandex.ru/pay/doc.xml?id=526209) оплаты по России
* **Баланс телефона** - Билайн, МегаФон и МТС
* **Интернет банкинг** - Альфа-Клик, Сбербанк Онлайн, MasterPass и Промсвязьбанк
* **Кредитование** - Доверительный платеж (Куппи.ру)

###Установка модуля
Для установки данного модуля необходимо:
* переместить папку `core` из [архива](https://github.com/yandex-money/yandex-money-cms-shopcms/archive/master.zip) в корень Вашего сайта
* инсталлировать YandexMoney (перейти в раздел `Модули` - `Модули оплаты` - `Инсталлировать`)
* перейти к редактированию установленного модуля (`Модули` - `Модули оплаты` - `YandexMoney` - `Редактировать`) и внести нужные настройки
* добавить новый вариант оплаты (`Настройки` - `Варианты оплаты`, модуль YandexMoney)
* в файле `core/includes/helper.php` добавить код:

```php
// Helper for YandexMoney (result)
  if ($_REQUEST["yandexmoney"] == 'yes'){
        $orderID = (int) $_REQUEST["orderNumber"];
        $q = db_query( "select paymethod  from ".ORDERS_TABLE." where orderID=".$orderID);
        $order = db_fetch_row($q);
        if ( $order )
        {
            $paymentMethod = payGetPaymentMethodById( $order["paymethod"] );
            $currentPaymentModule = modGetModuleObj( $paymentMethod["module_id"], PAYMENT_MODULE );
            if ( $currentPaymentModule != null ) {
              $result = $currentPaymentModule->after_payment_php( $orderID, $_REQUEST);
            }
        }
  }
```

Пожалуйста, обязательно делайте бекапы!

###Лицензионный договор.
Любое использование Вами программы означает полное и безоговорочное принятие Вами условий лицензионного договора, размещенного по адресу https://money.yandex.ru/doc.xml?id=527132 (далее – «Лицензионный договор»). 
Если Вы не принимаете условия Лицензионного договора в полном объёме, Вы не имеете права использовать программу в каких-либо целях.

###Нашли ошибку или у вас есть предложение по улучшению модуля?
Пишите нам cms@yamoney.ru
При обращении необходимо:
* Указать наименование CMS и компонента магазина, а также их версии
* Указать версию платежного модуля (доступна на странице описания модуля)
* Описать проблему или предложение
* Приложить снимок экрана (для большей информативности)