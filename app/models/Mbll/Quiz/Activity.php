<?php

class Mbll_Quiz_Activity
{
	/**
	 * quiz activity
	 *
	 * @param string $type
	 * @param string $quiz
	 * @return string
	 */
	public static function getActivity($type, $quiz)
	{
        //$type 1 ,answer right 2 , answer wrong
		switch ($type) {
			case 1 :
				$template = "マイミククイズに正解しました！";
				break;
			case 2 :
				$template = "あなたの". $quiz ."を知りませんでした…";
				break;
			default:;
		}

        return $template;
	}
}