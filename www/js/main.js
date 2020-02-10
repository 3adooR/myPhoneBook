const selfHost = '//' + window.location.host;

function init() {
    //AJAX вместо перехода по ссылкам
    $('a').click(function (e) {
        let ctn = $(this).attr('href');
        if (ctn) {
            if (ctn.indexOf('?go=') >= 0) {
                e.preventDefault();
                ctn = ctn.replace('?go=', '');
                loadCtn(ctn);
            }
        }
    });

    //Добавление записи в книгу
    $('#form input[name=office-list-add]').click(function () {
        let fields = ['b_name', 'b_phone', 'b_mail'];
        validForm(fields);
    });

    //Редактирование записи в книге
    $('#form input[name=office-list-edit]').click(function () {
        let fields = ['b_name', 'b_phone', 'b_mail'];
        validForm(fields);
    });

    //Валидация восстановления пароля
    $('#form input[name=office-auth]').click(function () {
        let fields = ['office-login', 'office-password'];
        validForm(fields);
    });

    //Валидация регистрации
    $('#form input[name=office-registration]').click(function () {
        let fields = ['u_mail', 'u_pass', 'u_name', 'u_phone', 'captcha'];
        validForm(fields);
    });

    //Валидация восстановления пароля
    $('#form input[name=office-remember]').click(function () {
        let fields = ['u_mail', 'captcha'];
        validForm(fields);
    });

    //Сортировка таблиц
    $(".tablesorter").tablesorter({
        theme: "bootstrap",
        widthFixed: true
    });

    //Маска полей с телефоном
    $('.phone').mask('+7 (999) 999-99-99');
}

//AJAX загрузка контента
function loadCtn(ctn) {
    let url = selfHost + '/?go=' + ctn;
    $.post(url, {
        ajax: true
    }, function (data) {
        if (data) {
            history.pushState({}, '', url);
            content(data);
        }
    });
}

function content(data) {
    $('.content').html(data);
    init();
}

//Валидация форм
function validForm(fields) {
    if (fields) {
        let errorText = '';
        let errors = [];
        let errorInp = false;
        let fld = '';
        let fldVal = '';
        let fldType = '';
        fields.forEach(function (elem) {
            fld = '#form input[name=' + elem + ']';
            $(fld).removeClass('err');
            fldVal = $(fld).val();

            if (!fldVal) {
                $(fld).addClass('err');
                if (!errorInp) $(fld).focus();
                errorInp = true;
            }

            fldType = $(fld).attr('type');
            if (fldType == 'email') {
                if (!validateEmail(fldVal)) errors.push('Не корректный e-mail.');
            } else {
                if (fldType == 'tel') {
                    if (!validatePhone(fldVal)) errors.push('Не корректный телефон.');
                } else {
                    if (fldType == 'password') {
                        if (!validatePassword(fldVal)) errors.push('Пароль должен содержать и цифры, и буквы.');
                    }
                }
            }
        });

        if (errorInp) errors.push('Не заполнены все поля формы.');

        if (errors) {
            errors.forEach(function (err) {
                errorText += '<p>' + err + '</p>';
            });
        }

        if (errorText) officeAlert(errorText);
        else {
            var frm = $('#form');
            var frmData = frm.serialize();
            var frmUrl = frm.attr('action');
            var frmLink = selfHost + '/' + frmUrl + '&ajax';
            $.post(frmLink, frmData, function (data) {
                content(data);
            });
        }
    }
}

//Валидация E-mail
function validateEmail(email) {
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}

//Валидация телефона
function validatePhone(phone) {
    var phoneNum = phone.replace(/[^\d]/g, '');
    if (phoneNum.length > 6 && phoneNum.length < 12) return true;
}

//Валидация пароля
function validatePassword(passwd) {
    const withoutSpecialChars = /^[^-() /]*$/
    const containsLetters = /^.*[a-zA-Z]+.*$/
    const containsDigits = /^.*[^\D]+.*$/
    if (
        withoutSpecialChars.test(passwd) &&
        containsLetters.test(passwd) &&
        containsDigits.test(passwd)
    ) {
        return true;
    }
}

//Вывод ошибок и уведомлений
function officeAlert(str) {
    fancyMessage('Ошибка', str);
}

function officeSuccess(str) {
    fancyMessage('Успешно', str);
}

function fancyMessage(title, str) {
    let message = '<div class="message"><h2>' + title + '</h2><p>' + str + '</p></div>';
    $.fancybox.open(message);
}

function fancyClose() {
    $.fancybox.getInstance().close();
}

//Скрытие элемента
function hideElem(elemId) {
    $('#' + elemId).fadeOut("slow");
}

//Удаление записи
function del(id) {
    let url = selfHost + '/?go=list';
    $.post(url, {
        ajax: true,
        delID: id
    }, function (data) {
        if (data) hideElem('b-' + id);
    });
}

function uploadFile(id) {
    let fd = new FormData();
    let files = $('#file')[0].files[0];
    if (files && files.size > 2097152) officeAlert('Размер файла не должен превышать 2Mb');
    else {
        fd.append('file', files);
        $.ajax({
            url: selfHost + '/?go=list-edit&ajax&id=' + id,
            type: 'post',
            data: fd,
            contentType: false,
            processData: false,
            success: function (response) {
                if (response) {
                    let imgWay = selfHost + '/upload/' + response + '?rnd=' + Math.random();
                    $('#img').attr("src", imgWay);
                    $('#b_photo').val(response);
                    $('.preview img').show();
                }
            }
        });
    }
}

$(function () {
    //Инициализация
    init();

    //Автопродление сессии
    setInterval(function () {
        $.get(selfHost + '/sys/sessionLoad.php');
    }, 30000);
});