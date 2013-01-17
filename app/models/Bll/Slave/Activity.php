<?php

class Bll_Slave_Activity
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
			case 2 :
				$template = "{*target*}をドレイちゃんに追加しました";
				break;
			case 3 :
				$template = "ドレイちゃん{*target*}をポイ捨てしました";
				break;
			case 4 :
				$template = "{*target*}に革命を起こしました";
				break;
			case 5 :
				$template = "革命に成功し、{*target*}をドレイちゃんにしました";
				break;
            case 11 :
		        $template = "{*actor*}が、{*target*}をドレイちゃんに追加しました";
		        break;
			case 12 :
			    $template = "{*actor*}が、ドレイちゃん{*target*}をポイ捨てしました";
			    break;
			case 13 :
			    $template = "{*actor*}が革命に成功し、{*target*}をドレイちゃんにしました";
			    break;
			case 14 :
			    $template = "{*target*}の革命を阻止し、所持金を没収しました";
			    break;

			case 15 :
                $template = "{*target*}にチョッカイをしちゃいました";
                break;
            case 16 :
                $template = "{*actor*}にチョッカイされちゃいました";
                break;
            case 17 :
                $template = "{*actor*}が{*target*}にチョッカイをしちゃいました";
                break;
            case 18 :
                $template = "{*actor*}の職業レベルが上がりました";
                break;
            case 19 :
                $template = "{*actor*}に屈辱的なニックネームをつけられました";
                break;
            case 20 :
                $template = "{*actor*}が{*target*}に屈辱的なニックネームをつけました";
                break;
            case 21 :
                $template = "{*target*}にギフトを贈りました";
                break;
            case 22 :
                $template = "ギフトを購入しました";
                break;
            case 23 :
                $template = "ギフトを売却しました";
                break;
            case 24 :
                $template = "{*target*}に強制労働させました";
                break;
            case 25 :
                $template = "{*target*}に屈辱的なニックネームを与えました";
                break;
            case 26 :
                $template = "{*actor*}が{*target*}にXXXを…!!";
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