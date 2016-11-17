<?php

/**
 * В этом классе описывается, как искать в логах того или иного шлюза
 * Class Log_Config
 */
class Log_Config {

    /**
     * Здесь описываем, как искать в логах
     * формат:
     * ключ массива - имя коллекции
     */
    public static function gates(){
        $gates = [
            'rgd' => [ //имя коллекции в Монго
                'field_name' => 'name', //поле в Монго, в котором ищем
                'total_regexp' => '//', //регулярное выражение для поиска всех обращений к шлюзу
                'error_regexp' => '/error\.xml/', //регулярное выражение для ошибок
                'title' => 'РЖД',
            ],
            'ufs' => [
                'field_name' => 'content',
                'total_regexp' => '//',
                'error_regexp' => '/Недостаточно средств/',
                'title' => 'УФС',
            ],
            'hotels_aa' => [
                'field_name' => 'name',
                'total_regexp' => '/request/',
                'error_regexp' => '/error/',
                'title' => 'Hotels AA',
            ],
            'hotels_acase' => [
                'field_name' => 'name',
                'total_regexp' => '/request/',
                'error_regexp' => '/error/',
                'title' => 'Hotels Acase',
            ],
            'hotels_cbooking' => [
                'field_name' => 'name',
                'total_regexp' => '/request/',
                'error_regexp' => '/error/',
                'title' => 'Hotels CBooking',
            ],
            'hotels_ccra' => [
                'field_name' => 'name',
                'total_regexp' => '/request/',
                'error_regexp' => '/error/',
                'title' => 'Hotels CCRA',
            ],
            'hotels_rsr' => [
                'field_name' => 'name',
                'total_regexp' => '/request/',
                'error_regexp' => '/error/',
                'title' => 'Hotels RSR',
            ],
            'hotels_hotelbook' => [
                'field_name' => 'name',
                'total_regexp' => '/request/',
                'error_regexp' => '/error/',
                'title' => 'Hotels Hotelbook',
            ],
            'galileo' => [
                'field_name' => 'content',
                'total_regexp' => '//',
                'error_regexp' => '/ErrText/',
                'title' => 'Galileo',
            ],
            'sabre' => [
                'field_name' => 'content',
                'total_regexp' => '//',
                'error_regexp' => '/Error/',
                'title' => 'Sabre',
            ],
            'amadeus' => [
                'field_name' => 'content',
                'total_regexp' => '//',
                'error_regexp' => '/Error/',
                'title' => 'Amadeus',
            ],
            'sirena' => [
                'field_name' => 'content',
                'total_regexp' => '//',
                'error_regexp' => '/error/',
                'title' => 'Сирена',
            ],
            'aeroexpress' => [
                'field_name' => 'content',
                'total_regexp' => '//',
                'error_regexp' => '/error/',
                'title' => 'Аэроэкспресс',
            ],
            'cars' => [
                'field_name' => 'content',
                'total_regexp' => '//',
                'error_regexp' => '/error/',
                'title' => 'Трансферы',
            ],
            'cars_region' => [
                'field_name' => 'content',
                'total_regexp' => '//',
                'error_regexp' => '/error/',
                'title' => 'Трансферы регион',
            ],
        ];

        return $gates;
    }

    /**
     * На какую часть от отображения ошибок показывать процент ошибок, на сколько ниже
     * @var float
     */
    public static $persentage_scale = 0.5;

    /**
     * Уровень, при котором точка считается тревожной
     * @var float
     */
    public static $gate_alarm_level = 0.05;

    /**
     * интервал обновления реалтайм графиков Cyfe в МИНУТАХ
     * ВНИМАНИЕ!!! Это значение должно совпадать с тем, что указано на графиках Cyfe,
     * иначе не будет работать оповещение
     * @var int
     */
    public static $cyfe_realtime_update_interval = 5;

    /**
     * КОл-во точек, превышающих тревожный уровень, при котором срабатывает оповещение
     * @var int
     */
    public static $count_alarm_level_points = 3;

    /**
     * Общее кол-во запросов на шлюзе, необходимое для включения тревоги, не менее
     * @var int
     */
    public static $alarm_total_level = 20;

    public static function email_list()
    {
        $list = [
            'konst21.spb@yandex.ru'
        ];

        return $list;
    }

}
/**
 *
Julia Smirnova [1:34 PM]
В логах пишется запрос и ответ, коллекции названы по именам поставщиков, у ошибок после имени запроса стоит постфикс _error

​[1:34]
Всё )

Konstantin Kiyashko [2:45 PM]
Юля, очень буду тебе признателен за имена коллекций. Я просто с ними никогда не сталкивался

Julia Smirnova [2:48 PM]
aa, acase, cbooking, ccra, rsr, hotelbook
 */

/**
 *
Oleg Tikhonov [3:05 PM]
added a XML snippet: galileo.xml
<xsl:template match="/">
<xsl:choose>
<xsl:when test="/soap-env:Envelope/soap-env:Body/soap-env:Fault">
<exception>
<xsl:apply-templates select="/soap-env:Envelope/soap-env:Body/soap-env:Fault/faultstring" />
</exception>
</xsl:when>
<xsl:when test="//TransactionErrorCode/Code">
<xsl:choose>
<xsl:when test="//PNRBFPrimaryBldChg/ErrorCode">
<exception>
<item>
<xsl:value-of select="concat(//PNRBFPrimaryBldChg/ErrorCode, ' ', //PNRBFPrimaryBldChg/Text)" />
</item>
</exception>
</xsl:when>
<xsl:when test="//PNRBFSecondaryBldChg/ErrorCode">
<exception>
<item>
<xsl:value-of select="concat(//PNRBFSecondaryBldChg/ErrorCode, ' ', //PNRBFSecondaryBldChg/Text)" />
</item>
</exception>
</xsl:when>
<xsl:otherwise>
<exception>
<item>
<xsl:value-of select="." />
</item>
</exception>
</xsl:otherwise>
</xsl:choose>
</xsl:when>
<xsl:when test="//EndTransaction/ErrorCode">
<exception>
<item>
<xsl:value-of select="//EndTransactMessage/Text" />
</item>
</exception>
</xsl:when>
<xsl:when test="//PNRBFRetrieve/ErrText">
<exception>
<item>
<xsl:value-of select="concat(//PNRBFRetrieve/ErrorCode, ' ', //PNRBFRetrieve/ErrText/Text)" />
</item>
</exception>
</xsl:when>
<xsl:when test="//ErrText and not(contains(//ErrText, 'NO FARES'))">
<exception>
<xsl:apply-templates select="//ErrText" />
</exception>
</xsl:when>
<xsl:otherwise>
<xsl:apply-templates select="*" />
</xsl:otherwise>
</xsl:choose>
</xsl:template>Add Comment Click to expand inline 56 lines
Oleg Tikhonov [3:05 PM]
Ну ок, вот галилео, все, чт ов exception/item, то выкидывается исключением

Konstantin Kiyashko [3:06 PM]
Ну то есть вот то, что ты написал - это ошибка. Ок

Oleg Tikhonov [3:06 PM]
Нет, это условия, которые могут быть ошибкой

​[3:08]
Ты знаком с xslt? Просто, например, если присутствует тег //ErrText, но он содержит NO FARES, то это не ошибка

​[3:09]
Но при этом //ErrText в одном ответе может быть и с NO FARES и с каким-нибудь другим текстом и это будет ошибка
 *
Oleg Tikhonov [3:11 PM]
Я понял, что ты не анализируешь DOM, потому и говорю, что если ты будешь искать регулярным выражением ErrText, то надо учитывать, что оно не всегда может быть ошибкой

​[3:12]
И это только галилео

Konstantin Kiyashko [3:14 PM]
давай вот что. дабы я твое время не тратил, по остальным шлюзам дай мне такую же инфо. я все построю, и потом просто внесу уточнения вот как ты говоришь

Oleg Tikhonov [3:14 PM]
Ну ок, попробуй. МОжет сначала пока по галилео?

Konstantin Kiyashko [3:15 PM]
ну давай попробуем

Oleg Tikhonov [3:17 PM]
Просто в этом файле предоставлен набор правил, по которому xslt разбирает xml и условия, по которым выкидывается исключение в коде

​[3:17]
Ну и в какой-то степени, чтобы ты смог сориентироваться по этим условиям

Konstantin Kiyashko [3:18 PM]
 */