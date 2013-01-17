<?php

class Bll_Chomeboard_Activity
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
	public static function getActivity($actor,$target,$type)
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
                $template = "{*target*}にチョメチョメしました";
                break;
            case 2 :
                $template = "あなたが{*actor*}からチョメチョメしました。";
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