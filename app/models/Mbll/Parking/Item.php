<?php

require_once 'Mbll/Abstract.php';

/**
 * item Operation
 *
 * @package    Mbll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/05/19    lp
 */
class Mbll_Parking_Item extends Mbll_Abstract
{
	/**
     * use item over
     *
     * @param integer $itemId
     * @return String
     */
	public function useItemOver($itemId, $result)
	{
		$message = '';

	    switch ($itemId) {
            case 1:
                if ($result == 1) {
                    $message = '有料駐車場カードを使用しました。無料区画が有料になりました。';
                }
                else {
                    $message = 'システムエラー';
                }
                break;
            case 3:
                if ($result == 1) {
                    $message = 'わいろカードを使用しました。使用後72時間、警察からの取り締まりが免除されます。';
                }
                else if ($result == -2) {
                    $message = 'このカードは、現在「使用中」なので、重複して使用する事はできません。';
                }
                else {
                    $message = 'システムエラー';
                }
                break;
            case 5:
                if ($result == 1) {
                    $message = '廃車カードを駐車場に設置しました。';
                }
                else if ($result == -2) {
                    $message = 'このカードを使える区画がありません。';
                }
                else {
                    $message = 'システムエラー';
                }
                break;
            case 6:
                if ($result == 1) {
                    $message = '検問カードを使用しました。';
                }
                else if ($result == -2) {
                    $message = 'このカードは、現在「使用中」なので、重複して使用する事はできません。';
                }
                else {
                    $message = 'システムエラー';
                }
                break;
            case 7:
                if ($result == 1) {
                    $message = '自動車保険カードを使用しました。あなたの所有車を損害から一度だけ守ってくれます。';
                }
                else if ($result == 2) {
                    $message = 'このカードは、現在「使用中」なので、重複して使用する事はできません。';
                }
                else {
                    $message = 'システムエラー';
                }
                break;
            case 8:
                if ($result == 1) {
                    $message = 'トラップ回避カードを使用しました。';
                }
                else if ($result == -2) {
                    $message = 'このカードは、現在「使用中」なので、重複して使用する事はできません。';
                }
                else {
                    $message = 'システムエラー';
                }
                break;
            case 9:
                if ($result == 1) {
                    $message = '警備員カードを使用しました。ヤンキーを駐車場から見事に撃退しました。';
                }
                else if ($result == -2) {
                    $message = 'ヤンキーがいないので、警備員カードを使えません。';
                }
                else {
                    $message = 'システムエラー';
                }
                break;
            case 10:
                if ($result == 1) {
                    $message = '廃車を整備して乗れるようにしました。';
                }
                else {
                    $message = 'システムエラー';
                }
                break;
            case 11:
                if ($result == 1) {
                    $message = 'ヤンキーカードを使用しました。この区画は72時間駐車できなくなりました。';
                }
                else if ($result == -2) {
                    $message = 'このカードを使える区画がありません。';
                }
                else {
                    $message = 'システムエラー';
                }
                break;
            default:
                break;
        }
        return $message;
	}
}