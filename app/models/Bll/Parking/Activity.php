<?php

class Bll_Parking_Activity
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
		$actor = Bll_User::getPerson($actor);
		
		if (empty($actor)) {
			$actor_name = "____";
		}
		else {
			$actor_name = $actor->getDisplayName();
		}
		$json_array['actor'] = $actor_name;
		
		if ($target) {
            if ($target < 0) {
                require_once 'Dal/Parking/Neighbor.php';
                $dalPark = new Dal_Parking_Neighbor();
                $json_array['target'] = $dalPark->getNeighborName($target);
            }
            else {
                $targ = Bll_User::getPerson($target);

		        if (empty($targ)) {
		        	$target_name = "____";
		        }
		        else {
		        	$target_name = $targ->getDisplayName();
		        }
                $json_array['target'] = $target_name ;
            }
        }
        
		switch ($type) {
			case 1 :
				$template = "{*target*}を取り締まりました。";
				break;
			case 2 :
				$template = "{*actor*}が、{*target*}の{*car_name*}の通報に成功しました。";
				break;
			case 3 :
				$template = "{*target*}の{*car_name*}が、友達の誰かに通報されました。";
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