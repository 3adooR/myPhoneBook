<?php

namespace app;

use core\CoreClass as CoreClass;

class PhoneBook extends CoreClass
{
    //Данные пользователя
    public $userID;
    public $userObj;

    //таблицы БД
    public $tbl = 'users';
    public $tblBook = 'users_book';

    //меню ЛК
    public $menu;
    public $userFields = array();    //передаваемые данные
    public $success;            //текст об успещности операции
    public $errors = array();        //ошибки
    public $edit = 0;
    public $upFile = '';

    function __construct()
    {
    }

    //Инициализация
    public function init($view = 'main')
    {
        if ($this->ajax) $this->body();
        else {
            $this->htmlHead();
            $this->view($view);
            $this->htmlFoot();
        }
    }

    public function body()
    {
        //Определение пункта меню
        if (isset($_GET['go'])) $this->menu = $this->inpClean($_GET['go']);

        //Инициализация пользователя
        if (isset($_SESSION['user'])) $this->userID = $_SESSION['user'];
        if ($this->userID) {
            $this->userObj = $this->getUser($this->userID);
            if ($this->menu == 'auth') $this->menu = 'list';
        }

        //Выход
        if ($this->menu == 'exit') $this->officeExit();

        //Остальные разделы ЛК
        else {
            if (!$this->menu) {
                if ($this->userID) $this->menu = 'list';
                else $this->menu = 'auth';
            }

            //Дополнительные функции разделов
            switch ($this->menu) {
                case 'auth':
                {
                    $this->officeAuth();
                    break;
                }
                case 'registration':
                {
                    $this->officeRegistration();
                    break;
                }
                case 'remember':
                {
                    $this->officeRemember();
                    break;
                }
                case 'info':
                {
                    $this->officeInfo();
                    break;
                }
                case 'list':
                {
                    $this->officeListActions();
                    break;
                }
                case 'list-edit':
                {
                    $this->officeListEdit();
                    break;
                }
                default:
                {
                }
            }

            //Вывод ошибок и уведомлений
            if ($this->errors) $this->showErrors();
            if ($this->success) $this->showSuccess();

            //Основное содержимое раздела
            $viewFL = 'office.' . $this->menu;
            if ($this->upFile) echo $this->upFile;
            else $this->view($viewFL);
        }
    }

    //Авторизация
    public function officeAuth()
    {
        if (isset($_POST['office-login']) && isset($_POST['office-password'])) {
            $login = $this->inpClean($_POST['office-login']);
            $password = $this->inpClean($_POST['office-password']);
            if ($login && $password) {
                if ($sql = $this->getFlds($this->tbl, 'u_id', "u_mail='$login' AND u_pass=MD5('$password')")) {
                    $uID = $sql->u_id;
                    $_SESSION['user'] = $uID;
                    $this->qUpd($this->tbl, array('u_lastvisit=NOW()', 'u_visits=u_visits+1'), 'u_id=' . $this->quote($uID));
                    $this->jsContent('list');
                }
            }
            if (!$uID) $this->errors[] = 'не верные реквизиты доступа.';
        }
    }

    //Выход из личного кабинета
    public function officeExit()
    {
        if (isset($_SESSION['user'])) unset($_SESSION['user']);
        $this->jsContent('auth');
    }

    //Регистрация
    public function officeRegistration()
    {
        if (isset($_POST['u_mail'])) {
            $this->checkCaptcha($_POST['captcha']);
            if ($_POST['captcha']) unset($_POST['captcha']);
            $this->userFields = $_POST;
            if ($this->userFields && !$this->errors) {
                foreach ($this->userFields as $k => $v) $this->userFields[$k] = $this->inpClean($v);
                if ($this->userFields['u_mail'] && $this->userFields['u_pass']) {
                    if ($check = $this->getFlds($this->tbl, 'u_id', "
						u_mail='" . $this->userFields['u_mail'] . "'
						", '', 1)) {
                        $this->errors[] = 'В системе уже зарегистрирован пользователь с таким e-mail адресом.';
                    } else {
                        $uId = $this->addUser($this->userFields);
                        if ($uId) {
                            $_SESSION['user'] = $uId;
                            $this->success = 'Вы успешно зарегистрировались!';
                            $this->jsContent('list');
                        } else $this->errors[] = 'Не удалось зарегистрироваться';
                    }
                }
            }
        }
    }

    //Восстановление пароля
    public function officeRemember()
    {
        if (isset($_POST['u_mail'])) {
            $this->checkCaptcha($_POST['captcha']);
            if ($_POST['captcha']) unset($_POST['captcha']);
            $this->userFields = $_POST;
            if ($this->userFields && !$this->errors) {
                foreach ($this->userFields as $k => $v) $this->userFields[$k] = $this->inpClean($v);
                if ($this->userFields['u_mail']) {
                    if ($check = $this->getFlds($this->tbl, 'u_id,u_mail', "
						u_mail='" . $this->userFields['u_mail'] . "'
						", '', 1)) {
                        $uID = $check->u_id;
                        $uMail = $check->u_mail;
                        $newPass = $this->generatePassword();
                        $this->changePassword($uID, $newPass);
                        $mesBody = "<p>Если это письмо доставлено Вам по ошибке - просто удалите его.</p>
							<h2>Реквизиты доступа в личный кабиент обновлены</h2> 
							<p>Ваш логин: <b>$uMail</b><br />
							Пароль: <b>$newPass</b></p>";
                        $prmAR = array(
                            'To' => $uMail,
                            'ToName' => $uMail,
                            'Title' => $this->cfg['siteName'] . ': восстановление пароля',
                            'Body' => $mesBody
                        );
                        if ($this->sendMail($prmAR)) $this->success = 'Реквизиты доступа отправлены на указанный электронный адрес.';
                    } else $this->errors[] = 'Пользователь с таким E-mail не наиден.';
                }
            }
        }
    }

    //Действия с записями книги
    public function officeListActions()
    {
        //Добавление записи
        if (isset($_POST['b_phone'])) {
            $this->menu = 'list-add';
            $this->userFields = $_POST;
            if ($this->userFields && !$this->errors) {
                foreach ($this->userFields as $k => $v) $this->userFields[$k] = $this->inpClean($v);
                if ($this->userFields['b_name'] && $this->userFields['b_phone']) {
                    if ($check = $this->getFlds($this->tblBook, 'b_id', "
						b_user='" . $this->userID . "'
						AND b_phone='" . $this->userFields['b_phone'] . "'
						", '', 1)) {
                        $this->errors[] = 'У Вас уже есть пользователь с таким номером телефона';
                    } else {
                        $id = $this->qIns(
                            $this->tblBook,
                            ['b_user', 'b_name', 'b_surname', 'b_phone', 'b_mail', 'b_photo'],
                            [$this->userID, $this->quote($this->userFields['b_name']), $this->quote($this->userFields['b_surname']), $this->quote($this->userFields['b_phone']), $this->quote($this->userFields['b_mail']), $this->quote($this->userFields['b_photo'])]
                        );
                        if (isset($_SESSION['last-file'])) {
                            $this->userFields['b_photo'] = $_SESSION['last-file'];
                            $this->userFields['b_photo'] = str_replace('b0', 'b' . $id, $this->userFields['b_photo']);
                            if (file_exists('upload/' . $_SESSION['last-file'])) {
                                rename('upload/' . $_SESSION['last-file'], 'upload/' . $this->userFields['b_photo']);
                                unset($_SESSION['last-file']);
                                $this->qUpd(
                                    $this->tblBook,
                                    ['b_photo=' . $this->quote($this->userFields['b_photo'])],
                                    'b_id=' . $this->quote($id) . ' AND b_user=' . $this->quote($this->userID)
                                );
                            }
                        }
                        $this->jsContent('list');
                    }
                } else $this->errors[] = 'Заполните обязательные поля формы';
            }

            //Удаление записи
        } elseif (isset($_POST['delID'])) {
            $delID = (int)$_POST['delID'];
            $this->delItm($this->tblBook, $delID);
        }
    }

    //Редактирование записи
    public function officeListEdit()
    {
        if (isset($_REQUEST['id'])) $this->edit = (int)$_REQUEST['id'];

        //Загрузка файла
        if ($_FILES['file']['tmp_name']) {
            $fName = 'u' . $this->userID . '-b' . $this->edit;
            $this->upFile = $this->uploadImage($fName);
            if (!$this->edit) $_SESSION['last-file'] = $this->upFile;

            //Сохранение записи
        } elseif ($this->edit) {
            if (isset($_POST['b_mail'])) {
                $this->userFields = $_POST;
                if ($this->userFields && !$this->errors) {
                    foreach ($this->userFields as $k => $v) $this->userFields[$k] = $this->inpClean($v);
                    if ($this->userFields['b_name'] && $this->userFields['b_phone']) {
                        $this->qUpd(
                            $this->tblBook,
                            [
                                'b_name=' . $this->quote($this->userFields['b_name']),
                                'b_surname=' . $this->quote($this->userFields['b_surname']),
                                'b_phone=' . $this->quote($this->userFields['b_phone']),
                                'b_mail=' . $this->quote($this->userFields['b_mail']),
                                'b_photo=' . $this->quote($this->userFields['b_photo']),
                            ],
                            'b_id=' . $this->quote($this->edit) . ' AND b_user=' . $this->quote($this->userID)
                        );
                        $this->success = 'Информация успешно обновлена';
                        //$this->success.='<p><a href="?go=list" oncclick="fancyClose()"><i class="far fa-list"></i> показать все записи</a></p>';
                    }
                }
            }
            if ($this->userFields) unset($this->userFields);
            if ($row = $this->getFlds($this->tblBook, '*', 'b_id=' . $this->quote($this->edit) . ' AND b_user=' . $this->quote($this->userID), '', 1)) {
                $this->userFields['b_name'] = $row->b_name;
                $this->userFields['b_surname'] = $row->b_surname;
                $this->userFields['b_phone'] = $row->b_phone;
                $this->userFields['b_mail'] = $row->b_mail;
                $this->userFields['b_photo'] = $row->b_photo;
            }
            //if(!$this->userFields['b_photo'])$this->userFields['b_photo']='noimage.jpg';
        }
    }

    //Загрузка изображения
    public function uploadImage($name = '', $fName = 'file')
    {
        $filename = $_FILES[$fName]['name'];
        $fileType = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $filename = $this->str('encodestring', $_FILES[$fName]['name']);
        $filename = $name . '.' . $fileType;
        $location = 'upload/' . $filename;
        $valid_extensions = ["jpg", "jpeg", "png"];
        if (in_array($fileType, $valid_extensions)) {
            if (move_uploaded_file($_FILES[$fName]['tmp_name'], $location)) {
                $upFile = $filename;
            }
        }
        if ($upFile) return $upFile;
    }

    //Отображение списка записей книги
    public function book()
    {
        if ($sql = $this->getFldsAr($this->tblBook, '*', 'b_user=' . $this->userID, 'b_name')) {
            echo '<table class="tablesorter"><thead>
				<!--<th>№</th>-->
				<th>Фото</th>
				<th>Имя Фамилия</th>				
				<th>Телефон</th>
				<th>Email</th>
				<th class="hideTxt">Действия</th>				
			</thead><tbody>';
            foreach ($sql as $row) {
                $num++;
                $img = 'images/noimage.jpg';
                if ($row->b_photo) $img = 'upload/' . $row->b_photo;
                echo '<tr id="b-' . $row->b_id . '">
					<!--<td>' . $num . '</td>-->
					<td><img src="' . $this->host . $img . '" alt="" /></td>
					<td>' . $row->b_surname . ' <b>' . $row->b_name . '</b></td>					
					<td>
						<div class="fRight">
							<a href="javascript:;" onclick="fancyMessage(\'Телефон буквами (зачём-то :)\',\'' . $this->str('phone-letters', $row->b_phone) . '\')"><i class="far fa-font"></i></a>
						</div>
						<a href="tel:' . $this->str('phone', $row->b_phone) . '">
							<i class="far fa-phone"></i> 
							' . $row->b_phone . '
						</a>
					</td>
					<td>
						<a href="mailto:' . $row->b_mail . '">
							<i class="far fa-envelope"></i> 
							' . $row->b_mail . '
						</a>
					</td>
					<td class="tdAction">
						<a href="?go=list-edit&id=' . $row->b_id . '">
							<i class="far fa-edit"></i>
						</a>
						<a href="javascript:;" onclick="del(' . $row->b_id . ')">
							<i class="far fa-trash-alt"></i>
						</a>
					</td>					
				</tr>';
            }
            echo '</tbody></table>';
        } else echo '<p>Записей пока нет</p>';
    }

    //получение данных пользователя по ID
    public function getUser($uID)
    {
        if ($sql = $this->getFlds($this->tbl, '*', 'u_id=' . $this->quote($uID))) $res = $sql;
        return $res;
    }

    //добавление пользователя
    public function addUser($vals)
    {
        if ($vals) foreach ($vals as $k => $v) $vals[$k] = $this->quote($v);
        $userIP = $this->quote($this->getIP());
        if ($vals['u_pass']) $vals['u_pass'] = 'MD5(' . $vals['u_pass'] . ')';
        return $this->qIns(
            $this->tbl,
            array('u_mail', 'u_pass', 'u_name', 'u_phone', 'u_datereg', 'u_lastvisit', 'u_visits', 'u_ip'),
            array($vals['u_mail'], $vals['u_pass'], $vals['u_name'], $vals['u_phone'], 'NOW()', 'NOW()', 1, $userIP)
        );
    }

    //смена пароля
    public function changePassword($uID, $newPass = '')
    {
        if (!$newPass) $newPass = $this->generatePassword();
        if ($uID) $this->qUpd($this->tbl, array("u_pass=MD5(" . $this->quote($newPass) . ")"), 'u_id=' . $this->quote($uID));
    }

    //генерация нового пароля
    public function generatePassword($length = 8)
    {
        $chars = 'abdefhiknrstyzABDEFGHKNQRSTYZ23456789';
        $numChars = strlen($chars);
        $string = '';
        for ($i = 0; $i < $length; $i++) $string .= substr($chars, rand(1, $numChars) - 1, 1);
        return $string;
    }

    //Проверка капчи
    public function checkCaptcha($captcha)
    {
        if (isset($_SESSION['captcha']) && $_SESSION['captcha'] === $captcha) {
        } else $this->errors[] = 'Вы не Верно ввели защитный код.';
        unset($_SESSION['captcha']);
    }

    //Отображение ошибок
    public function showErrors()
    {
        if ($this->errors) {
            foreach ($this->errors as $e) $errText .= '<p>' . $e . '</p>';
            if ($errText) echo '<script>officeAlert(\'' . $errText . '\');</script>';
        }
    }

    //Отображение ошибок
    public function showSuccess()
    {
        if ($this->success) {
            echo '<script>officeSuccess(\'' . $this->success . '\');</script>';
        }
    }

    //AJAX загрузка контента
    public function jsContent($ctn)
    {
        echo '<script>loadCtn(\'' . $ctn . '\')</script>';
    }
}