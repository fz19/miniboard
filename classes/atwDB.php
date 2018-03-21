<?php
/*
	* DB 래퍼 클래스 atwDB
	
	DB 사용시 간단하게 사용할수 있도록 도와주며
	필요시에는 mysql 외의 DB도 코드 변경없이 사용 가능하도록 설계(미구현)
	
	사용법:
	$db = new atwDB;
	$db->setConfigFile(...);
	$db->connect();
	$db->query(...);
	$db->close();
*/
class atwDB {
	var $server;
	var $user;
	var $password;
	var $database;	
	var $conn;
	var $debug_type = "hidden";
	var $query_result;
	var $config_src = "db_config.php";
	
	/*
		* DB 접속 정보 설정
		
		$server : DB 서버
		$user : 사용자 계정
		$password : 비밀번호
		$database : 데이터베이스
	*/
	function atwDB($server=NULL,$user=NULL,$password=NULL,$database=NULL) {
		$this->server = $server;
		$this->user = $user;
		$this->password = $password;
		$this->database = $database;
	}
	
	/*
		* DB 접속 정보 경로지정
	*/
	function setConfigFile($src) {
		$config_src = $src;
		
		if (!file_exists($src)) {
			$this->error('DB 설정 파일이 없습니다.');
		}
		
		include $src;
		
		if (!$id || !$host || !$pw || !$db) {
			$this->error('DB 설정 파일이 잘못되었습니다.');
		}
		
		$this->server = $host;
		$this->user = $id;
		$this->password = $pw;
		$this->database = $db;
	}
	
	/*
		* 디버그 메세지 출력타입 설정
		
		hidden : html 주석으로 표시됩니다.
		none : div 태그에 일반 텍스트로 표시됩니다.
		
		그 밖에 type은 표시가 되지 않습니다.
	*/
	function setDebugType($type) {
		$this->debug_type = $type;
	}
	
	/*
		* DB 연결
	*/
	function connect() {
		$this->conn = mysql_connect($this->server,$this->user,$this->password) or $this->error("DB 연결에 실패하였습니다.");
		mysql_select_db($this->database,$this->conn) or $this->error("DB 선택에 실패하였습니다.");
		mysql_query("set names utf8");
	}
	
	/*
		* DB 닫기
	*/
	function close() {
		mysql_close($this->conn) or $this->error("DB 닫기에 실패하였습니다.");
	}
	
	function query($sql) {
		$this->query_result = mysql_query($sql,$this->conn) or $this->error("DB 쿼리가 잘못되었습니다.",$sql,mysql_errno($this->conn) . ": " . mysql_error($this->conn));
		return $this->query_result;
		
	}
	
	function fetch_array() {
		return mysql_fetch_array($this->query_result);
	}
	
	function num_rows() {
		return mysql_num_rows($this->query_result);
	}
	
	function result($row,$col=NULL) {
		if ($col == NULL)
			return mysql_result($this->query_result,$row);
		else
			return mysql_result($this->query_result,$row,$col);
	}
	
	// 기타
	
	function escape($str) {
		if ($this->conn)
			return mysql_real_escape_string($str);
		else
			return mysql_escape_string($str);
	}
	
	/*
		* 에러 메세지 표시
		에러 메세지를 표시합니다.
		setDebugType 메소드로 출력형태를 설정할수 있습니다.
	*/
	function error($msg,$sql=NULL,$sql_msg=NULL) {
		if ($this->debug_type == "hidden"):
			echo "
<!--
===== DB ERROR =====
$msg
$sql
$sql_msg
-->";
		elseif ($this->debug_type == "none"):
			echo "
<div>
<b>DB관련 오류가 발생했습니다.</b><br />
$msg <br />
SQL: $sql <br />
ERROR: $sql_msg
</div>
";
		endif;
exit;
	}
}
?>
