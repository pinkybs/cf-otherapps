<?php

class Bll_Chat_Activity
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
	public static function getActivity($actor,$target,$json_array,$type)
	{
		require_once 'Bll/User.php';
		if ($actor) {
    		$actor = Bll_User::getPerson($actor);

    		if (empty($actor)) {
    			$actor_name = "____";
    		}
    		else {
    			$actor_name = $actor->getDisplayName();
    		}
    		$json_array['actor'] = $actor_name;
		}

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
				$template = "チャットの招待状を送りました。";
				break;
			case 2 :
				$template = "{*chat_name*}に出席表明しました。";
				break;
			case 3 :
				$template = "{*chat_name*}の開催時刻を変更しました。";
				break;
			case 4 :
				$template = "{*chat_name*}の開催を取り消しました。";
				break;
			case 5 :
				$template = "{*chat_name*}に欠席表明しました。";
				break;
			case 6 :
				$template = "あと15分で、{*chat_name*}開始の時間です。";
				break;
			case 7 :
				$template = "{*chat_name*}を開始しました！";
				break;
			default:;
		}

		if ($json_array) {
    		foreach ($json_array as $k => $v) {
                $keys[] = '{*' . $k . '*}';
                $values[] = $v;
            }
            $template = str_replace($keys, $values, $template);
		}
        return $template;
	}
}