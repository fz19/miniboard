<?
	if (!$setting) die("잘못된 접근입니다.");
	$dir = $setting[skin_path];
	
	// 이미지 가로 갯수
	$max_col = 4;
	
	// 썸네일 기준 크기
	$thum_size = 140;
    
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
	
	// 이미지 파일 여부
	function is_imageFile($name) {
		if (eregi("\.jpg",$name))
			return true;
		else
			return false;
	}
	
	// 썸네일 만들기 (썸네일 사이즈 리턴)
	function make_thumnail($src,$thum_src,$size) {
		// noimage라면 리턴
		if (is_file($thum_src) && eregi("/noimage.jpg",$thum_src)) return $thum_src;
		
		$srcstat = @stat($src);
		$thumbstat = @stat($thum_src);
	
		// 원본 파일과 썸네일의 변경일시 비교
		if (!is_file($thum_src) || $srcstat[mtime] != $thumbstat[mtime]) {
			// 기존 썸네일 제거
			@unlink($thum_src);

			// 사이즈 결정
			$img_size = getimagesize($src);
			$width = $img_size[0];
			$height = $img_size[1];
			if ($width > $height) {
				$height = $height/($width/$size);
				$width = $size;
			}elseif ($width < $height) {
				$width = $width/($height/$size);
				$height = $size;
			}else{
				$width = $height = $size;
			}
			
			// 썸네일 만들기
			/*
			$src_img = imagecreatefromjpeg($src);
			$dest_img = imagecreatetruecolor($width,$height);
			imagecolorallocate($dest_img,0,0,0); // White Background
			imagecopyresampled($dest_img,$src_img,0,0,0,0,$width,$height,$img_size[0],$img_size[1]);
			@imagejpeg($dest_img,$thum_src);
			@imagedestroy($dest_img);
			@imagedestroy($src_img);
			*/
			$src_img = imagecreatefromjpeg($src);
			$dest_img = imagecreatetruecolor($width,$height);
			imagecolorallocate($dest_img,0,0,0); // White Background
			imagecopyresampled($dest_img,$src_img,0,0,0,0,$width,$height,$img_size[0],$img_size[1]);
			imagejpeg($dest_img,$thum_src);
			imagedestroy($dest_img);
			imagedestroy($src_img);
			
			// 썸네일 파일의 변경일시를 원본 파일과 맞춤
			@touch($thum_src,$srcstat[mtime]);
			
			// 파일이 정상적으로 생성됬는지 체크
			if (is_file($thum_src)) {
				$result_src = $thum_src;
			}else{
				$result_src = $src;
			}
		}else{
			$result_src = $thum_src;
		}
		
		return $result_src;
	}
?>
<link href="<?=$dir?>inc/style.css" rel="stylesheet" type="text/css" />
<script language="javascript" src="<?=$dir?>inc/script.js" type="text/javascript"></script>