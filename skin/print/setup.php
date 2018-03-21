<?
	if (!$setting) die("잘못된 접근입니다.");
	$dir = $setting[skin_path];
    
    // 공백 추가
    function replyIcon($num,$dir) {
		if (!$num) return;
    	$s = "";
    	for ($i=0;$i<$num;$i++)
        	$s .= "&nbsp;";
		$s .= "<img src=\"" . $dir . "images/icon_point.gif\" width=\"11\" height=\"11\" /> ";
        return $s;
    }
	
	// 특정단어 하이라이트
	function text_highlight($t,$str) {
		return eregi_replace($t, "<span class=\"highlight\">\\0</span>", $str);
	}
	
	// 체크박스 Checked
	function checked($v) {
		if ($v == "Y") return " checked=\"checked\"";
	}
?>
<link href="<?=$dir?>inc/style.css" rel="stylesheet" type="text/css" />
<script language="javascript" src="<?=$dir?>inc/script.js" type="text/javascript"></script>