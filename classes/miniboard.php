<?php
/*
	* miniBoard 클래스
	
	miniBoard 게시판의 모든 기능을 이 클래스에서 구현합니다.
	(스킨 처리, 데이터 처리 등)
	
	사용법:
	$mBoard = new miniBoard;
	$mBoard->setDB($db);
	$mBoardType = array([secret,accept,skin])
	$mBoard->setType($mBoardType);
	$mBoard->pack();
	
	제작자: 임현규(에프제트) flashizm@gmail.com
*/
class miniBoard {
	var $db;
	var $boardID; // 게시판 ID
	var $boardType = array();
	var $setting; // 스킨처리시 필요한 정보
	var $bbs_dir;
	var $bbs_url;
	var $upload_dir;
	var $upload_url;
	var $use_tag = "a,font,p,br,b,strong,strike,em,i,u,font,img,span,div,table,tr,td,th,tbody,thead,tfoot,sub,sup,embed,object";
	var $admin = false;
	/*
		게시판 작업 모드($mode)
		list: 게시판 목록 페이지
		view: 게시물 보기 페이지
		modify: 게시물 수정 페이지
		write: 게시물 작성 페이지
		reply: 게시물 답변글 페이지
		delete: 게시물 삭제 페이지
		writeAction: 게시물 작성 처리
		modifyAction: 게시물 수정 처리
		deleteAction: 게시물 삭제 처리
		acceptAction: 게시물 승인 처리
		rejectAction: 게시물 제외 처리
	*/
	var $mode;
	
	// 생성자
	function miniBoard() {
		// 기본 Type 설정(비밀글 사용안함, 승인게시판 사용 안함, 스킨 Default)
		$boardType = array(
						"secret" => false,
						"accept" => false,
						"skin" => "default",
						"list_count" => 10,
						"page_count" => 10,
						"admin" => false,
						"file" => false
						);
		$this->setType($boardType);

		// 현재 모드를 설정, Default값은 list
		$this->mode = $_GET[mode];
		if (!$this->mode)
			$this->mode = "list";
	}
	
	// DB 연결시 사용할 DB레퍼 설정
	function setDB($db) {
		$this->db = &$db;
	}
	
	// 모듈 파일이 위치한 URI 설정
	function setURL($url) {
		$this->bbs_url = $url;
		$this->upload_url = $url . "upload/";
	}
	
	// 모듈 파일이 위치한 경로 설정
	function setDIR($path) {
		$this->bbs_dir = $path;
		$this->upload_dir = $path . "upload/";
	}
	
	// 파일 업로드 위치 설정
	function setUploadDir($dir) {
		$this->upload_dir = $dir;
	}
	
	// Board ID 설정
	function setBoardID($id) {
		$this->boardID = $this->db->escape($id);
	}
	
	// Board Type 설정
	function setType($type) {
		$this->boardType = $type + $this->boardType;
	}
	
	// 게시판 처리 & 페이지 출력
	function process() {
	
		// 스킨에 넘길 값 설정
		$this->setting = array();
		$this->setting[mode] = $this->mode;
		$this->setting[boardID] = $this->boardID;
		$this->setting[board_secret] = $this->boardType[secret];
		$this->setting[board_accept] = $this->boardType[accept];
		$this->setting[board_admin] = $this->boardType[admin];
		$this->setting[board_file] = $this->boardType[file];
		$this->setting[list_count] = $this->boardType[list_count];
		$this->setting[skin] = $this->boardType[skin];
		$this->setting[bbs_url] = $this->bbs_url;
		$this->setting[bbs_dir] = $this->bbs_dir;
		$this->setting[skin_path] = $this->bbs_url . "skin/" . $this->boardType[skin] . "/";
		$this->setting[upload_url] = $this->upload_url;
		$this->setting[upload_dir] = $this->upload_dir;
		$this->setting[parameter] = "&page=" . $_GET[page] . "&s_field=" . $_GET[s_field] . "&s_text=" . urlencode($_GET[s_text]);
		$this->setting[protect] = $this->boardType[admin] && !$this->admin;
		$this->setting[admin] = $this->admin;
		
		switch($this->mode):
		case "list":
			$this->listPage();
			break;
		case "view":
			$this->viewPage();
			break;
		case "write":
			$this->writePage();
			break;
		case "reply":
			$this->writePage();
			break;
		case "modify":
			$this->writePage();
			break;
		case "delete":
			$this->deletePage();
			break;
		case "writeAction":
			$this->writeAction();
			break;
		case "deleteAction":
			$this->deleteAction();
			break;
		case "acceptAction":
			$this->acceptAction('Y');
			break;
		case "rejectAction":
			$this->acceptAction('N');
			break;
		default :
			$this->listPage();
			break;
		endswitch;
	}
	
	/*
	===== 페이지 처리 메소드 =====
	*/
	
	// 게시판 목록 페이지 처리
	function listPage() {
		// 검색 처리
		$search_query = NULL;
		
		$s_field = $_GET[s_field];
		$s_text = $this->db->escape(trim($_GET[s_text]));
		
		// 검색어가 있을 경우
		if (strlen($s_text) > 0) {			
			// 검색 필드에 따라 다르게 처리
			if ($s_field == "subject") { // 제목
				$search_query = " AND (1";
				$s_text = explode(" ", $s_text); // 검색어를 공백으로 분리
				for ($i=0;$i<count($s_text);$i++)
					$search_query .= " AND subject LIKE '%" . $s_text[$i] . "%'";
				$search_query .= ")";
			}elseif ($s_field == "content") { // 내용
				$search_query = " AND (1";
				$s_text = explode(" ", $s_text); // 검색어를 공백으로 분리
				for ($i=0;$i<count($s_text);$i++)
					$search_query .= " AND content LIKE '%" . $s_text[$i] . "%'";
				$search_query .= ")";
			}elseif ($s_field == "name") { // 작성자
				$search_query = " AND name LIKE '%" . $s_text . "%'";
			}elseif ($s_field == "subject_content") { // 제목 + 내용
				$search_query = " AND (1";
				$s_text = explode(" ", $s_text); // 검색어를 공백으로 분리
				for ($i=0;$i<count($s_text);$i++)
					$search_query .= " AND CONCAT(subject,content) LIKE '%" . $s_text[$i] . "%'";
				$search_query .= ")";
			}else{
				$s_field = "";
				$search_query = NULL;
			}
		}
		
		// 게시물 수 구하기
		$bbs_count = $this->getListCount($search_query);
		
		// 최대 페이지수 구하기
		$rest = $bbs_count%$this->boardType[list_count];
		$max_page = (int)($bbs_count/$this->boardType[list_count]);
		if($rest!=0)
			$max_page++;
		if (!$max_page)
			$max_page = 1;
		
		// 현재 페이지 구하기
		$page = (int)$_GET[page];
		if (!$page || $page < 1)
			$page = 1;
		elseif ($page > $max_page)
			$page = $max_page;
		
		// limit 위치 설정(MYSQL)
		$limit = $page*$this->boardType[list_count]-$this->boardType[list_count];
		
		// list에서 사용할 URL Parameter(page 없음)
		$this->setting[list_parameter] = "&s_field=" . $_GET[s_field] . "&s_text=" . urlencode($_GET[s_text]);
		
		// 이전 페이지 주소 얻기
		$prev_page = $page-$this->boardType[page_count];
		if ($prev_page < 1)
			$prev_page = 1;
		$prev_link = "?mode=list&page=" . $prev_page . $this->setting[list_parameter];
		
		// 다음 페이지 주소 얻기
		$next_page = $page+$this->boardType[list_count];
		if ($next_page > $max_page)
			$next_page = $max_page;
		$next_link = "?mode=list&page=" . $next_page . $this->setting[list_parameter];
		
		// Page Navigation 만들기
		$page_navi = "";
		for ($i=1; $i<=$this->boardType[list_count]; $i++) {
			$page_num = ((int)(($page-1)/$this->boardType[list_count]))*$this->boardType[list_count]+$i;
			$link = "?mode=list&page=" . $page_num . $this->setting[list_parameter];
			if ($page_num == $page)
				$page_navi .= "<b>&nbsp;${page_num}&nbsp;</b>";
			elseif ($page_num <= $max_page)
				$page_navi .= "&nbsp<a href='" . $link . "'>[".$page_num."]</a>&nbsp";
		}
		
		// 목록 가져오기
		$list = $this->getList($limit,$search_query);
		
		// 스킨에 넘길 값 설정
		$this->setting[page_navi] = $page_navi;
		$this->setting[prev_link] = $prev_link;
		$this->setting[next_link] = $next_link;
		$this->setting[page] = $page;
		$this->setting[max_page] = $max_page;
		$this->setting[s_field] = $s_field;
		$this->setting[s_text] = $s_text;
		$this->setting[bbs_count] = $bbs_count;
		$setting = $this->setting;
		
		// 스킨 가져오기
		$skin_dir = "skin/" . $this->boardType[skin];
		$skin_src = $skin_dir . "/list.html";
		
		include $this->bbs_dir . $skin_src;
	}
	
	
	// 게시물 보기 페이지 처리
	function viewPage() {
		// no값 검증하기
		$uid = (int)$this->db->escape($_GET[no]);
		if (!$uid) {
			$this->error("파라메터가 잘못되었습니다.");
			return;
		}
		
		// 데이터 가져오기
		$data = $this->getData($uid);
		if (!$data[uid]) {
			$this->error("게시물 데이터가 존재하지 않습니다.");
			return;
		}
		
		$data[file1_src] = $this->upload_dir . $data[file1_src];
		$data[file2_src] = $this->upload_dir . $data[file2_src];
		
		// 스킨 디렉토리 얻기		
		$skin_dir = "skin/" . $this->boardType[skin];
		$skin_file = "/view.html";
		
		// 비밀글 처리
		if (!$this->admin && $data[secret] == "Y") {
			// 게시글 비밀번호 확인
			$password = $this->db->escape($_POST[password]);
			if (!$password || strcmp($password,$data[password])) {
				// 이 게시글이 답글이면
				if (trim($data[depth]) != "") {
					// 윗 게시글 비밀번호로 확인
					$parent_data = $this->getDataByGroup($data[group],substr($data[depth],0,strlen($data[depth])-1));
					if (strcmp($password,$parent_data[password])) {
						$skin_file = "/secret.html";
					}
				}else{
					$skin_file = "/secret.html";
				}
			}
		}
		
		// hit수 늘리기
		$this->increaseHit($uid);
		
		// 스킨에 넘길 값 설정
		$s_field = $_GET[s_field];
		$s_text = explode(" ", $this->db->escape($_GET[s_text])); // 검색어를 공백으로 분리
		
		$this->setting[uid] = $uid;
		$this->setting[secret] = $data[secret]=="Y"?true:false;
		$this->setting[password] = $password?true:false; // 비밀번호를 입력했는지 안했는지 여부만
		$this->setting[s_field] = $s_field;
		$this->setting[s_text] = $s_text;
		$setting = $this->setting;
		
		// 스킨 가져오기
		$skin_src = $skin_dir . $skin_file;
		include $this->bbs_dir . $skin_src;
	}
	
	// 게시물 글쓰기,수정,답변글 페이지 처리
	function writePage() {
		// 관리자만 쓰기설정일떄 관리자가 아닐경우
		if ($this->boardType[admin] && !$this->admin) {
			$this->alert("권한이 없습니다.");
			return;
		}
		
		// 수정이나 답글일떄 $uid 가져오기
		$uid = 0;
		if ($this->mode == "modify" || $this->mode == "reply") {
			$uid = (int)$this->db->escape($_GET[no]);
			if (!$uid) {
				$this->error("파라메터가 잘못되었습니다.");
				return;
			}
			
			$o_data = $this->getData($uid);
			if (!$o_data[uid]) {
				$this->error("게시물 데이터가 존재하지 않습니다.");
				return;
			}
			$o_data[subject] = htmlspecialchars($o_data[subject]);
			$o_data[file1_src] = $this->upload_dir . $o_data[file1_src];
			$o_data[file2_src] = $this->upload_dir . $o_data[file2_src];
			if ($this->mode == "modify") $data = $o_data; // 글 수정일경우 데이터 그대로 넘김
			elseif ($this->mode == "reply") {
				// 답글 가능여부 검사
				$this->db->query("SELECT depth FROM atw_miniboard WHERE depth LIKE '" . $o_data[depth] . "%' AND LENGTH(depth) = " . (strlen($o_data[depth])+1)) . " ORDER BY depth DESC";				
				if (substr(@$this->db->result(0,0),strlen($depth)) == "z") {
					$this->error("이 게시물엔 더이상 답글을 달 수 없습니다.");
					return;
				}
				
				// 답글일 경우 데이터 수정(zb4 참고)
				$data = array();
				$data[uid] = $o_data[uid];
				$data[subject] = $o_data[subject];
				if(!eregi("\[Re\]",$data[subject])) $data[subject] = "[Re]" . $data[subject];
				$data[content] = $o_data[content];
				$data[content]=str_replace("\n","\n>",$data[content]);
				$data[content]="\n\n>".$data[content]."\n";
				$data[secret] = $o_data[secret];
			}
		}
		
		// 스킨에 넘길 값 설정
		$this->setting[uid] = $uid;
//		$this->setting[secret] = $data[secret]=="Y"?true:false;
		$setting = $this->setting;
		
		// 스킨 가져오기
		$skin_dir = "skin/" . $this->boardType[skin];
		$skin_src = $skin_dir . "/write.html";
		
		include $this->bbs_dir . $skin_src;
	}
	
	// 게시물 삭제 페이지 처리
	function deletePage() {
		// 관리자만 쓰기설정일떄 관리자가 아닐경우
		if ($this->boardType[admin] && !$this->admin) {
			$this->alert("권한이 없습니다.");
			return;
		}
		
		// no값 검증하기
		$uid = (int)$this->db->escape($_GET[no]);
		if (!$uid) {
			$this->error("파라메터가 잘못되었습니다.");
			return;
		}
	
		// 스킨에 넘길 값 설정
		$this->setting[uid] = $uid;
		$setting = $this->setting;
		
		// 스킨 가져오기
		$skin_dir = "skin/" . $this->boardType[skin];
		$skin_src = $skin_dir . "/delete.html";
		
		include $this->bbs_dir . $skin_src;
	}
	
	/*
	===== 데이터 처리 메소드 =====
	*/
	
	// 게시판 목록 가져오기
	function getList($limit=NULL,$search_query=NULL) {
		// 관리자가 아닐경우 승인된 게시물만 표시
		$accept_query = "";
		if (!$this->admin) $accept_query = " AND accept='Y'";
		
		$sql = "SELECT uid as no,LENGTH(depth) as space,notice,subject,name,DATE_FORMAT(write_date,'%Y/%m/%d') as date,count_view as hit, accept,file1,file2,file1_src,file2_src FROM atw_miniboard WHERE board_id='" . $this->boardID . "'" . $search_query . $accept_query . " ORDER BY notice, `group` DESC, `depth` ASC";
		if ($limit !== NULL)
			$sql .= " LIMIT " . $limit . ", " . $this->boardType[list_count];
		$this->db->query($sql);
		
		$list = array();
		
		while($row = $this->db->fetch_array()) {
			$row[subject] = stripslashes($row[subject]);
			$row[name] = stripslashes($row[name]);
			array_push($list, $row);
			
		}
		return $list;
	}
	
	function getListCount($search_query=NULL) {
		// 관리자가 아닐경우 승인된 게시물만 표시
		$accept_query = "";
		if (!$this->admin) $accept_query = " AND accept='Y'";
		
		$this->db->query("SELECT count(*) FROM atw_miniboard WHERE board_id='" . $this->boardID . "'" . $search_query . $accept_query);
		return (int)$this->db->result(0,0);
	}
	
	// 게시물 데이터 가져오기
	function getData($uid) {
		$this->db->query("SELECT * FROM atw_miniboard WHERE uid=" . $uid);
		$data = $this->db->fetch_array();
		$data[subject] = stripslashes($data[subject]);
		$data[content] = stripslashes($data[content]);
		$data[name] = stripslashes($data[name]);
		return $data;
	}
	
	// group, depth값으로 게시물 데이터 가져오기
	function getDataByGroup($group, $depth) {
		$this->db->query("SELECT * FROM atw_miniboard WHERE depth='" . $depth . "' AND `group`=" . $group);
		$data = $this->db->fetch_array();
		$data[subject] = stripslashes($data[subject]);
		$data[content] = stripslashes($data[content]);
		$data[name] = stripslashes($data[name]);
		return $data;
	}
	
	// 게시물 데이터 등록하기
	function writeAction() {
		// 관리자만 쓰기설정일떄 관리자가 아닐경우
		if ($this->boardType[admin] && !$this->admin) {
			$this->alert("권한이 없습니다.");
			return;
		}
		
		$uid = (int)$this->db->escape(trim($_POST[no])); // 대상 게시물 번호
		$name = $this->db->escape(trim($_POST[name])); // 작성자
		$subject = $this->db->escape(trim($_POST[subject])); // 제목
		$content = $this->db->escape(trim($_POST[content])); // 내용
 		$password = $this->db->escape(trim($_POST[password])); // 비밀번호
		$write_date = $this->db->escape(trim($_POST[write_date]));
		
		$secret = 'N'; // 비밀글
		$notice = 'N'; // 공지사항
		$accept = 'Y'; // 승인여부
		$html = 'N'; // HTML
		if ($_POST[is_secret] == "1") $secret = 'Y';
		if ($_POST[is_html] == "1") $html = 'Y';
		if ($this->admin && $_POST[is_notice] == "1") $notice = 'Y';
		if (!$this->admin && $this->boardType[accept]) $accept = 'N';
		
		// 비밀게시판 모드일떄는 공지사항 제외하고 모두 비밀글
		if ($this->boardType[secret] && $notice == 'N')
			$secret = 'Y';
		
		$writeMode = $_POST[writeMode]; // 게시글 답글,수정,작성 여부
		
		// value check
		if (!$this->admin && !$password) {
			$this->alert("비밀번호를 입력하세요.");
			return;
		}elseif (!$name) {
			$this->alert("작성자를 입력하세요.");
			return;
		}elseif(!$subject) {
			$this->alert("제목을 입력하세요.");
			return;
		}elseif(!$content) {
			$this->alert("내용을 입력하세요.");
			return;
		}
		
		// 데이터 가공
		if (!$this->admin)
			$subject = htmlspecialchars($subject);
		if ($html == 'Y') {
			// http://guys0823.egloos.com/1382684 참고
			$content = str_replace("<", "[#SPTAG-LT#]", $content); // 우선 tag를 제거
			$tag = explode(",", $this->use_tag);
			
			foreach ($tag as $t) { // 허용된 tag만 사용가능토록 처리
				$content = eregi_replace("\[#SPTAG-LT#\]".$t." ", "<".$t." ", $content);
				$content = eregi_replace("\[#SPTAG-LT#\]".$t.">", "<".$t.">", $content);
				$content = eregi_replace("\[#SPTAG-LT#\]/".$t, "</".$t, $content);
			}
			
			$content = str_replace("[#SPTAG-LT#]", "&lt;", $content);
			
			// on으로 시작하는 javascript 이벤트 제거
			$content = eregi_replace("( )(on[a-z\r\n]+)(=| =)"," dis\\2=",$content);
		}else{
			$content = htmlspecialchars($content);
		}
		$name = htmlspecialchars($name);
		if ($write_date) {
			$write_date = "'" . $write_date . "'";
		}else{
			$write_date = "now()";
		}
		
		// 파일 업로드 변수 초기화
		$f1_name = "";
		$f1_src = "";
		$f2_name = "";
		$f2_src = "";
		
		if ($writeMode == "write") { // 게시글 작성
		
			// 파일 업로드 처리
			if ($this->boardType[file]) { // 파일첨부기능 사용할 경우
				if (is_uploaded_file($_FILES[file1][tmp_name])) {
					$f1_name = $this->db->escape($_FILES[file1][name]);
					$f1_src = mktime() . "1" . rand();
					$this->fileUpload($_FILES[file1], $this->upload_dir . $f1_src);
				}
				if (is_uploaded_file($_FILES[file2][tmp_name])) {
					$f2_name = $this->db->escape($_FILES[file2][name]);
					$f2_src = mktime() . "2" . rand();
					$this->fileUpload($_FILES[file2], $this->upload_dir . $f2_src);
				}
			}
		
			// group값 얻기
			$this->db->query("SELECT MAX(`group`) FROM atw_miniboard");
			$group = $this->db->result(0,0) + 1;
			$depth = "";
			
			// DB에 추가
			$this->db->query("INSERT INTO atw_miniboard
							(board_id,`group`,depth,name,subject,content,file1,file2,file1_src,file2_src,secret,password,html,accept,notice,write_date,write_ip)
							VALUES ('" . $this->boardID . "'," . $group . ",'" . $depth . "','" . $name . "','" . $subject . "','" . $content . "','" . $f1_name . "','" . $f2_name . "','" . $f1_src . "','" . $f2_src . "','" . $secret . "','" . $password . "','" . $html . "','" . $accept . "','" . $notice . "'," . $write_date . ",'" . getenv("REMOTE_ADDR") . "')");

		} elseif ($writeMode == "reply") { // 답글 작성
		
			// 파일 업로드 처리
			if ($this->boardType[file]) { // 파일첨부기능 사용할 경우
				if (is_uploaded_file($_FILES[file1][tmp_name])) {
					$f1_name = $this->db->escape($_FILES[file1][name]);
					$f1_src = mktime() . "1" . rand();
					$this->fileUpload($_FILES[file1], $this->upload_dir . $f1_src);
				}
				if (is_uploaded_file($_FILES[file2][tmp_name])) {
					$f2_name = $this->db->escape($_FILES[file2][name]);
					$f2_src = mktime() . "2" . rand();
					$this->fileUpload($_FILES[file2], $this->upload_dir . $f2_src);
				}
			}
			
			// $uid 값 검증
			if (!$uid) {
				$this->error("파라메터가 잘못되었습니다.");
				return;
			}			
			$data = $this->getData($uid);
			if (!$data[uid]) {
				$this->alert("대상 게시물이 없습니다.");
				return;
			}
			
			// group값 얻기
			$this->db->query("SELECT `group`,depth FROM atw_miniboard WHERE uid=" . $uid);
			$group = $this->db->result(0,0);
			$depth = $this->db->result(0,1);
			
			// depth값 얻기
			$this->db->query("SELECT depth FROM atw_miniboard WHERE depth LIKE '" . $depth . "%' AND LENGTH(depth) = " . (strlen($depth)+1)) . " ORDER BY depth DESC";
			$depth_laststr = substr(@$this->db->result(0,0),strlen($depth));
			if ($depth_laststr == "") {
				$depth_laststr = "a"; // 답글이 없을경우 첫번째 답글로
			}elseif ($depth_laststr == "z") {
				$this->error("이 게시물엔 더이상 답글을 달 수 없습니다.");
				return;
			}else{
				$depth_laststr++; // 다음 알파벳 글자로
			}
			$depth .= $depth_laststr;
			
			// DB에 추가
			$this->db->query("INSERT INTO atw_miniboard
							(board_id,`group`,depth,name,subject,content,file1,file2,file1_src,file2_src,secret,password,html,accept,notice,write_date,write_ip)
							VALUES ('" . $this->boardID . "'," . $group . ",'" . $depth . "','" . $name . "','" . $subject . "','" . $content . "','" . $f1_name . "','" . $f2_name . "','" . $f1_src . "','" . $f2_src . "','" . $secret . "','" . $password . "','" . $html . "','" . $accept . "','" . $notice . "',now(),'" . getenv("REMOTE_ADDR") . "')");

		}elseif ($writeMode == "modify") { // 게시글 수정
			// $uid 값 검증
			if (!$uid) {
				$this->error("파라메터가 잘못되었습니다.");
				return;
			}			
			$data = $this->getData($uid);
			if (!$data[uid]) {
				$this->alert("대상 게시물이 없습니다.");
				return;
			}
			
			// 비밀번호 체크
			if (!$this->admin && strcmp($password,$data[password])) {
				$this->alert("비밀번호가 맞지 않습니다");
				return;
			}
		
			// 파일 업로드 처리
			$sql_file = "";
			if ($this->boardType[file]) { // 파일첨부기능 사용할 경우
			
				$f1 = false;
				$f2 = false;
				
				if (is_uploaded_file($_FILES[file1][tmp_name])) { // 업로드된 파일이 있을경우 (file1)
					$f1_name = $this->db->escape($_FILES[file1][name]);
					$f1_src = mktime() . "1" . rand();
					if ($this->fileUpload($_FILES[file1], $this->upload_dir . $f1_src)) {
						$f1 = true;
						$sql_file .= "file1='" . $f1_name . "', file1_src='" . $f1_src . "', ";
					}
				}
				if (is_uploaded_file($_FILES[file2][tmp_name])) { // 업로드된 파일이 있을경우 (file2)
					$f2_name = $this->db->escape($_FILES[file2][name]);
					$f2_src = mktime() . "2" . rand();
					if ($this->fileUpload($_FILES[file2], $this->upload_dir . $f2_src)) {
						$f2 = true;
						$sql_file .= "file2='" . $f2_name . "', file2_src='" . $f2_src . "', ";
					}
				}
				
				// 파일 삭제에 체크했을 경우 (file1)
				if ($data[file1_src] && $_POST[file1_del] == "delete") {
					@unlink($this->upload_dir . $data[file1_src]); // 파일 삭제
					// 업로드된 파일이 없다면
					if (!$f1) {
						$sql_file .= "file1='', file1_src='', ";
					}
				}
				
				// 파일 삭제에 체크했을 경우 (file2)
				if ($data[file2_src] && $_POST[file2_del] == "delete") {
					@unlink($this->upload_dir . $data[file2_src]); // 파일 삭제
					// 업로드된 파일이 없다면
					if (!$f2) {
						$sql_file .= "file2='', file2_src='', ";
					}
				}
			}
			
			// DB 데이터 수정
			$this->db->query("UPDATE atw_miniboard SET name='" . $name . "', subject='" . $subject . "', content='" . $content . "', " . $sql_file . "secret='" . $secret . "', html='" . $html . "', notice='" . $notice . "', modify_date=now(), modify_ip='" . getenv("REMOTE_ADDR") . "', write_date=" . $write_date . " WHERE uid=" . $uid);
		}
		
		// 글쓰기거나 답글이고 승인안된 상태로 등록됬다면
		if ($accept == 'N' && ($writeMode == "write" || $writeMode == "reply"))
			$this->message("작성해 주셔서 감사합니다.\\n게시물은 관리자 승인 후 등록됩니다.");

		// list 페이지로 이동
		$this->go("?mode=list" . $this->setting[parameter]);
	}
	
	function deleteAction() {
		// 관리자만 쓰기설정일떄 관리자가 아닐경우
		if ($this->boardType[admin] && !$this->admin) {
			$this->alert("권한이 없습니다.");
			return;
		}
		
		$uid = (int)$this->db->escape(trim($_POST[no])); // 삭제할 게시물 번호
		// $uid 값 검증
		if (!$uid) {
			$this->error("파라메터가 잘못되었습니다.");
			return;
		}			
		$data = $this->getData($uid);
		if (!$data[uid]) {
			$this->alert("삭제할 게시물이 없습니다.");
			return;
		}
		
 		$password = $this->db->escape(trim($_POST[password])); // 비밀번호
		
		// value check
		if (!$this->admin && !$password) {
			$this->alert("비밀번호를 입력하세요.");
			return;
		}
		// 비밀번호 체크
		if (!$this->admin && strcmp($password,$data[password])) {
			$this->alert("비밀번호가 맞지 않습니다");
			return;
		}
		
		// 해당 게시물, 답글 모두 삭제
		$this->db->query("DELETE FROM atw_miniboard WHERE uid=" . $uid);
		$this->db->query("DELETE FROM atw_miniboard WHERE `group` = " . $data[group] . " AND depth LIKE '" . $data[depth] . "%'");

		// list 페이지로 이동
		$this->go("?mode=list" . $this->setting[parameter]);
	}
	
	function acceptAction($value) {
		// 관리자가 아닌 경우
		if (!$this->admin) {
			$this->alert("권한이 없습니다.");
			return;
		}
		
		$uid = (int)$this->db->escape(trim($_GET[no])); // 삭제할 게시물 번호
		if (!$uid) {
			$this->error("파라메터가 잘못되었습니다.");
			return;
		}
		
		$this->db->query("UPDATE atw_miniboard SET accept='" . $value . "' WHERE uid=" . $uid);
		
		// list 페이지로 이동
		$this->go("?mode=list" . $this->setting[parameter]);
	}
	
	// 조회수 늘리기
	function increaseHit($uid) {
		$this->db->query("UPDATE atw_miniboard SET count_view=count_view+1 WHERE uid=" . $uid);
	}
	
	/*
	===== 기타 메소드 =====
	*/
	
	// 에러메세지 표시
	function error($msg) {
		echo $msg;
	}
	
	// 알림창 표시
	function message($msg) {
		echo "<script>alert('" . $msg . "')</script>";
	}
	
	// 알림창 표시후 뒤로가기
	function alert($msg) {
		echo "<script>alert('" . $msg . "');history.back()</script>";
	}
	
	// 페이지 이동
	function go($url) {
		echo "<script>location.href='" . $url . "'</script>";
	}
	
	// 관리자 모드
	function admin() {
		$this->admin = true;
	}
	
	// 파일 업로드
	function fileUpload($file,$target) {
		if (!move_uploaded_file($file[tmp_name], $target)) {
			$this->error("파일 업로드에 실패하였습니다.");
			return false;
		}
		return true;
	}
	
	// 파일 다운로드
	function fileDownload($uid,$name,$type="download") {
		$uid = (int)$this->db->escape(trim($uid)); // 다운로드할 게시물 번호
		// $uid 값 검증
		if (!$uid) {
			$this->alert("파라메터가 잘못되었습니다.");
			return;
		}			
		$data = $this->getData($uid);
		if (!$data[uid]) {
			$this->alert("다운로드 파일이 있는 게시물이 없습니다.");
			return;
		}
		
		// 레퍼러 검증
		if (!eregi("mode=view",$_SERVER['HTTP_REFERER']) && $type != "preview") {
			$this->alert("정상적인 접근이 아닙니다.");
			return;
		}
		
		$src = "";
		
		if ($name && $data[file1] == $name) {
			$src = $data[file1_src];
		}elseif ($name && $data[file2] == $name) {
			$src = $data[file2_src];
		}else{
			$this->alert("해당 파일이 게시물에 없습니다.");
			return;
		}
		
		// HTTP 헤더 설정
		if ($type == "none" || $type == "preview") { // 파일로 다운받지 않을경우
			// 확장자에 따라 헤더의 Content-Type 변경
			$content_type = "text/plain";
			if (eregi("\.jpg",$name))
				$content_type = "image/jpeg";
			elseif (eregi("\.jpg",$name))
				$content_type = "image/gif";
			elseif (eregi("\.png",$name))
				$content_type = "image/png";

			header("Content-Type: " . $content_type);
//			header("Content-Disposition: attachment; filename=\"" . $name . "\"");
		}else{
			header("Content-Type: application/octet-stream");
			header("Content-Disposition: attachment; filename=\"" . $name . "\"");
			header("Expires: 0");
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
		}
		
		@readfile($this->upload_dir . $src);
	}
	
	/*	
	function fileRead($src) {
		$fp = fopen($src,'r');
		$rt_text = fread($fp,filesize($src));
		fclose($fp);
		return $rt_text;
	}
	*/
}
?>