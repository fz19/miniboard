// JavaScript Document
function formCheck(form) {
	if (form.password && form.password.value == "") {
		alert("��й�ȣ�� �Է��ϼ���.");
		form.password.focus();
		return false;
	}else if (form.name.value == "") {
		alert("�ۼ��ڸ��� �Է��ϼ���.");
		form.name.focus();
		return false;
	}else if (form.subject.value == "") {
		alert("������ �Է��ϼ���.");
		form.subject.focus();
		return false;
	}else if (form.content.value == "") {
		alert("������ �Է��ϼ���.");
		form.content.focus();
		return false;
	}
	return true;
}

function passwordCheck(form) {
	if (form.password.value == "") {
		alert("��й�ȣ�� �Է��ϼ���.");
		form.password.focus();
		return false;
	}
	return true;
}

function searchCheck(form) {
	if (form.s_text.value == "") {
		alert("�˻�� �Է��ϼ���.");
		form.s_text.focus();
		return false;
	}
}