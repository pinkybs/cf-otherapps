<?php

/**

* システム名    ：携帯絵文字自動変換

* プログラム名  ：MobileClass

*

* :    :    :    :    :    :    :    :    :    :    :    :    :    :    :    :    :

* [プログラム概要]

* DoCoMo向けに入力した絵文字を、アクセスしてきたキャリアに合わせて

* 自動的に互換する絵文字(コード)に置換します。

* DoCoMo絵文字の入力は、関数の引数に絵文字入力ソフトを使って直接入力するか、

* 16進法を引数に与える事により実現します(推奨は16進法です)

*

* :    :    :    :    :    :    :    :    :    :    :    :    :    :    :    :    :

*

* @since            2006/11/20

* @auther           T.Kotaka

*

* @varsion          $Id: MobileClass.php,v 1.3 2008/05/28 02:20:24 duanlp Exp $

*

* [メソッド一覧]

* 1) __construct             (public)     コンストラクタ

* 2) MobileClass             (public)     コンストラクタ(__constructメソッドをコール)

* 3) getErrorMessage         (public)     直前に発生したエラーメッセージを返却します。

* 4) Convert                 (public)     引数で指定された絵文字コードを、各キャリアに合わせた絵文字コードに変換し返却

* 5) _setErrorMessage        (private)    エラーメッセージをセットします

* 5) _setUserAgent           (private)    ユーザーエージェントをセットします

*                                         UserAgent = DoCoMo：1、SoftBank：2、EzWeb：3、Others(PC)：4

* 6) _EmojiTable             (private)    絵文字テーブルをセットします

*

*

* [改版履歴]

* 000001    2007/01/22    16進法による入力をサポートしました。

* 000002    2007/01/22    ユーザーエージェントが「SoftBank」の際に、絵文字変換されない不具合を修正しました。

* 000003    2007/01/23    EzWebにおいて、絵文字の代替文字が出力出来ない不具合を修正しました。

* 000004    2007/02/20    各キャリアの識別番号を定数に移行しました。

* 000005    2007/02/22    getErrorMessageメソッドを追加しました。

* 000006    2007/02/22    各種エラーメッセージを定数にまとめました。

*/



// {{{ constants



/**

 * @const MB_USER_AGENT_DOCOMO

 * @value 1

 */

 define('MB_USER_AGENT_DOCOMO',   1);



/**

 * @const MB_USER_AGENT_SOFTBANK

 * @value 2

 */

 define('MB_USER_AGENT_SOFTBANK', 2);



/**

 * @const MB_USER_AGENT_EZWEB

 * @value 3

 */

 define('MB_USER_AGENT_EZWEB',    3);



/**

 * @const MB_USER_AGENT_OTHERS

 * @value 4

 */

 define('MB_USER_AGENT_OTHERS',   4);



/**

 * @const ERROR_MESSAGE_INPUTEMOJI

 * @value 入力モードの指定が正しくありません。

 */

 define('ERROR_MESSAGE_INPUTEMOJI','表示する絵文字コード、若しくは絵文字の引数がありません');



/**

 * @const ERROR_MESSAGE_INPUTMODE

 * @value 入力モードの指定が正しくありません。

 */

 define('ERROR_MESSAGE_INPUTMODE','入力モードの指定が正しくありません。');



/**

 * @const ERROR_MESSAGE_NOT_EXISTS_TO_DOCOMO

 * @value DoCoMo絵文字が見つかりません。

 */

 define('ERROR_MESSAGE_NOT_EXISTS_TO_DOCOMO','絵文字が見つかりません。');



// }}}



class MobileClass {



    // {{{ properties



    /**

     * name EMOJI of 絵文字テーブル

     *

     * @var array

     * @access public

     */

    var $EMOJI             = array();



    /**

     * name InputMode of 0 Or 1 （0：バイナリ入力、1：絵文字直接入力）

     *

     * @var integer

     * @access public

     */

    var $InputMode         = 0;



    /**

     * name strErrorMessage of エラーメッセージ

     *

     * @var string

     * @access public

     */

    var $strErrorMessage   = '';



    /**

     * name intUserAgent of ユーザーエージェント

     *

     * @var integer

     * @access public

     */

    var $intUserAgent      = null;



    // }}}

    // {{{ construct



   /**

    * コンストラクタ(PHP5対応)

    */

    function __construct()

    {

        //------------------------------------------------------------

        // 絵文字テーブルセット

        //------------------------------------------------------------

        $this->_EmojiTable();





        //------------------------------------------------------------

        // ユーザーエージェントセット

        //------------------------------------------------------------

        $this->_setUserAgent();

    }


    // }}}

    // {{{ getErrorMessage()



    /**

     * <sample>

     * require_once 'MobileClass.php';

     *

     * $MobileClass = new MobileClass();

     *

     * // 引数を指定していない

     * $MobileClass->Convert();

     * $MobileClass->getErrorMessage();

     *

     * </sample>

     *

     * @param

     * @return boolean 直前に発生したエラーに対するエラーメッセージを返却

     *

     * @access public

     */

    function getErrorMessage()

    {

        return $this->strErrorMessage == '' ? false : $this->strErrorMessage;

    }



    // }}}

    // {{{ Convert()



    /**

     * <sample>

     * require_once 'MobileClass.php';

     *

     * $MobileClass = new MobileClass();

     *

     * echo $MobileClass->Convert('F89F');

     *

     * </sample>

     *

     * @param

     * @return string 引数で指定された文字列を、エージェントにあわせた表記方法に変換し返却

     *

     * @access public

     */

    function Convert($InputEmoji = '')

    {

        if ($InputEmoji == '' ) {

            $this->_setErrorMessage(ERROR_MESSAGE_INPUTEMOJI);

            return false;

        }

        switch ($this->InputMode) {

        // バイナリ入力

        case 0:

            $InputEmoji = strtoupper($InputEmoji);

            break;

        // 絵文字入力

        case 1:

            $InputEmoji = strtoupper(bin2hex($InputEmoji));

            break;

        default:

            $this->_setErrorMessage(ERROR_MESSAGE_INPUTMODE);

            return false;

            break;

        }

        if (is_null($this->intUserAgent)) { $this->_setUserAgent(); }

        // 該当絵文字が無かった場合

        if (!array_key_exists($InputEmoji, $this->EMOJI)){

            $this->_setErrorMessage(ERROR_MESSAGE_NOT_EXISTS_TO_DOCOMO);

            return false;

        }

        switch ($this->intUserAgent) {

            case MB_USER_AGENT_DOCOMO:

                // DoCoMo

                $InputEmoji = pack("H*",$InputEmoji);
                $InputEmoji=mb_convert_encoding($InputEmoji, "UTF-8","sjis-win");

                break;

            case MB_USER_AGENT_SOFTBANK:

                // SoftBank

                $InputEmoji = $this->EMOJI[$InputEmoji]['SB_UNI'];

                break;

            case MB_USER_AGENT_EZWEB:

                // EzWeb

                if (preg_match('/^[0-9]{0,3}$/', $this->EMOJI[$InputEmoji]['EzWeb'])) {

                    $InputEmoji = sprintf("<img localsrc=%s />", $this->EMOJI[$InputEmoji]['EzWeb']);

                } else {

                    $InputEmoji = $this->EMOJI[$InputEmoji]['EzWeb'];

                }

                break;

            case MB_USER_AGENT_OTHERS:

                // PC
                $this->_staticUrl = Zend_Registry::get('static');
                $InputEmoji = sprintf('<img src="%s/cmn/img/mobile/emoji/%s.gif" />',$this->_staticUrl,$InputEmoji);

                break;

        }

        return $InputEmoji;

    }



    // }}}

    // {{{ _setErrorMessage()



    /**

     * <sample>

     * $this->_setErrorMessage(ERROR_MESSAGE_INPUTEMOJI);

     *

     * </sample>

     *

     * @param

     * @return

     *

     * @access private

     */

    function _setErrorMessage($strErrorMessage = '')

    {

        $this->strErrorMessage = $strErrorMessage;

    }



    // }}}

    // {{{ _setUserAgent()



    /**

     * <sample>

     * $this->_setUserAgent();

     *

     * </sample>

     *

     * @param

     * @return

     *

     * @access private

     */

    function _setUserAgent()

    {

        $UA = $_SERVER['HTTP_USER_AGENT'];

        if (strstr($UA,'DoCoMo')) {

            $this->intUserAgent = MB_USER_AGENT_DOCOMO;

        } elseif (strstr($UA,'J-PHONE') || strstr($UA,'Vodafone') || strstr($UA, 'SoftBank')) {

            $this->intUserAgent = MB_USER_AGENT_SOFTBANK;

        } elseif (strstr($UA,'UP.Browser')) {

            $this->intUserAgent = MB_USER_AGENT_EZWEB;

        } else {

            $this->intUserAgent = MB_USER_AGENT_OTHERS;

        }

    }



    // }}}

    // {{{ _setUserAgent()



    /**

     * <sample>

     * $this->_EmojiTable();

     *

     * </sample>

     *

     * @param

     * @return

     *

     * @access private

     */

    function _EmojiTable()

    {

        $this->EMOJI['F89F'] = array('TIT' => '晴れ',              'EzWeb' => '44',              'SB' => '$Gj',            'SB_UNI' => '&#xE04A;');

        $this->EMOJI['F8A0'] = array('TIT' => '曇り',              'EzWeb' => '107',             'SB' => '$Gi',            'SB_UNI' => '&#xE049;');

        $this->EMOJI['F8A1'] = array('TIT' => '雨',                'EzWeb' => '95',              'SB' => '$Gk',            'SB_UNI' => '&#xE04B;');

        $this->EMOJI['F8A2'] = array('TIT' => '雪',                'EzWeb' => '191',             'SB' => '$Gh',            'SB_UNI' => '&#xE048;');

        $this->EMOJI['F8A3'] = array('TIT' => '雷',                'EzWeb' => '16',              'SB' => '$E]',            'SB_UNI' => '&#xE13D;');

        $this->EMOJI['F8A4'] = array('TIT' => '台風',              'EzWeb' => '190',             'SB' => '$Pc',            'SB_UNI' => '&#xE443;');

        $this->EMOJI['F8A5'] = array('TIT' => '霧',                'EzWeb' => '305',             'SB' => '[霧]',             'SB_UNI' => '[霧]');

        $this->EMOJI['F8A6'] = array('TIT' => '小雨',              'EzWeb' => '481',             'SB' => '$P\',            'SB_UNI' => '&#xE43C;');

        $this->EMOJI['F8A7'] = array('TIT' => '牡羊座',            'EzWeb' => '192',             'SB' => '$F_',            'SB_UNI' => '&#xE23F;');

        $this->EMOJI['F8A8'] = array('TIT' => '牡牛座',            'EzWeb' => '193',             'SB' => '$F`',            'SB_UNI' => '&#xE240;');

        $this->EMOJI['F8A9'] = array('TIT' => '双子座',            'EzWeb' => '194',             'SB' => '$Fa',            'SB_UNI' => '&#xE241;');

        $this->EMOJI['F8AA'] = array('TIT' => '蟹座',              'EzWeb' => '195',             'SB' => '$Fb',            'SB_UNI' => '&#xE242;');

        $this->EMOJI['F8AB'] = array('TIT' => '獅子座',            'EzWeb' => '196',             'SB' => '$Fc',            'SB_UNI' => '&#xE243;');

        $this->EMOJI['F8AC'] = array('TIT' => '乙女座',            'EzWeb' => '197',             'SB' => '$Fd',            'SB_UNI' => '&#xE244;');

        $this->EMOJI['F8AD'] = array('TIT' => '天秤座',            'EzWeb' => '198',             'SB' => '$Fe',            'SB_UNI' => '&#xE245;');

        $this->EMOJI['F8AE'] = array('TIT' => '蠍座',              'EzWeb' => '199',             'SB' => '$Ff',            'SB_UNI' => '&#xE246;');

        $this->EMOJI['F8AF'] = array('TIT' => '射手座',            'EzWeb' => '200',             'SB' => '$Fg',            'SB_UNI' => '&#xE247;');

        $this->EMOJI['F8B0'] = array('TIT' => '山羊座',            'EzWeb' => '201',             'SB' => '$Fh',            'SB_UNI' => '&#xE248;');

        $this->EMOJI['F8B1'] = array('TIT' => '水瓶座',            'EzWeb' => '202',             'SB' => '$Fi',            'SB_UNI' => '&#xE249;');

        $this->EMOJI['F8B2'] = array('TIT' => '魚座',              'EzWeb' => '203',             'SB' => '$Fj',            'SB_UNI' => '&#xE24A;');

        $this->EMOJI['F8B3'] = array('TIT' => 'スポーツ',          'EzWeb' => '-',               'SB' => '-',                'SB_UNI' => '-');

        $this->EMOJI['F8B4'] = array('TIT' => '野球',              'EzWeb' => '45',              'SB' => '$G6',            'SB_UNI' => '&#xE016;');

        $this->EMOJI['F8B5'] = array('TIT' => 'ゴルフ',            'EzWeb' => '306',             'SB' => '$G4',            'SB_UNI' => '&#xE014;');

        $this->EMOJI['F8B6'] = array('TIT' => 'テニス',            'EzWeb' => '220',             'SB' => '$G5',            'SB_UNI' => '&#xE015;');

        $this->EMOJI['F8B7'] = array('TIT' => 'サッカー',          'EzWeb' => '219',             'SB' => '$G8',            'SB_UNI' => '&#xE018;');

        $this->EMOJI['F8B8'] = array('TIT' => 'スキー',            'EzWeb' => '421',             'SB' => '$G3',            'SB_UNI' => '&#xE013;');

        $this->EMOJI['F8B9'] = array('TIT' => 'バスケットボール',  'EzWeb' => '307',             'SB' => '$PJ',            'SB_UNI' => '&#xE42A;');

        $this->EMOJI['F8BA'] = array('TIT' => 'モータースポーツ',  'EzWeb' => '222',             'SB' => '$ER',            'SB_UNI' => '&#xE132;');

        $this->EMOJI['F8BB'] = array('TIT' => 'ポケットベル',      'EzWeb' => '308',             'SB' => '[PB]',             'SB_UNI' => '[PB]');

        $this->EMOJI['F8BC'] = array('TIT' => '電車',              'EzWeb' => '172',             'SB' => '$G>',            'SB_UNI' => '&#xE01E;');

        $this->EMOJI['F8BD'] = array('TIT' => '地下鉄',            'EzWeb' => '341',             'SB' => '$PT',            'SB_UNI' => '&#xE434;');

        $this->EMOJI['F8BE'] = array('TIT' => '新幹線',            'EzWeb' => '217',             'SB' => '$PU',            'SB_UNI' => '&#xE435;');

        $this->EMOJI['F8BF'] = array('TIT' => '車（セダン）',      'EzWeb' => '125',             'SB' => '$G;',            'SB_UNI' => '&#xE01B;');

        $this->EMOJI['F8C0'] = array('TIT' => '車（ＲＶ）',        'EzWeb' => '125',             'SB' => '$PN',            'SB_UNI' => '&#xE42E;');

        $this->EMOJI['F8C1'] = array('TIT' => 'バス',              'EzWeb' => '216',             'SB' => '$Ey',            'SB_UNI' => '&#xE159;');

        $this->EMOJI['F8C2'] = array('TIT' => '船',                'EzWeb' => '379',             'SB' => '$F"',            'SB_UNI' => '&#xE202;');

        $this->EMOJI['F8C3'] = array('TIT' => '飛行機',            'EzWeb' => '168',             'SB' => '$G=',            'SB_UNI' => '&#xE01D;');

        $this->EMOJI['F8C4'] = array('TIT' => '家',                'EzWeb' => '112',             'SB' => '$GV',            'SB_UNI' => '&#xE036;');

        $this->EMOJI['F8C5'] = array('TIT' => 'ビル',              'EzWeb' => '156',             'SB' => '$GX',            'SB_UNI' => '&#xE038;');

        $this->EMOJI['F8C6'] = array('TIT' => '郵便局',            'EzWeb' => '375',             'SB' => '$Es',            'SB_UNI' => '&#xE153;');

        $this->EMOJI['F8C7'] = array('TIT' => '病院',              'EzWeb' => '376',             'SB' => '$Eu',            'SB_UNI' => '&#xE155;');

        $this->EMOJI['F8C8'] = array('TIT' => '銀行',              'EzWeb' => '212',             'SB' => '$Em',            'SB_UNI' => '&#xE14D;');

        $this->EMOJI['F8C9'] = array('TIT' => 'ＡＴＭ',            'EzWeb' => '205',             'SB' => '$Et',            'SB_UNI' => '&#xE154;');

        $this->EMOJI['F8CA'] = array('TIT' => 'ホテル',            'EzWeb' => '378',             'SB' => '$Ex',            'SB_UNI' => '&#xE158;');

        $this->EMOJI['F8CB'] = array('TIT' => 'コンビニ',          'EzWeb' => '206',             'SB' => '$Ev',            'SB_UNI' => '&#xE156;');

        $this->EMOJI['F8CC'] = array('TIT' => 'ガソリンスタンド',  'EzWeb' => '213',             'SB' => '$GZ',            'SB_UNI' => '&#xE03A;');

        $this->EMOJI['F8CD'] = array('TIT' => '駐車場',            'EzWeb' => '208',             'SB' => '$Eo',            'SB_UNI' => '&#xE14F;');

        $this->EMOJI['F8CE'] = array('TIT' => '信号',              'EzWeb' => '99',              'SB' => '$En',            'SB_UNI' => '&#xE14E;');

        $this->EMOJI['F8CF'] = array('TIT' => 'トイレ',            'EzWeb' => '207',             'SB' => '$Eq',            'SB_UNI' => '&#xE151;');

        $this->EMOJI['F8D0'] = array('TIT' => 'レストラン',        'EzWeb' => '146',             'SB' => '$Gc',            'SB_UNI' => '&#xE043;');

        $this->EMOJI['F8D1'] = array('TIT' => '喫茶店',            'EzWeb' => '93',              'SB' => '$Ge',            'SB_UNI' => '&#xE045;');

        $this->EMOJI['F8D2'] = array('TIT' => 'バー',              'EzWeb' => '52',              'SB' => '$Gd',            'SB_UNI' => '&#xE044;');

        $this->EMOJI['F8D3'] = array('TIT' => 'ビール',            'EzWeb' => '65',              'SB' => '$Gg',            'SB_UNI' => '&#xE047;');

        $this->EMOJI['F8D4'] = array('TIT' => 'ファーストフード',  'EzWeb' => '245',             'SB' => '$E@',            'SB_UNI' => '&#xE120;');

        $this->EMOJI['F8D5'] = array('TIT' => 'ブティック',        'EzWeb' => '124',             'SB' => '$E^',            'SB_UNI' => '&#xE13E;');

        $this->EMOJI['F8D6'] = array('TIT' => '美容院',            'EzWeb' => '104',             'SB' => '$O3',            'SB_UNI' => '&#xE313;');

        $this->EMOJI['F8D7'] = array('TIT' => 'カラオケ',          'EzWeb' => '289',             'SB' => '$G\',            'SB_UNI' => '&#xE03C;');

        $this->EMOJI['F8D8'] = array('TIT' => '映画',              'EzWeb' => '110',             'SB' => '$G]',            'SB_UNI' => '&#xE03D;');

        $this->EMOJI['F8D9'] = array('TIT' => '右斜め上',          'EzWeb' => '70',              'SB' => '$FV',            'SB_UNI' => '&#xE236;');

        $this->EMOJI['F8DA'] = array('TIT' => '遊園地',            'EzWeb' => '-', '              SB' => '-',                'SB' => '-');

        $this->EMOJI['F8DB'] = array('TIT' => '音楽',              'EzWeb' => '294',             'SB' => '$O*',            'SB_UNI' => '&#xE30A;');

        $this->EMOJI['F8DC'] = array('TIT' => 'アート',            'EzWeb' => '309',             'SB' => '$Q"',            'SB_UNI' => '&#xE502;');

        $this->EMOJI['F8DD'] = array('TIT' => '演劇',              'EzWeb' => '494',             'SB' => '$Q#',            'SB_UNI' => '&#xE503;');

        $this->EMOJI['F8DE'] = array('TIT' => 'イベント',          'EzWeb' => '311',             'SB' => '-',                  'SB_UNI' => '-');

        $this->EMOJI['F8DF'] = array('TIT' => 'チケット',          'EzWeb' => '106',             'SB' => '$EE',            'SB_UNI' => '&#xE125;');

        $this->EMOJI['F8E0'] = array('TIT' => '喫煙',              'EzWeb' => '176',             'SB' => '$O.',            'SB_UNI' => '&#xE30E;');

        $this->EMOJI['F8E1'] = array('TIT' => '禁煙',              'EzWeb' => '177',             'SB' => '$F(',            'SB_UNI' => '&#xE208;');

        $this->EMOJI['F8E2'] = array('TIT' => 'カメラ',            'EzWeb' => '94',              'SB' => '$G(',            'SB_UNI' => '&#xE008;');

        $this->EMOJI['F8E3'] = array('TIT' => 'カバン',            'EzWeb' => '83',              'SB' => '$OC',            'SB_UNI' => '&#xE323;');

        $this->EMOJI['F8E4'] = array('TIT' => '本',                'EzWeb' => '122',             'SB' => '$Eh',            'SB_UNI' => '&#xE148;');

        $this->EMOJI['F8E5'] = array('TIT' => 'リボン',            'EzWeb' => '312',             'SB' => '$O4',            'SB_UNI' => '&#xE314;');

        $this->EMOJI['F8E6'] = array('TIT' => 'プレゼント',        'EzWeb' => '144',             'SB' => '$E2',            'SB_UNI' => '&#xE112;');

        $this->EMOJI['F8E7'] = array('TIT' => 'バースデー',        'EzWeb' => '313',             'SB' => '$Ok',            'SB_UNI' => '&#xE34B;');

        $this->EMOJI['F8E8'] = array('TIT' => '電話',              'EzWeb' => '85',              'SB' => '$G)',            'SB_UNI' => '&#xE009;');

        $this->EMOJI['F8E9'] = array('TIT' => '携帯電話',          'EzWeb' => '161',             'SB' => '$G*',            'SB_UNI' => '&#xE00A;');

        $this->EMOJI['F8EA'] = array('TIT' => 'メモ',              'EzWeb' => '395',             'SB' => '$O!',            'SB_UNI' => '&#xE301;');

        $this->EMOJI['F8EB'] = array('TIT' => 'ＴＶ',              'EzWeb' => '288',             'SB' => '$EJ',            'SB_UNI' => '&#xE12A;');

        $this->EMOJI['F8EC'] = array('TIT' => 'ゲーム',            'EzWeb' => '232',             'SB' => '[ゲーム]',         'SB_UNI' => '[ゲーム]');

        $this->EMOJI['F8ED'] = array('TIT' => 'ＣＤ',              'EzWeb' => '300',             'SB' => '$EF',            'SB_UNI' => '&#xE126;');

        $this->EMOJI['F8EE'] = array('TIT' => 'ハート',            'EzWeb' => '414',             'SB' => '$F,',            'SB_UNI' => '&#xE20C;');

        $this->EMOJI['F8EF'] = array('TIT' => 'スペード',          'EzWeb' => '314',             'SB' => '$F.',            'SB_UNI' => '&#xE20E;');

        $this->EMOJI['F8F0'] = array('TIT' => 'ダイヤ',            'EzWeb' => '315',             'SB' => '$F-',            'SB_UNI' => '&#xE20D;');

        $this->EMOJI['F8F1'] = array('TIT' => 'クラブ',            'EzWeb' => '316',             'SB' => '$F/',            'SB_UNI' => '&#xE20F;');

        $this->EMOJI['F8F2'] = array('TIT' => '目',                'EzWeb' => '317',             'SB' => '$P9',            'SB_UNI' => '&#xE419;');

        $this->EMOJI['F8F3'] = array('TIT' => '耳',                'EzWeb' => '318',             'SB' => '$P;',            'SB_UNI' => '&#xE41B;');

        $this->EMOJI['F8F4'] = array('TIT' => '手（グー）',        'EzWeb' => '817',             'SB' => '$G0',            'SB_UNI' => '&#xE010;');

        $this->EMOJI['F8F5'] = array('TIT' => '手（チョキ）',      'EzWeb' => '319',             'SB' => '$G1',            'SB_UNI' => '&#xE011;');

        $this->EMOJI['F8F6'] = array('TIT' => '手（パー）',        'EzWeb' => '320',             'SB' => '$G2',            'SB_UNI' => '&#xE012;');

        $this->EMOJI['F8F7'] = array('TIT' => '右斜め下',          'EzWeb' => '43',              'SB' => '$FX',            'SB_UNI' => '&#xE238;');

        $this->EMOJI['F8F8'] = array('TIT' => '左斜め上',          'EzWeb' => '42',              'SB' => '$FW',            'SB_UNI' => '&#xE237;');

        $this->EMOJI['F8F9'] = array('TIT' => '足',                'EzWeb' => '728',             'SB' => '$QV',            'SB_UNI' => '&#xE536;');

        $this->EMOJI['F8FA'] = array('TIT' => 'くつ',              'EzWeb' => '729',             'SB' => '$G\'',           'SB_UNI' => '&#xE007;');

        $this->EMOJI['F8FB'] = array('TIT' => '眼鏡',              'EzWeb' => '116',             'SB' => '[メガネ]',         'SB_UNI' => '[メガネ]');

        $this->EMOJI['F8FC'] = array('TIT' => '車椅子',            'EzWeb' => '178',             'SB' => '$F*',            'SB_UNI' => '&#xE20A;');

        $this->EMOJI['F940'] = array('TIT' => '新月',              'EzWeb' => '321',             'SB' => '●',                'SB_UNI' => '●');

        $this->EMOJI['F941'] = array('TIT' => 'やや欠け月',        'EzWeb' => '322',             'SB' => '$Gl',            'SB_UNI' => '&#xE04C;');

        $this->EMOJI['F942'] = array('TIT' => '半月',              'EzWeb' => '323',             'SB' => '$Gl',            'SB_UNI' => '&#xE04C;');

        $this->EMOJI['F943'] = array('TIT' => '三日月',            'EzWeb' => '15',              'SB' => '$Gl',            'SB_UNI' => '&#xE04C;');

        $this->EMOJI['F944'] = array('TIT' => '満月',              'EzWeb' => '○',              'SB' => '○',                 'SB_UNI' => '○');

        $this->EMOJI['F945'] = array('TIT' => '犬',                'EzWeb' => '134',             'SB' => '$Gr',            'SB_UNI' => '&#xE052;');

        $this->EMOJI['F946'] = array('TIT' => '猫',                'EzWeb' => '251',             'SB' => '$Go',            'SB_UNI' => '&#xE04F;');

        $this->EMOJI['F947'] = array('TIT' => 'リゾート',          'EzWeb' => '169',             'SB' => '$G<',            'SB_UNI' => '&#xE01C;');

        $this->EMOJI['F948'] = array('TIT' => 'クリスマス',        'EzWeb' => '234',             'SB' => '$GS',            'SB_UNI' => '&#xE033;');

        $this->EMOJI['F949'] = array('TIT' => '左斜め下',          'EzWeb' => '71',              'SB' => '$FY',            'SB_UNI' => '&#xE239;');

        $this->EMOJI['F950'] = array('TIT' => 'カチンコ',          'EzWeb' => '226',             'SB' => '$OD',            'SB_UNI' => '&#xE324;');

        $this->EMOJI['F951'] = array('TIT' => 'ふくろ',            'EzWeb' => '[ふくろ]',        'SB' => '[ふくろ]',         'SB_UNI' => '[ふくろ]');

        $this->EMOJI['F952'] = array('TIT' => 'ペン',              'EzWeb' => '508',             'SB' => '［ペン］',         'SB_UNI' => '［ペン］');

        $this->EMOJI['F955'] = array('TIT' => '人影',              'EzWeb' => '-',               'SB' => '-',            'SB_UNI' => '-');

        $this->EMOJI['F956'] = array('TIT' => 'いす',              'EzWeb' => '[いす]',          'SB' => '$E?',            'SB_UNI' => '&#xE11F;');

        $this->EMOJI['F957'] = array('TIT' => '夜',                'EzWeb' => '490',             'SB' => '$Pk',            'SB_UNI' => '&#xE44B;');

        $this->EMOJI['F95E'] = array('TIT' => '時計',              'EzWeb' => '46',              'SB' => '$GM',            'SB_UNI' => '&#xE02D;');

        $this->EMOJI['F972'] = array('TIT' => 'phone to',          'EzWeb' => '513',             'SB' => '$E$',            'SB_UNI' => '&#xE104;');

        $this->EMOJI['F973'] = array('TIT' => 'mail to',           'EzWeb' => '784',             'SB' => '$E#',            'SB_UNI' => '&#xE103;');

        $this->EMOJI['F974'] = array('TIT' => 'fax to',            'EzWeb' => '166',             'SB' => '$G+',            'SB_UNI' => '&#xE00B;');

        $this->EMOJI['F975'] = array('TIT' => 'iモード',           'EzWeb' => '[iモード]',       'SB' => '[iモード]',        'SB_UNI' => '[iモード]');

        $this->EMOJI['F976'] = array('TIT' => 'iモード（枠付き）', 'EzWeb' => '[iモード]',       'SB' => '[iモード]',        'SB_UNI' => '[iモード]');

        $this->EMOJI['F977'] = array('TIT' => 'メール',            'EzWeb' => '108',             'SB' => '$E#',            'SB_UNI' => '&#xE103;');

        $this->EMOJI['F978'] = array('TIT' => 'ドコモ提供',        'EzWeb' => '[ドコモ]',        'SB' => '[ドコモ]',         'SB_UNI' => '[ドコモ]');

        $this->EMOJI['F979'] = array('TIT' => 'ドコモポイント',    'EzWeb' => '[DP]',            'SB' => '[DP]',            'SB_UNI' => '[DP]');

        $this->EMOJI['F97A'] = array('TIT' => '有料',              'EzWeb' => '109',             'SB' => '￥',          'SB_UNI' => '￥');

        $this->EMOJI['F97B'] = array('TIT' => '無料',              'EzWeb' => '299',             'SB' => '［ＦＲＥＥ］',     'SB_UNI' => '［ＦＲＥＥ］');

        $this->EMOJI['F97D'] = array('TIT' => 'パスワード',        'EzWeb' => '120',             'SB' => '$G_',            'SB_UNI' => '&#xE03F;');

        $this->EMOJI['F97E'] = array('TIT' => '次項有',            'EzWeb' => '118',             'SB' => '-',           'SB_UNI' => '-');

        $this->EMOJI['F980'] = array('TIT' => 'クリア',            'EzWeb' => '324',             'SB' => '[CL]',           'SB_UNI' => '[CL]');

        $this->EMOJI['F981'] = array('TIT' => 'サーチ（調べる）',  'EzWeb' => '119',             'SB' => '$E4',            'SB_UNI' => '&#xE114;');

        $this->EMOJI['F982'] = array('TIT' => 'ＮＥＷ',            'EzWeb' => '334',             'SB' => '$F2',            'SB_UNI' => '&#xE212;');

        $this->EMOJI['F983'] = array('TIT' => '位置情報',          'EzWeb' => '730',             'SB' => '-',              'SB_UNI' => '-');

        $this->EMOJI['F984'] = array('TIT' => 'フリーダイヤル',    'EzWeb' => '[FD]',            'SB' => '$F1',            'SB_UNI' => '&#xE211;');

        $this->EMOJI['F985'] = array('TIT' => 'シャープダイヤル',  'EzWeb' => '818',             'SB' => '$F0',            'SB_UNI' => '&#xE210;');

        $this->EMOJI['F986'] = array('TIT' => 'モバＱ',            'EzWeb' => '4',               'SB' => '[Q]',           'SB_UNI' => '[Q]');

        $this->EMOJI['F987'] = array('TIT' => '1',                 'EzWeb' => '180',             'SB' => '$F<',            'SB_UNI' => '&#xE21C;');

        $this->EMOJI['F988'] = array('TIT' => '2',                 'EzWeb' => '181',             'SB' => '$F=',            'SB_UNI' => '&#xE21D;');

        $this->EMOJI['F989'] = array('TIT' => '3',                 'EzWeb' => '182',             'SB' => '$F>',            'SB_UNI' => '&#xE21E;');

        $this->EMOJI['F98A'] = array('TIT' => '4',                 'EzWeb' => '183',             'SB' => '$F?',            'SB_UNI' => '&#xE21F;');

        $this->EMOJI['F98B'] = array('TIT' => '5',                 'EzWeb' => '184',             'SB' => '$F@',            'SB_UNI' => '&#xE220;');

        $this->EMOJI['F98C'] = array('TIT' => '6',                 'EzWeb' => '185',             'SB' => '$FA',            'SB_UNI' => '&#xE221;');

        $this->EMOJI['F98D'] = array('TIT' => '7',                 'EzWeb' => '186',             'SB' => '$FB',            'SB_UNI' => '&#xE222;');

        $this->EMOJI['F98E'] = array('TIT' => '8',                 'EzWeb' => '187',             'SB' => '$FC',            'SB_UNI' => '&#xE223;');

        $this->EMOJI['F98F'] = array('TIT' => '9',                 'EzWeb' => '188',             'SB' => '$FD',            'SB_UNI' => '&#xE224;');

        $this->EMOJI['F990'] = array('TIT' => '0',                 'EzWeb' => '325',             'SB' => '$FE',            'SB_UNI' => '&#xE225;');

        $this->EMOJI['F9B0'] = array('TIT' => '決定',              'EzWeb' => '326',             'SB' => '$Fm',            'SB_UNI' => '&#xE24D;');

        $this->EMOJI['F991'] = array('TIT' => '黒ハート',          'EzWeb' => '51',              'SB' => '$GB',            'SB_UNI' => '&#xE022;');

        $this->EMOJI['F993'] = array('TIT' => '失恋',              'EzWeb' => '265',             'SB' => '$GC',            'SB_UNI' => '&#xE023;');

        $this->EMOJI['F994'] = array('TIT' => 'ハートたち',        'EzWeb' => '266',             'SB' => '$OG',            'SB_UNI' => '&#xE327;');

        $this->EMOJI['F995'] = array('TIT' => 'わーい',            'EzWeb' => '257',             'SB' => '$Gw',            'SB_UNI' => '&#xE057;');

        $this->EMOJI['F996'] = array('TIT' => 'ちっ',              'EzWeb' => '258',             'SB' => '$Gy',            'SB_UNI' => '&#xE059;');

        $this->EMOJI['F997'] = array('TIT' => 'がく～',            'EzWeb' => '441',             'SB' => '$Gx',            'SB_UNI' => '&#xE058;');

        $this->EMOJI['F998'] = array('TIT' => 'もうやだ～',        'EzWeb' => '444',             'SB' => '$P\'',           'SB_UNI' => '&#xE407;');

        $this->EMOJI['F999'] = array('TIT' => 'ふらふら',          'EzWeb' => '327',             'SB' => '$P&',            'SB_UNI' => '&#xE406;');

        $this->EMOJI['F99A'] = array('TIT' => 'グッド',            'EzWeb' => '731',             'SB' => '$FV',            'SB_UNI' => '&#xE236;');

        $this->EMOJI['F99B'] = array('TIT' => 'るんるん',          'EzWeb' => '343',             'SB' => '$G^',            'SB_UNI' => '&#xE03E;');

        $this->EMOJI['F99C'] = array('TIT' => 'いい気分',          'EzWeb' => '224',             'SB' => '$EC',            'SB_UNI' => '&#xE123;');

        $this->EMOJI['F99D'] = array('TIT' => 'かわいい',          'EzWeb' => '-',               'SB' => '-',                'SB_UNI' => '-');

        $this->EMOJI['F99E'] = array('TIT' => 'キスマーク',        'EzWeb' => '273',             'SB' => '$G#',            'SB_UNI' => '&#xE003;');

        $this->EMOJI['F99F'] = array('TIT' => 'ぴかぴか',          'EzWeb' => '420',             'SB' => '$ON',            'SB_UNI' => '&#xE32E;');

        $this->EMOJI['F9A0'] = array('TIT' => 'ひらめき',          'EzWeb' => '77',              'SB' => '$E/',            'SB_UNI' => '&#xE10F;');

        $this->EMOJI['F9A1'] = array('TIT' => 'むかっ',            'EzWeb' => '262',             'SB' => '$OT',            'SB_UNI' => '&#xE334;');

        $this->EMOJI['F9A2'] = array('TIT' => 'パンチ',            'EzWeb' => '281',             'SB' => '$G-',            'SB_UNI' => '&#xE00D;');

        $this->EMOJI['F9A3'] = array('TIT' => '爆弾',              'EzWeb' => '268',             'SB' => '$O1',            'SB_UNI' => '&#xE311;');

        $this->EMOJI['F9A4'] = array('TIT' => 'ムード',            'EzWeb' => '291',             'SB' => '$OF',            'SB_UNI' => '&#xE326;');

        $this->EMOJI['F9A5'] = array('TIT' => 'バッド',            'EzWeb' => '732',             'SB' => '$FX',            'SB_UNI' => '&#xE238;');

        $this->EMOJI['F9A6'] = array('TIT' => '眠い(睡眠)',        'EzWeb' => '261',             'SB' => '$E\',            'SB_UNI' => '&#xE13C;');

        $this->EMOJI['F9A7'] = array('TIT' => '！',                'EzWeb' => '2',               'SB' => '$GA',            'SB_UNI' => '&#xE021;');

        $this->EMOJI['F9A8'] = array('TIT' => '！？',              'EzWeb' => '733',             'SB' => '！？',            'SB_UNI' => '！？');

        $this->EMOJI['F9A9'] = array('TIT' => '！！',              'EzWeb' => '734',             'SB' => '！！',            'SB_UNI' => '！！');

        $this->EMOJI['F9AA'] = array('TIT' => 'どんっ（衝撃）',    'EzWeb' => '329',             'SB' => '-',                  'SB_UNI' => '-');

        $this->EMOJI['F9AB'] = array('TIT' => 'あせあせ',          'EzWeb' => '330',             'SB' => '$OQ',            'SB_UNI' => '&#xE331;');

        $this->EMOJI['F9AC'] = array('TIT' => 'たらーっ',          'EzWeb' => '263',             'SB' => '$OQ',            'SB_UNI' => '&#xE331;');

        $this->EMOJI['F9AD'] = array('TIT' => 'ダッシュ',          'EzWeb' => '282',             'SB' => '$OP',            'SB_UNI' => '&#xE330;');

        $this->EMOJI['F9AE'] = array('TIT' => 'ー（長音記号１）',  'EzWeb' => '-',               'SB' => '-',                   'SB_UNI' => '-');

        $this->EMOJI['F9AF'] = array('TIT' => 'ー（長音記号２）',  'EzWeb' => '735',             'SB' => '-',                   'SB_UNI' => '-');

        $this->EMOJI['F9B1'] = array('TIT' => 'iアプリ',           'EzWeb' => '[ｉアプリ]',      'SB' => '[ｉアプリ]',       'SB_UNI' => '[ｉアプリ]');

        $this->EMOJI['F9B2'] = array('TIT' => 'iアプリ（枠付き）', 'EzWeb' => '[ｉアプリ]',      'SB' => '[ｉアプリ]',       'SB_UNI' => '[ｉアプリ]');

        $this->EMOJI['F9B3'] = array('TIT' => 'Tシャツ',           'EzWeb' => '335',             'SB' => '$G&',            'SB_UNI' => '&#xE006;');

        $this->EMOJI['F9B4'] = array('TIT' => 'がま口財布',        'EzWeb' => '290',             'SB' => '[財布]',           'SB_UNI' => '[財布]');

        $this->EMOJI['F9B5'] = array('TIT' => '化粧',              'EzWeb' => '295',             'SB' => '$O<',            'SB_UNI' => '&#xE31C;');

        $this->EMOJI['F9B6'] = array('TIT' => 'ジーンズ',          'EzWeb' => '805',             'SB' => '[ジーンズ]',       'SB_UNI' => '[ジーンズ]');

        $this->EMOJI['F9B7'] = array('TIT' => 'スノボ',            'EzWeb' => '221',             'SB' => '[スノボ]',         'SB_UNI' => '[スノボ]');

        $this->EMOJI['F9B8'] = array('TIT' => 'チャペル',          'EzWeb' => '48',              'SB' => '$OE',            'SB_UNI' => '&#xE325;');

        $this->EMOJI['F9B9'] = array('TIT' => 'ドア',              'EzWeb' => '[ドア]',          'SB' => '[ドア]',           'SB_UNI' => '[ドア]');

        $this->EMOJI['F9BA'] = array('TIT' => 'ドル袋',            'EzWeb' => '233',             'SB' => '$EO',            'SB_UNI' => '&#xE12F;');

        $this->EMOJI['F9BB'] = array('TIT' => 'パソコン',          'EzWeb' => '337',             'SB' => '$G,',            'SB_UNI' => '&#xE00C;');

        $this->EMOJI['F9BC'] = array('TIT' => 'ラブレター',        'EzWeb' => '806',             'SB' => '$E#',            'SB_UNI' => '&#xE103;');

        $this->EMOJI['F9BD'] = array('TIT' => 'レンチ',            'EzWeb' => '152',             'SB' => '[レンチ]',         'SB_UNI' => '[レンチ]');

        $this->EMOJI['F9BE'] = array('TIT' => '鉛筆',              'EzWeb' => '149',             'SB' => '$O!',            'SB_UNI' => '&#xE301;');

        $this->EMOJI['F9BF'] = array('TIT' => '王冠',              'EzWeb' => '354',             'SB' => '$E.',            'SB_UNI' => '&#xE10E;');

        $this->EMOJI['F9C0'] = array('TIT' => '指輪',              'EzWeb' => '72',              'SB' => '$GT',            'SB_UNI' => '&#xE034;');

        $this->EMOJI['F9C1'] = array('TIT' => '砂時計',            'EzWeb' => '58',              'SB' => '[砂時計]',         'SB_UNI' => '[砂時計]');

        $this->EMOJI['F9C2'] = array('TIT' => '自転車',            'EzWeb' => '215',             'SB' => '$EV',            'SB_UNI' => '&#xE136;');

        $this->EMOJI['F9C3'] = array('TIT' => '湯のみ',            'EzWeb' => '423',             'SB' => '$OX',            'SB_UNI' => '&#xE338;');

        $this->EMOJI['F9C4'] = array('TIT' => '腕時計',            'EzWeb' => '25',              'SB' => '[腕時計]',         'SB_UNI' => '[腕時計]');

        $this->EMOJI['F9C5'] = array('TIT' => '考えてる顔',        'EzWeb' => '441',             'SB' => '$P#',            'SB_UNI' => '&#xE403;');

        $this->EMOJI['F9C6'] = array('TIT' => 'ほっとした顔',      'EzWeb' => '446',             'SB' => '$P*',            'SB_UNI' => '&#xE40A;');

        $this->EMOJI['F9C7'] = array('TIT' => '冷や汗',            'EzWeb' => '257',             'SB' => '$P5',            'SB_UNI' => '&#xE415;');

        $this->EMOJI['F9C8'] = array('TIT' => '冷や汗2',           'EzWeb' => '351',             'SB' => '$E(',            'SB_UNI' => '&#xE108;');

        $this->EMOJI['F9C9'] = array('TIT' => 'ぷっくっくな顔',    'EzWeb' => '779',             'SB' => '$P6',            'SB_UNI' => '&#xE416;');

        $this->EMOJI['F9CA'] = array('TIT' => 'ボケーっとした顔',  'EzWeb' => '450',             'SB' => '$P.',            'SB_UNI' => '&#xE40E;');

        $this->EMOJI['F9CB'] = array('TIT' => '目がハート',        'EzWeb' => '349',             'SB' => '$E&',            'SB_UNI' => '&#xE106;');

        $this->EMOJI['F9CC'] = array('TIT' => '指でOK',            'EzWeb' => '287',             'SB' => '$G.',            'SB_UNI' => '&#xE00E;');

        $this->EMOJI['F9CD'] = array('TIT' => 'あっかんべー',      'EzWeb' => '264',             'SB' => '$E%',            'SB_UNI' => '&#xE105;');

        $this->EMOJI['F9CE'] = array('TIT' => 'ウィンク',          'EzWeb' => '348',             'SB' => '$P%',            'SB_UNI' => '&#xE405;');

        $this->EMOJI['F9CF'] = array('TIT' => 'うれしい顔',        'EzWeb' => '446',             'SB' => '$P*',            'SB_UNI' => '&#xE40A;');

        $this->EMOJI['F9D0'] = array('TIT' => 'がまん顔',          'EzWeb' => '443',             'SB' => '$P&',            'SB_UNI' => '&#xE406;');

        $this->EMOJI['F9D1'] = array('TIT' => '猫2',               'EzWeb' => '440',             'SB' => '$P"',            'SB_UNI' => '&#xE402;');

        $this->EMOJI['F9D2'] = array('TIT' => '泣き顔',            'EzWeb' => '259',             'SB' => '$P1',            'SB_UNI' => '&#xE411;');

        $this->EMOJI['F9D3'] = array('TIT' => '涙',                'EzWeb' => '791',             'SB' => '$P3',            'SB_UNI' => '&#xE413;');

        $this->EMOJI['F9D4'] = array('TIT' => 'NG',                'EzWeb' => '[ＮＧ]',          'SB' => '[ＮＧ]',           'SB_UNI' => '[ＮＧ]');

        $this->EMOJI['F9D5'] = array('TIT' => 'クリップ',          'EzWeb' => '143',             'SB' => '[クリップ]',       'SB_UNI' => '[クリップ]');

        $this->EMOJI['F9D6'] = array('TIT' => 'コピーライト',      'EzWeb' => '81',              'SB' => '$Fn',            'SB_UNI' => '&#xE24E;');

        $this->EMOJI['F9D7'] = array('TIT' => 'トレードマーク',    'EzWeb' => '54',              'SB' => '$QW',            'SB_UNI' => '&#xE537;');

        $this->EMOJI['F9D8'] = array('TIT' => '走る人',            'EzWeb' => '218',             'SB' => '$E5',            'SB_UNI' => '&#xE115;');

        $this->EMOJI['F9D9'] = array('TIT' => 'マル秘',            'EzWeb' => '279',             'SB' => '$O5',            'SB_UNI' => '&#xE315;');

        $this->EMOJI['F9DA'] = array('TIT' => 'リサイクル',        'EzWeb' => '807',             'SB' => '-',                  'SB_UNI' => '-');

        $this->EMOJI['F9DB'] = array('TIT' => 'トレードマーク',    'EzWeb' => '82',              'SB' => '$Fo',            'SB_UNI' => '&#xE24F;');

        $this->EMOJI['F9DC'] = array('TIT' => '危険・警告',        'EzWeb' => '1',               'SB' => '$Fr',            'SB_UNI' => '&#xE252;');

        $this->EMOJI['F9DD'] = array('TIT' => '禁止',              'EzWeb' => '[禁]',            'SB' => '[禁]',            'SB_UNI' => '[禁]');

        $this->EMOJI['F9DE'] = array('TIT' => '空室・空席・空車',  'EzWeb' => '387',             'SB' => '$FK',            'SB_UNI' => '&#xE22B;');

        $this->EMOJI['F9DF'] = array('TIT' => '合格マーク',        'EzWeb' => '[合]',            'SB' => '[合]',            'SB_UNI' => '[合]');

        $this->EMOJI['F9E0'] = array('TIT' => '満室・満席・満車',  'EzWeb' => '386',             'SB' => '$FJ',            'SB_UNI' => '&#xE22A;');

        $this->EMOJI['F9E1'] = array('TIT' => '矢印左右',          'EzWeb' => '808',             'SB' => '⇔',            'SB_UNI' => '⇔');

        $this->EMOJI['F9E2'] = array('TIT' => '矢印上下',          'EzWeb' => '809',             'SB' => '-',            'SB_UNI' => '-');

        $this->EMOJI['F9E3'] = array('TIT' => '学校',              'EzWeb' => '377',             'SB' => '$Ew',            'SB_UNI' => '&#xE157;');

        $this->EMOJI['F9E4'] = array('TIT' => '波',                'EzWeb' => '810',             'SB' => '$P^',            'SB_UNI' => '&#xE43E;');

        $this->EMOJI['F9E5'] = array('TIT' => '富士山',            'EzWeb' => '342',             'SB' => '$G[',            'SB_UNI' => '&#xE03B;');

        $this->EMOJI['F9E6'] = array('TIT' => 'クローバー',        'EzWeb' => '53',              'SB' => '$E0',            'SB_UNI' => '&#xE110;');

        $this->EMOJI['F9E7'] = array('TIT' => 'さくらんぼ',        'EzWeb' => '241',             'SB' => '[チェリー]',       'SB_UNI' => '[チェリー]');

        $this->EMOJI['F9E8'] = array('TIT' => 'チューリップ',      'EzWeb' => '113',             'SB' => '$O$',            'SB_UNI' => '&#xE304;');

        $this->EMOJI['F9E9'] = array('TIT' => 'バナナ',            'EzWeb' => '739',             'SB' => '[バナナ]',         'SB_UNI' => '[バナナ]');

        $this->EMOJI['F9EA'] = array('TIT' => 'りんご',            'EzWeb' => '434',             'SB' => '$Oe',            'SB_UNI' => '&#xE345;');

        $this->EMOJI['F9EB'] = array('TIT' => '芽',                'EzWeb' => '811',             'SB' => '$E0',            'SB_UNI' => '&#xE110;');

        $this->EMOJI['F9EC'] = array('TIT' => 'もみじ',            'EzWeb' => '133',             'SB' => '$E8',            'SB_UNI' => '&#xE118;');

        $this->EMOJI['F9ED'] = array('TIT' => '桜',                'EzWeb' => '235',             'SB' => '$GP',            'SB_UNI' => '&#xE030;');

        $this->EMOJI['F9EE'] = array('TIT' => 'おにぎり',          'EzWeb' => '244',             'SB' => '$Ob',            'SB_UNI' => '&#xE342;');

        $this->EMOJI['F9EF'] = array('TIT' => 'ショートケーキ',    'EzWeb' => '239',             'SB' => '$Gf',            'SB_UNI' => '&#xE046;');

        $this->EMOJI['F9F0'] = array('TIT' => 'とっくり',          'EzWeb' => '400',             'SB' => '$O+',            'SB_UNI' => '&#xE30B;');

        $this->EMOJI['F9F1'] = array('TIT' => 'どんぶり',          'EzWeb' => '333',             'SB' => '$O`',            'SB_UNI' => '&#xE340;');

        $this->EMOJI['F9F2'] = array('TIT' => 'パン',              'EzWeb' => '424',             'SB' => '$OY',            'SB_UNI' => '&#xE339;');

        $this->EMOJI['F9F3'] = array('TIT' => 'かたつむり',        'EzWeb' => '812',             'SB' => '[カタツムリ]',     'SB_UNI' => '[カタツムリ]');

        $this->EMOJI['F9F4'] = array('TIT' => 'ひよこ',            'EzWeb' => '78',              'SB' => '$QC',            'SB_UNI' => '&#xE523;');

        $this->EMOJI['F9F5'] = array('TIT' => 'ペンギン',          'EzWeb' => '252',             'SB' => '$Gu',            'SB_UNI' => '&#xE055;');

        $this->EMOJI['F9F6'] = array('TIT' => '魚',                'EzWeb' => '203',             'SB' => '$G9',            'SB_UNI' => '&#xE019;');

        $this->EMOJI['F9F7'] = array('TIT' => 'うまい！',          'EzWeb' => '454',             'SB' => '$Gv',            'SB_UNI' => '&#xE056;');

        $this->EMOJI['F9F8'] = array('TIT' => 'ウッシッシ',        'EzWeb' => '814',             'SB' => '$P$',            'SB_UNI' => '&#xE404;');

        $this->EMOJI['F9F9'] = array('TIT' => 'ウマ',              'EzWeb' => '248',             'SB' => '$G:',            'SB_UNI' => '&#xE01A;');

        $this->EMOJI['F9FA'] = array('TIT' => 'ブタ',              'EzWeb' => '254',             'SB' => '$E+',            'SB_UNI' => '&#xE10B;');

        $this->EMOJI['F9FB'] = array('TIT' => 'ワイングラス',      'EzWeb' => '12',              'SB' => '$Gd',            'SB_UNI' => '&#xE044;');

        $this->EMOJI['F9FC'] = array('TIT' => 'げっそり',          'EzWeb' => '350',             'SB' => '$E\'',           'SB_UNI' => '&#xE107;');

    }



    // }}}



}



// }}}





/*

 * Local Variables:

 * mode: php

 * coding: iso-8859-1

 * tab-width: 4

 * c-basic-offset: 4

 * c-hanging-comment-ender-p: nil

 * indent-tabs-mode: nil

 * End:

 */

?>

