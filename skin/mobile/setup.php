<?
	if (!$setting) die("�߸��� �����Դϴ�.");
	$dir = $setting[skin_path];
    
    // ���� �߰�
    function replyIcon($num,$dir) {
		if (!$num) return;
    	$s = "";
    	for ($i=0;$i<$num;$i++)
        	$s .= "&nbsp;";
		$s .= "<img src=\"" . $dir . "images/icon_point.gif\" width=\"11\" height=\"11\" /> ";
        return $s;
    }
	
	// Ư���ܾ� ���̶���Ʈ
	function text_highlight($t,$str) {
		return eregi_replace($t, "<span class=\"highlight\">\\0</span>", $str);
	}
	
	// üũ�ڽ� Checked
	function checked($v) {
		if ($v == "Y") return " checked=\"checked\"";
	}
?>
<link href="<?=$dir?>inc/style.css" rel="stylesheet" type="text/css" />
<script language="javascript" src="<?=$dir?>inc/script.js" type="text/javascript"></script>