<?php

class Bll_Disney_Activity
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

        switch ($type) {
            case 1 :
                $template = "{*actor*}さんが「ディズニーご当地コレクション」を追加しました";
                break;
            case 2 :
                $template = "{*actor*}さんが{character_name}をGET!";
                break;
            case 3 :
                $template = "{*actor*}さんが{awardname}を受賞!";
                break;
            case 4 :
                $template = '{nickname}さんからﾄﾚｰﾄﾞﾘｸｴｽﾄがあります';
                break;
            case 5 :
                $template = "{character_name}と{character_name}のﾄﾚｰﾄﾞが成立!";
                break;
            case 6 :
                $template = '{character_name}と{character_name}のﾄﾚｰﾄﾞが不成立しました';
                break;
            case 7 :
                $template = "{nickname}さんからﾌﾟﾚｾﾞﾝﾄが届きました";
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