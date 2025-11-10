<?php

class accountAction
{
    private $db = null;
    private $aes = null;

    public function init()
    {
        if ($this->db == null) {
            $this->db = MyPDO::getInstance(HOST, USER, PWD, DB_NAME, PORT);
        }
        if (!isset($_SESSION['username'])) {
            msgBox(1, '请登录系统！', '?act=index&mod=admin');
        }
    }

    public function index()
    {
        require './htmls/admin_index.html';
    }

    //展示分类数据
    public function disAccount()
    {
        $whereStr = "";
        if (!isset($_GET['type'])) {
            $whereStr = "uid='" . $_SESSION['uid'] . "'";
        } else {
            $whereStr = "uid='" . $_SESSION['uid'] . "' and type='" . $_GET['type'] . "'";
        }

        $accounts = $this->db->fetchAll('account', $whereStr);
        if (!$accounts) {
            msgBox(1, '暂无数据!', '?act=index&mod=account');
        }
        require './htmls/account.html';
    }

    public function addAccount()
    {
        if (empty($_POST['account']) || empty($_POST['company']) || empty($_POST['password'])) {
            echo -2;
            exit;
        }
        $post = $this->clearData($_POST);
        $arr = array();
        $arr['company'] = $post['company'];
        $arr['account'] = $post['account'];
        $arr['password'] = AESUtil::getInstance()->Encry($post['password']);
        //$arr['desc'] = $desc ? $desc : '';
        $arr['desc'] = $post['desc'] ? AESUtil::getInstance()->Encry($post['desc']) : '';
        $arr['type'] = addslashes($post['type']);
        $arr['uid'] = $_SESSION['uid'];

        //判断账号是否已存在于数据库
        $whereStr = "uid='" . $_SESSION['uid'] . "' and company='" . $post['company'] . "' and account='" . $post['account'] . "'";
        $row = $this->db->fetch('account', $whereStr);
        if (isset($row['id'])) {
            echo 0;
            exit;
        }

        if ($this->db->insert('account', $arr)) {
            echo 1;
            exit;
        }
        echo -1;
        exit;
    }

    public function decryptAccount()
    {
        $id = (int)$_POST['id'];

        if (empty($id) || empty($key)) {
            echo -2;
            exit;
        }

        $row = $this->db->fetch('account', 'id=:id', array(':id' => $id));

        if ($row) {
            $arr = array();
            $arr['company'] = $row['company'];
            $arr['account'] = $this->aes->decrypt($row['account']);
            $arr['password'] = $this->aes->decrypt($row['password']);
            $arr['desc'] = empty($row['desc']) ? '' : $this->aes->decrypt($row['desc']);

            //print_r($arr);
            //清理数据，防止首尾出现空格
            //$arr = $this->clearData($arr);

            echo json_encode($arr);
            exit;
        }
        echo -1;
        exit;
    }


    public function changeAccount()
    {
        if (empty($_POST['id']) || empty($_POST['account']) || empty($_POST['company']) || empty($_POST['password'])) {
            echo -2;
            exit;
        }
        $post = $this->clearData($_POST);

        $id = (int)$post['id'];
        $arr = array();
        $arr['type'] = $post['type'];
        $arr['company'] = $post['company'];
        $arr['account'] = $post['account'];

        $arr['password'] = AESUtil::getInstance()->Encry($post['password']);
        $arr['desc'] = $post['desc'] ? AESUtil::getInstance()->Encry($post['desc']) : '';

        if ($this->db->update('account', $arr, 'id=' . $id)) {
            echo 1;
            exit;
        }
        echo -1;
        exit;
    }

    //菜单查询，根据名字查询
    public function searchAccount()
    {
        $search = $this->clearData($_GET['search']);
        if (empty($search)) {
            msgBox(-1, 'search empty');
        }
        $whereStr = " company like '%" . $_GET['search'] . "%' ";
        $accounts = $this->db->fetchAll('account', $whereStr);

        if (!$accounts) {
            msgBox(1, '暂无数据!', '?act=index&mod=account');
        }
        require './htmls/account.html';
    }

    //删除账户
    public function deleteAccount()
    {
        $id = (int)$_POST['id'];
        //检查用户输入密码与用户登录密码是否一致
        $whereStr = "id='" . $id . "'";
        $result = $this->db->delete('account', $whereStr);
        //echo $result;
        if ($result) {
            echo 1;
        } else {
            echo -1;
        }
    }

    //编辑账户，展示明文
    public function getAccountById()
    {
        $id = (int)$_POST['id'];
        $whereStr = "id='" . $id . "'";
        $row = $this->db->fetch('account', $whereStr);
        if (!$row) {
            echo -2;
            exit;
        }
        $arr = array();
        $arr['type'] = $row['type'];
        $arr['company'] = $row['company'];
        $arr['account'] = $row['account'];

        $arr['password'] = AESUtil::getInstance()->Decry($row['password']);
        if (!empty($row['desc']))
            $arr['desc'] = AESUtil::getInstance()->Decry($row['desc']);
        else
            $arr['desc'] = "";
        echo json_encode($arr);
        return json_encode($arr);
    }

    public function clearData($arr)
    {
        if (is_array($arr)) {
            foreach ($arr as $k => $v) {
                $arr[$k] = addslashes(trim($v));//去掉空格，处理特殊符号
            }
        } else {
            $arr = addslashes(trim($arr));
        }
        return $arr;
    }
}