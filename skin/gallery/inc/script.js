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

function popupImage(imageURL){
  imageHandle=open("","popupForImage","toolbar=no,location=no,status=no,manubar=no,scrollbars=no,resizable=yes,width=100,height=100,top=100,left=50");
  imageHandle.document.write("<title>이미지 크게 보기</title>"); 
  imageHandle.document.write("<style>");
  imageHandle.document.write("*{margin:0;padding:0;border:0;}"); 
  imageHandle.document.write("</style>");
  imageHandle.document.write("<img src=\""+imageURL+"\" onload=\"window.resizeTo(this.width+9,this.height+28);\" onclick=\"self.close();\" style=\"cursor:hand;\" title=\"마우스를 클릭하면 창이 닫힙니다.\">");
}