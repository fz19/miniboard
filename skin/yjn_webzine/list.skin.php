<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once(G4_LIB_PATH.'/thumbnail.lib.php');
// 썸네일 크기 설정
$thumb_width = '110';    //썸네일 넓이
$thumb_height = '80';    //썸네일 높이
?>

<link rel="stylesheet" href="<?php echo $board_skin_url ?>/style.css">

<?php if (!$wr_id) { ?><h1 id="bo_list_title"><?php echo $board['bo_subject'] ?></h1><?php } ?>

<!-- 게시판 목록 시작 -->
<div id="bo_img" style="width:<?php echo $width; ?>">

    <?php if ($is_category) { ?>
    <form name="fcategory" id="fcategory" method="get">
    <nav id="bo_cate">
        <h2><?php echo $board['bo_subject'] ?> 카테고리</h2>
        <ul id="bo_cate_ul">
            <?php echo $category_option ?>
        </ul>
    </nav>
    </form>
    <?php } ?>

    <div class="bo_fx">
        <div id="bo_list_total">
            <span>Total <?php echo number_format($total_count) ?>건</span>
            <?php echo $page ?> 페이지
        </div>

        <?php if ($rss_href || $write_href) { ?>
        <ul class="btn_bo_user">
            <?php if ($rss_href) { ?><li><a href="<?php echo $rss_href ?>" class="btn_b01">RSS</a></li><?php } ?>
            <?php if ($admin_href) { ?><li><a href="<?php echo $admin_href ?>" class="btn_admin">관리자</a></li><?php } ?>
            <?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="btn_b02">글쓰기</a></li><?php } ?>
        </ul>
        <?php } ?>
    </div>

    <form name="fboardlist"  id="fboardlist" action="./board_list_update.php" onsubmit="return fboardlist_submit(this);" method="post">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="spt" value="<?php echo $spt ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="sw" value="">

    <h2>이미지 목록</h2>

    <ul id="bo_img_list">
		<?php for ($i=0; $i<count($list); $i++) { ?>

		<li class="bo_img_list_li <?php if ($wr_id == $list[$i]['wr_id']) { ?>bo_img_now<?php } ?>">
            <span class="sound_only">
                <?php
                if ($wr_id == $list[$i]['wr_id'])
                    echo "<span class=\"bo_current\">열람중</span>";
                else
                    echo $list[$i]['num'];
                 ?>
            </span>

			<ul class="bo_img_con"> 
                <li class="bo_img_href" style="padding:4px; border:1px solid #ddd;">
                    <a href="<?php echo $list[$i]['href'] ?>">
                    <?php
                    if ($list[$i]['is_notice']) { // 공지사항  ?>
                        <strong style="width:<?php echo $thumb_width ?>px; height:45px">공지</strong>
                    <?php } else {
						$thumb = get_list_thumbnail($board['bo_table'], $list[$i]['wr_id'], $thumb_width, $thumb_height);

                        if($thumb['src']) {
                            $img_content = '<img src="'.$thumb['src'].'" alt="'.$thumb['alt'].'" width="'.$thumb_width.'" height="'.$thumb_height.'">';
                        } else {
                            $img_content = '<span style="width:'.$thumb_width.'px;height:'.$thumb_height.'px">no image</span>';
                        }
						echo $img_content;
                    }
                     ?>
                    </a>
                </li>
            </ul>  
			<ul class="bo_img_con2"> 
                <li class="bo_img_text_href">
		            <?php if ($is_checkbox) { ?>
				    <label for="chk_wr_id_<?php echo $i ?>" class="sound_only"><?php echo $list[$i]['wr_subject'] ?></label>
		            <input type="checkbox" name="chk_wr_id[]" value="<?php echo $list[$i]['wr_id'] ?>" id="chk_wr_id_<?php echo $i ?>">
				    <?php } ?>
                    <a href="<?php echo $list[$i]['href'] ?>">
                        <?php echo $list[$i]['subject'] ?>
                        <?php if ($list[$i]['comment_cnt']) { ?><span class="sound_only">댓글</span><?php echo $list[$i]['comment_cnt']; ?><span class="sound_only">개</span><?php } ?>
                    </a>
                    <?php
                    // if ($list[$i]['link']['count']) { echo '['.$list[$i]['link']['count']}.']'; }
                    // if ($list[$i]['file']['count']) { echo '<'.$list[$i]['file']['count'].'>'; }

                    if (isset($list[$i]['icon_new'])) echo $list[$i]['icon_new'];
                    if (isset($list[$i]['icon_hot'])) echo $list[$i]['icon_hot'];
                    //if (isset($list[$i]['icon_file'])) echo $list[$i]['icon_file'];
                    //if (isset($list[$i]['icon_link'])) echo $list[$i]['icon_link'];
                    //if (isset($list[$i]['icon_secret'])) echo $list[$i]['icon_secret'];
                     ?>
                </li>
                <li>
					<?php
                    if ($is_category && $list[$i]['ca_name'] && !$list[$i]['is_notice']) { 
                     ?>
                    <a href="<?php echo $list[$i]['ca_name_href'] ?>" class="bo_cate_link"><?php echo $list[$i]['ca_name'] ?></a>
					<span class="bo_gubun">|</span>
                    <?php } ?>
					<?php echo $list[$i]['name'] ?> <span class="bo_gubun">|</span>       
					<?php echo $list[$i]['datetime2'] ?> <span class="bo_gubun">|</span>  
					<span class="bo_img_subject">조회 </span><?php echo $list[$i]['wr_hit'] ?> 
	                <?php if ($is_good) { ?><span class="bo_gubun">|</span><span class="bo_img_subject">추천 </span><strong><?php echo $list[$i]['wr_good'] ?></strong><?php } ?>
		            <?php if ($is_nogood) { ?><span class="bo_gubun">|</span><span class="bo_img_subject">비추천 </span><strong><?php echo $list[$i]['wr_nogood'] ?></strong><?php } ?>
				</li>
				<?php
				$wr_content = preg_replace("/<(.*?)\>/"," ",$list[$i][wr_content]); 
				$wr_content = preg_replace("/&nbsp;/"," ",$wr_content); 
				$wr_content = str_replace("//##", " ", $wr_content); 
				$wr_content = cut_str(get_text($wr_content), 100, '…');
                if (!$list[$i]['is_notice']) { ?>
					<li class="bo_content"><?php echo $wr_content?></li>
				<?php } ?>
            </ul>
        </li>
        <?php } ?>
        <?php if (count($list) == 0) { echo "<li class=\"empty_list\">게시물이 없습니다.</li>"; } ?>
    </ul>

    <?php if ($list_href || $is_checkbox || $write_href) { ?>
    <div class="bo_fx">
        <ul class="btn_bo_adm">
            <?php if ($list_href) { ?>
            <li><a href="<?php echo $list_href ?>" class="btn_b01"> 목록</a></li>
            <?php } ?>
            <?php if ($is_checkbox) { ?>
            <li><input type="submit" name="btn_submit" value="선택삭제" onclick="document.pressed=this.value"></li>
            <li><input type="submit" name="btn_submit" value="선택복사" onclick="document.pressed=this.value"></li>
            <li><input type="submit" name="btn_submit" value="선택이동" onclick="document.pressed=this.value"></li>
            <?php } ?>
        </ul>

        <ul class="btn_bo_user">
            <li><?php if ($write_href) { ?><a href="<?php echo $write_href ?>" class="btn_b02">글쓰기</a><?php } ?></li>
        </ul>
    </div>
    <?php } ?>

    </form>
</div>

<?php if($is_checkbox) { ?>
<noscript>
<p>자바스크립트를 사용하지 않는 경우<br>별도의 확인 절차 없이 바로 선택삭제 처리하므로 주의하시기 바랍니다.</p>
</noscript>
<?php } ?>

<!-- 페이지 -->
<?php echo $write_pages;  ?>

<fieldset id="bo_sch">
    <legend>게시물 검색</legend>

    <form name="fsearch" method="get">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
    <input type="hidden" name="sca" value="<?php echo $sca ?>">
    <input type="hidden" name="sop" value="and">
    <label for="sfl" class="sound_only">검색대상</label>
    <select name="sfl" id="sfl">
        <option value="wr_subject"<?php echo get_selected($sfl, "wr_subject", true); ?>>제목</option>
        <option value="wr_content"<?php echo get_selected($sfl, "wr_content"); ?>>내용</option>
        <option value="wr_subject||wr_content"<?php echo get_selected($sfl, "wr_subject||wr_content"); ?>>제목+내용</option>
        <option value="mb_id,1"<?php echo get_selected($sfl, "mb_id,1"); ?>>회원아이디</option>
        <option value="mb_id,0"<?php echo get_selected($sfl, "mb_id,0"); ?>>회원아이디(코)</option>
        <option value="wr_name,1"<?php echo get_selected($sfl, "wr_name,1"); ?>>글쓴이</option>
        <option value="wr_name,0"<?php echo get_selected($sfl, "wr_name,0"); ?>>글쓴이(코)</option>
    </select>
    <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    <input type="text" name="stx" value="<?php echo stripslashes($stx) ?>" id="stx" required class="frm_input required" size="15" maxlength="15">
    <input type="submit" value="검색" class="btn_submit">
    </form>
</fieldset>

<?php if ($is_checkbox) { ?>
<script>
function all_checked(sw) {
    var f = document.fboardlist;

    for (var i=0; i<f.length; i++) {
        if (f.elements[i].name == "chk_wr_id[]")
            f.elements[i].checked = sw;
    }
}

function fboardlist_submit(f) {
    var chk_count = 0;

    for (var i=0; i<f.length; i++) {
        if (f.elements[i].name == "chk_wr_id[]" && f.elements[i].checked)
            chk_count++;
    }

    if (!chk_count) {
        alert(document.pressed + "할 게시물을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택복사") {
        select_copy("copy");
        return;
    }

    if(document.pressed == "선택이동") {
        select_copy("move");
        return;
    }

    if(document.pressed == "선택삭제") {
        if (!confirm("선택한 게시물을 정말 삭제하시겠습니까?\n\n한번 삭제한 자료는 복구할 수 없습니다"))
            return false;
    }

    return true;
}

// 선택한 게시물 복사 및 이동
function select_copy(sw) {
    var f = document.fboardlist;

    if (sw == 'copy')
        str = "복사";
    else
        str = "이동";

    var sub_win = window.open("", "move", "left=50, top=50, width=500, height=550, scrollbars=1");

    f.sw.value = sw;
    f.target = "move";
    f.action = "./move.php";
    f.submit();
}
</script>
<?php } ?>
<!-- 게시판 목록 끝 -->
