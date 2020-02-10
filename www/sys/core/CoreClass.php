<?php

namespace core;

use core\DBClass as DBClass;

class CoreClass extends DBClass
{
    public $flCfg = 'requisites.php';        //Путь до файла секвизитами доступа в БД и root администратора

    public $root;                        //Путь до корневой директории
    public $protocol;                    //Протокол
    public $host;                        //Хост

    public $ajax;                        //Передача данных по AJAX
    public $cfg;                        //Массив с конфигурацей

    public $meta = array();
    public $css = array();
    public $js = array();

    function __construct()
    {
    }

    //Основной конфиг системы
    public function setCfg()
    {
        $this->protocol = (!empty($_SERVER['HTTPS']) && 'off' !== strtolower($_SERVER['HTTPS']) ? "https" : "http");
        $this->host = $this->protocol . '://' . $_SERVER['HTTP_HOST'] . '/';
        if (!$this->root) {
            $this->root = $_SERVER['DOCUMENT_ROOT'];
            $rootLastCharacter = substr($this->root, -1);
            if ($rootLastCharacter != '/') $this->root .= '/';
        }
        if (!$this->cfg) {
            $cfgFl = $this->root . $this->flCfg;
            if (file_exists($cfgFl)) {
                if (!filesize($cfgFl) && file_exists($installFl)) include($installFl);
                else {
                    include($cfgFl);
                    if (!$this->pdo) $this->dbConnect('open', $bdtarget, $bdname, $bdlogin, $bdpass);    //Подключение бд
                }
            }
        }
    }

    //Заголовки
    public function showHeaders()
    {
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
        header('ETag: "10c24bc-4ab-457e1c1f"');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
        header('Cache-Control: post-check=900,pre-check=3600');
    }

    //Функция очистки от SQL-инъекции
    public function inpClean($str, $sql = false)
    {
        if (is_array($str)) {
            foreach ($str as $k => $v) $input[$k] = $this->inpClean($v, $sql);
        } else {
            $input = htmlentities($str, ENT_QUOTES, 'UTF-8');            // мнемонизировали строку.
            if (get_magic_quotes_gpc()) $input = stripslashes($input);    // убрали лишнее теперь экранирование.
            if ($sql) $input = mysql_real_escape_string($input);        // если нужен MySQL-запрос, то делаем соответствующую очистку.
            $input = strip_tags($input);                                //режем теги.
            //обрабатываем переводы строки.
            $input = str_replace("\n", " ", $input);
            $input = str_replace("\r", "", $input);
            $input = trim($input);
        }
        return $input;
    }

    //Подключение файла
    public function incFL($flWay)
    {
        if ($flWay) if (file_exists($flWay)) {
            $flHave = true;
            include_once($flWay);
        }
        return $flHave;
    }

    //Отображения шаблона
    public function view($name = '')
    {
        if ($name) {
            $viewFL = 'view/' . $name . '.php';
            $this->incFL($viewFL);
        }
    }

    //Определение IP адрес пользователя
    public function getIP()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) $ip = $_SERVER['HTTP_CLIENT_IP'];
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else $ip = $_SERVER['REMOTE_ADDR'];
        return $ip;
    }

    //Функция отправки E-mail-сообщения
    public function sendMail($prmAR = array())
    {
        if ($prmAR) {
            //Автоопределение не указанных параметров
            if ($this->cfg['adminMail']) {
                if (!$prmAR['From']) {
                    $prmAR['From'] = $this->cfg['adminMail'];
                    if (!$prmAR['FromName'] && $this->cfg['siteName']) $prmAR['FromName'] = $this->cfg['siteName'];
                }
                if (!$prmAR['To']) {
                    $prmAR['To'] = $this->cfg['adminMail'];
                    if (!$prmAR['ToName'] && $this->cfg['siteName']) $prmAR['ToName'] = $this->cfg['siteName'];
                }
            }
            if (!$prmAR['FromName']) $prmAR['FromName'] = $prmAR['From'];
            if (!$prmAR['ToName']) $prmAR['ToName'] = $prmAR['To'];
            if (!$prmAR['BodyAlt']) $prmAR['BodyAlt'] = $prmAR['Body'];

            //Определение шаблона отправки письма
            $tplFLDefault = $this->root . 'view/message.view.php';
            if ($prmAR['Template']) $tplFL = str_replace('.view', '.' . $prmAR['Template'] . '.view', $tplFLDefault);
            if (file_exists($tplFL) && filesize($tplFL)) $tpl = file_get_contents($tplFL);
            elseif (file_exists($tplFLDefault) && filesize($tplFLDefault)) $tpl = file_get_contents($tplFLDefault);

            //Замена текста в шаблоне
            if (!$tpl) $tpl = '{message}';
            $tpl = str_replace('{message}', $prmAR['Body'], $tpl);
            $tpl = str_replace('{host}', $this->host, $tpl);

            //Отправка
            $mail = new \lib\classes\PHPMailer\PHPMailer();
            $mail->CharSet = 'UTF-8';
            $mail->From = $prmAR['From'];
            $mail->FromName = $prmAR['FromName'];
            $mail->Subject = $prmAR['Title'];
            $mail->AltBody = $tpl;
            $mail->MsgHTML($tpl);
            $mail->AddAddress($prmAR['To'], $prmAR['ToName']);
            if ($mail->Send()) $res = 1;
        }
        return $res;
    }

    //Вызов функций файловой системы
    public function str($action = '', $data = '')
    {
        $str = new \lib\classes\StrClass;
        if ($action) {
            switch ($action) {
                case 'phone':
                    $res = $str->strToPhone($data);
                    break;
                case 'phone-letters':
                    $res = $str->strToPhone($data, true);
                    break;
                case 'createDate':
                    $res = $str->createDate($data);
                    break;
                case 'encodestring':
                    $res = $str->encodestring($data);
                    break;
                default:
                {
                }
            }
        } else $res = $str;
        return $res;
    }

    //Функции вывода HTML-страницы
    public function htmlHead()
    {
        $meta = $this->metaAR();
        if (!$meta) $meta = array();
        $css = $this->cssAR();
        if (!$css) $css = array();
        if (!$js) $js = array();
        $head = array_merge($meta, $css, $js);
        $html = '<!DOCTYPE html><html lang="ru"><head>';
        if ($head) foreach ($head as $ln) $html .= $ln;
        $html .= '</head><body>';
        echo $html;
    }

    public function htmlFoot()
    {
        if ($this->cfg['showJS'] == 'down') {
            $js = $this->jsAR();
            if ($js) foreach ($js as $ln) echo $ln;
        }
        echo '</body></html>';
    }

    public function headLn($ln, $dir = '', $tpl = '')
    {
        if ($ln) foreach ($ln as $k => $v) if ($v) {
            $v = $this->lnWay($v);
            $ln[$k] = str_replace('{ln}', $v, $tpl);
        }
        return $ln;
    }

    //Генерация мета-тегов
    public function metaAR()
    {
        $ln = $this->meta;
        if ($ln) {
            if (!$ln['charset']) $ln['charset'] = 'utf-8';
            if (!$ln['favicon']) {
                $fl = 'images/favicon.ico';
                if (file_exists($this->root . $fl)) $ln['favicon'] = $fl;
            }
            if (!stristr($ln['favicon'], '//')) $ln['favicon'] = $this->host . $ln['favicon'];
            if ($ln['title']) $ln['title'] = '<title>' . $this->inpClean($ln['title']) . '</title>';
            if ($ln['charset']) $ln['charset'] = '<meta charset="' . $ln['charset'] . '">';
            $metaBase = array('title', 'charset', 'canonical', 'alternate', 'favicon');
            foreach ($ln as $mKey => $mVal) {
                if (!in_array($mKey, $metaBase)) $ln[$mKey] = '<meta name="' . $mKey . '" content="' . $this->inpClean($mVal) . '">';
            }
            if ($ln['favicon']) $ln['favicon'] = '<link rel="shortcut icon" href="' . $ln['favicon'] . '">';
        }
        return $ln;
    }

    //Функции по работе с CSS и JS
    public function cssAR()
    {
        if ($this->css) $ln = $this->css; else $ln[] = '/css/style.css';
        if ($ln) $ln = $this->headLn($ln, 'css', '<link href="{ln}" type="text/css" rel="stylesheet">');
        return $ln;
    }

    public function addCSS($fl)
    {
        if (is_array($fl)) foreach ($fl as $v) $this->addCSS($v);
        elseif (!in_array($fl, $this->css)) $this->css[] = $fl;
    }

    public function jsAR()
    {
        if ($this->js) $ln = $this->js;
        if ($ln) $ln = $this->headLn($ln, 'js', '<script src="{ln}"></script>');
        return $ln;
    }

    public function addJS($fl)
    {
        if (is_array($fl)) foreach ($fl as $v) $this->addJS($v);
        elseif (!in_array($fl, $this->js)) $this->js[] = $fl;
    }

    public function lnWay($v)
    {
        if (!stristr($v, '//')) $v = $this->host . $v;
        if (stristr($v, '&amp;')) $v = str_replace('&amp;', '&', $v);
        $v = str_replace(array('http://', 'https://'), '//', $v);
        return $v;
    }
}