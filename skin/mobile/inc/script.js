// JavaScript Document
function formCheck(form) {
	if (form.password && form.password.value == "") {
		alert("비밀번호를 입력하세요.");
		form.password.focus();
		return false;
	}else if (form.name.value == "") {
		alert("작성자명을 입력하세요.");
		form.name.focus();
		return false;
	}else if (form.subject.value == "") {
		alert("제목을 입력하세요.");
		form.subject.focus();
		return false;
	}else if (form.content.value == "") {
		alert("내용을 입력하세요.");
		form.content.focus();
		return false;
	}
	return true;
}

function passwordCheck(form) {
	if (form.password.value == "") {
		alert("비밀번호를 입력하세요.");
		form.password.focus();
		return false;
	}
	return true;
}

function searchCheck(form) {
	if (form.s_text.value == "") {
		alert("검색어를 입력하세요.");
		form.s_text.focus();
		return false;
	}
}