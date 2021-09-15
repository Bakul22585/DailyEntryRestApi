<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Category_model
 *
 * @author SBI CSP
 */

defined('BASEPATH') or exit('No direct script access allowed');

class User_model extends CI_Model
{

    public function add_user($data)
    {
        $this->db->insert('tbl_user', $data);

        return $this->db->insert_id();
    }

    public function login_user($data) {
        $where = "(username = '". $data['username']."' OR email = '". $data['username']."') AND password = '". $data['password']."' ";
        $this->db->select('id,firstname,lastname,username,email,img');
        $this->db->from('tbl_user');
        $this->db->where($where);
        $query = $this->db->get();
        $data = $query->result_array();

        return $data;
    }

    public function add_touser($data) {
        $this->db->insert('tbl_touser', $data);

        return $this->db->insert_id();
    }

    public function get_touser($data) {
        $this->db->select('*');
        $this->db->from('tbl_touser');
        $this->db->where($data);
        $query = $this->db->get();
        $data = $query->result_array();

        return $data;
    }

    public function getwithdrawRequestList($condition, $offset, $field, $order)
    {
        $this->db->select('wr.*,u.fullname');
        $this->db->from('tbl_withdraw_request wr');
        $this->db->join('tbl_user u', 'u.id = wr.user_id', 'left');
        $this->db->where($condition);
        $this->db->order_by($field, $order);
        $this->db->limit(10, $offset);
        $query = $this->db->get();
        $data = $query->result_array();

        return $data;
    }

    public function CheckUserField($field, $value) {
        $this->db->select('*');
        $this->db->from('tbl_user');
        $this->db->where($field, $value);
        $query = $this->db->get();
        $data = $query->result_array();

        return $data;
    }

    public function editUser($id, $Fields) {
        $this->db->where('id', $id);
        $this->db->update('tbl_user', $Fields);
        return $this->db->affected_rows();
    }
}
