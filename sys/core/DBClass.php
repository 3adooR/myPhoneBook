<?php

namespace core;

class DBClass
{
    public $pdo;                    //Обращение к PDO

    public $bdname;                 //Реквизиты к БД
    public $bdlogin;
    public $bdpass;
    public $bdtarget;
    public $bdcharset;

    function __construct()
    {
    }

    /*
    Подключение / отключение БД
    dbconnect('тип','target','имя бд','логин к бд','пароль к бд','установить кодировку')
    p.s. Если тип = 'close' - остальные поля игнорируются и являются не обязательными
    */
    public function dbConnect($type, $bdtarget = '', $bdname = '', $bdlogin = '', $bdpass = '', $bdcharset = 'utf8')
    {
        if ($type == 'close') {
            $this->pdo = null;
        } else {
            try {
                $this->pdo = new \PDO('mysql:host=' . $bdtarget . ';dbname=' . $bdname, $bdlogin, $bdpass);
                if ($bdcharset) $this->pdo->exec("set names " . $bdcharset);
                $this->bdname = $bdname;
                $this->bdlogin = $bdlogin;
                $this->bdpass = $bdpass;
                $this->bdtarget = $bdcharset;
                $this->bdcharset = $bdcharset;

            } catch (PDOException $e) {
                print "<b>Нет соединения с базой данных!</b><br />Проверьте настройки файла requisites.php<br /><br />Ошибка: " . $e->getMessage();
                die();
            }
        }
    }

    /*
    Выборка из базы
    getFlds('название таблицы',['перечисление полей'],['условие - WHERE'],['порядок - ORDER'],['кол-во Limit'])
    p.s. по умолчанию запрос выглядит так "SELECT * FROM 'название таблицы'"
    */
    public function getFlds($tbl, $fields = '*', $where = '', $order = '', $limit = '', $showQ = 0)
    {
        if ($tbl) {
            if ($where) {
                if (is_array($where)) $where = implode(' AND ', $where);
                $where = 'WHERE ' . $where;
            }
            if ($order) $order = 'ORDER BY ' . $order;
            if ($limit) {
                if (!stristr($limit, ',')) $limit = '0,' . $limit;
                $limit = 'LIMIT ' . $limit;
            }
            $q = "SELECT $fields FROM $tbl $where $order $limit";
            if ($showQ) echo 'SQL: ' . $q . '<br />';
            $q = $this->query($q);
            if ($q) {
                $rows = $q->rowCount();
                if ($showQ) echo 'rows: ' . $rows . '<br />';
                if ($rows) {
                    if ($rows > 1) while ($obj = $q->fetchObject()) {
                        $res[] = $obj;
                        if ($showQ) echo 'obj: ' . print_r($obj) . '<br />';
                    } else $res = $q->fetchObject();
                }
            }
        }
        return $res;
    }

    //Тоже самое, только всегда возвращает массив
    public function getFldsAr($tbl, $fields = '*', $where = '', $order = '', $limit = '', $showQ = 0)
    {
        $sql = $this->getFlds($tbl, $fields, $where, $order, $limit, $showQ);
        if ($sql) {
            if ($showQ) print_r($sql);
            if (!is_array($sql)) $res[] = $sql; else $res = $sql;
        }
        return $res;
    }

    //Проверка существования таблицы в БД
    public function haveTbl($tbl, $show = 1)
    {
        if ($q = $this->query("SHOW TABLES LIKE '$tbl'")) $res = $q->rowCount();
        if (!$res && $show) {
            echo '<div class="dError">В базе данных нет таблицы <b>' . $tbl . '</b></div>';
            die();
        }
        return $res;
    }

    //Обновление строки в таблице
    public function qUpd($tbl, $kvAR = array(), $qWhere = '', $showQ = 0)
    {
        if ($kvAR) {
            $q = "UPDATE " . $tbl . " SET " . implode($kvAR, ", ");
            if ($qWhere) $q .= " WHERE " . $qWhere;
            if ($showQ) echo '<p>' . $q . '</p>';
            $this->query($q) or die('Ошибка сохранения инормации:<br />' . $q . '<br />' . $this->errorInfo());
        }
    }

    //Добавление строки в таблицу
    public function qIns($tbl, $keys, $vals, $showQ = 0)
    {
        $q = "INSERT INTO " . $tbl . " (" . implode($keys, ", ") . ") VALUES (" . implode($vals, ", ") . ")";
        if ($showQ) echo $q;
        $this->query($q) or die('Не удалось добавить запись:<br />' . $q . '<br />' . print_r($this->errorInfo()));
        return $this->lastId();
    }

    //Удаление строки в таблице
    public function delItm($tbl = '', $id = '', $idField = 'b_id')
    {
        if (is_array($id)) $qWhere = $idField . ' IN (' . implode(',', $id) . ')'; else $qWhere = $idField . '=' . $this->quote($id);
        $q = "DELETE FROM $tbl WHERE " . $qWhere;
        $this->query($q) or die('Не удалось удалить запись:<br />' . $q . '<br />' . print_r($this->errorInfo()));
    }

    /* PDO-функции */
    public function errorInfo()
    {
        return $this->pdo->errorInfo();
    }

    public function quote($sql)
    {
        return $this->pdo->quote($sql);
    }

    public function query($sql)
    {
        return $this->pdo->query($sql);
    }

    public function prepare($sql)
    {
        return $this->pdo->prepare($sql);
    }

    public function excute($sql)
    {
        return $this->pdo->excute($sql);
    }

    public function fetch()
    {
        return $this->pdo->fetch($sql);
    }

    public function fetchObject()
    {
        return $this->pdo->fetch(PDO::FETCH_OBJ);
    }

    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    public function lastId()
    {
        return $this->pdo->lastInsertId();
    }

    public function rowCount()
    {
        return $this->pdo->rowCount();
    }
}