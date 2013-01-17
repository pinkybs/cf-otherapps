<?php

class Mbll_Brain_Activity
{
	/**
	 * brain activity
	 *
	 * @param string $type
	 * @param string $score
	 * @return string
	 */
	public static function getActivity($type,$score,$gamename)
	{	
		
		
		
		switch ($type) {
			case 1 :
				$template = "【ﾏｲﾐｸ頭脳くらべ】を追加しました。";
				break;
			case 2 :
				$template = $gamename . "で" . $score . "点獲得!";
				break;
			case 3 :
				$template = $gamename . "で" . $score . "点獲得!";
				break;
			default:;
		}
        
        return $template;
	}
}