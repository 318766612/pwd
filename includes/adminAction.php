<?php

class adminAction
{
    private $db = null;
    private $aes = null;

    public function init()
    {
        if ($this->db == null) {
            $this->db = MyPDO::getInstance(HOST, USER, PWD, DB_NAME, PORT);
        }
        if (!isset($_SESSION['username'])) {
            if (!isset($_GET['act']) || empty($_GET['act'])) {
                header("Location:?act=error&mod=admin");
            } else if ($_GET['act'] != 'index' && $_GET['act'] != 'login' && $_GET['act'] != 'error' && $_GET['act'] != 'registe') {
                msgBox(1, '请输入正确的用户名和密码！', '?act=index&mod=admin');
            }
        }
    }

    public function login()
    {
        if (empty($_POST['username']) || empty($_POST['password'])) {
            msgBox(-1, "请输入正确的用户名和密码！");
        } else {
            $pwd=AESUtil::getInstance()->Encry($_POST['password']);
            $whereStr = "name='" . $_POST['username'] . "'";
            $info = $this->db->fetch('admin', $whereStr);
            if ($info['password'] === $pwd) {
                $_SESSION['uid'] = $info['id'];
                $_SESSION['username'] = $info['name'];
                $_SESSION['password'] = $info['password'];
                header("Location:?act=main&mod=admin");
            } else {
                msgBox(-1, "用户名或密码密码错误……");
            }
        }
    }

    //统计密码数量
    public function main()
    {
        if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
            header("Location:?act=index&mod=admin");
        } else {
            $nums = array();
            //从数据库查询所有类型的账号对应数量
            foreach (SystemConfig::$types as $k => $v) {
                $num = $this->db->query("select count(id) as num from account where type=" . $k . " and uid=" . $_SESSION['uid'], 'Row');
                $nums[$k] = $num['num'];
            }
            require './htmls/admin.html';
        }
    }

    public function index()
    {
        require './htmls/index.html';
    }

    public function registe()
    {
        if (empty($_POST['username']) || empty($_POST['password'])) {
            msgBox(-1, '用户名或密码为空！');
        }
        //判断是否存在
        $whereStr = "name='" . $_POST['username'] . "'";
        $info = $this->db->fetch('admin', $whereStr);
        if ($info) {
            msgBox(-1, '注册失败,账号已存在');
            exit;
        }
        $arr = array();
        $arr['name'] = $_POST['username'];
        $pwd=AESUtil::getInstance()->Encry($_POST['password']);
        echo $pwd;
        $arr['password'] = $pwd;
        if ($this->db->insert('admin', $arr)) {
            msgBox(1, '注册成功，请登录！', '?act=index&mod=admin');
        } else {
            msgBox(-1, '注册失败');
        }
    }

    public function logout()
    {
        unset($_SESSION);
        session_destroy();
        header("Location:?act=index&mod=admin");
    }

    public function error()
    {
        require './htmls/error.html';
    }

}