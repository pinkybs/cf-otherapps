<?php

class Bll_Dynamite_Activity
{
    /**
     * get activity content
     *
     * @param string $actor
     * @param string $target
     * @param array $json_array
     * @param integer $type
     * @return string
     */
    public static function getActivity($actor, $target, $type, $message = null)
    {
        require_once 'Bll/User.php';
        $actor = Bll_User::getPerson($actor);

        $json_array = array();

        if (empty($actor)) {
            $actor_name = "____";
        }
        else {
            $actor_name = $actor->getDisplayName();
        }
        $json_array['actor'] = $actor_name;

        if ($target) {
            $targ = Bll_User::getPerson($target);

            if (empty($targ)) {
                $target_name = "____";
            }
            else {
                $target_name = $targ->getDisplayName();
            }
            $json_array['target'] = $target_name ;
        }

        /*$failMessage = array('0' => '覚えてろよー',
                             '1' => '報復してやるからなー',
                             '2' => 'ボッコボコにしてやるからなー',
                             '3' => 'ぜんぜん痛くないもんねー',
                             '4' => 'ハエがうるさいんだけど');*/
        switch ($type) {
            case 1 :
                $template = "{*target*}組にダイナマイト設置";
                break;
            case 2 :
                $template = "{*target*}組のヒットマンを爆破";
                break;
            case 3 :
                $template = "{*target*}組のヒットマンを爆破";
                break;
            case 4 :
                $template = 'ダイナマイト撤去失敗';//$failMessage[rand(0, 4)]
                break;
            case 5 :
                $template = "ダイナマイト撤去失敗";
                break;
            case 6 :
                $template = 'ダイナマイト撤去成功';
                break;
            case 7 :
                $template = "奇跡の雨を降らせました";
                break;
            case 8 :
            	$template = '禁断の兵器を使用しました';
            	break;
            case 9 :
                $template = '奇跡の雨を降らせました';
                break;
            case 10 :
                $template = '神々の怒りを身に纏いました';
                break;
            case 11 :
                $template = 'アジトがレベルUP！！';
                break;
            case 12 :
                $template = '新しいヒットマンを雇用しました';
                break;
            case 13 :
                $template = '{*actor*}組が仕掛けたダイナマイトを撤去しました';
                break;
            case 14 :
                $template = '最強のヒットマンを召還しました';
                break;

            default:;
        }

        foreach ($json_array as $k => $v) {
            $keys[] = '{*' . $k . '*}';
            $values[] = $v;
        }

        return str_replace($keys, $values, $template);
    }
}