<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Finances_model
 *
 * @author SBI CSP
 */

defined('BASEPATH') or exit('No direct script access allowed');

class Finances_model extends CI_Model
{

    public function add_finance($data)
    {
        $this->db->insert('tbl_finance', $data);

        return $this->db->insert_id();
    }

    public function get_financesEntry($condition, $offset) {

        $this->db->select("f.id,f.description,f.amount,f.type,f.finance_type,f.cheque_number,f.is_complate,f.complate_date,
                DATE_FORMAT(date, '%d %b %Y')as date,
                DATE_FORMAT(cheque_date, '%d %b %Y')as cheque_date,
                (SELECT name FROM tbl_touser WHERE id = f.to_user)as to_username");
        $this->db->from('tbl_finance f');
        $this->db->where($condition, NULL, FALSE);
        $this->db->order_by('id', 'desc');
        $this->db->limit(10, $offset);
        $query = $this->db->get();
        $data = $query->result_array();

        return $data;
    }

    public function get_barchardata($user_id, $type) {
        $query = $this->db->query("
				    	SELECT 
				    		f.id,f.description,f.amount,f.type,f.finance_type,f.cheque_number,f.is_complate,f.complate_date,
                            DATE_FORMAT(date, '%d %b %Y')as date,
                            DATE_FORMAT(cheque_date, '%d %b %Y')as cheque_date,
                            (SELECT name FROM tbl_touser WHERE id = f.to_user)as to_username,
                            (SELECT SUM(amount) FROM tbl_finance WHERE user_id = $user_id AND finance_type = 1 $type) as total_income,
                            (SELECT SUM(amount) FROM tbl_finance WHERE user_id = $user_id AND finance_type = 2 $type) as total_expenses,
                            ((SELECT SUM(amount) FROM tbl_finance WHERE user_id = $user_id AND finance_type = 1 $type) - (SELECT SUM(amount) FROM tbl_finance WHERE user_id = $user_id AND finance_type = 2 $type))as total_balance
			    		FROM tbl_finance f
                        WHERE
                            user_id = $user_id
                            $type
			    			AND MONTH(date) = MONTH(CURRENT_DATE())
                            AND YEAR(date) = YEAR(CURRENT_DATE())
                        ORDER BY id desc");
        $data = $query->result_array();

        return $data;
    }

    public function get_totalBalance($user_id, $type) {
        $query = $this->db->query("
				    	SELECT 
                            IFNULL((SELECT SUM(amount) FROM tbl_finance WHERE user_id = $user_id AND finance_type = 1 $type), 0) as total_income,
                            IFNULL((SELECT SUM(amount) FROM tbl_finance WHERE user_id = $user_id AND finance_type = 2 $type), 0) as total_expenses,
                            (IFNULL((SELECT SUM(amount) FROM tbl_finance WHERE user_id = $user_id AND finance_type = 1 $type), 0) - IFNULL((SELECT SUM(amount) FROM tbl_finance WHERE user_id = $user_id AND finance_type = 2 $type), 0))as total_balance
			    		FROM tbl_finance f
                        WHERE
                            user_id = $user_id
                            $type
                        group by total_balance
                        ORDER BY date asc");

        $data = $query->result_array();

        return $data;
    }

    public function DeleteFinanceEntry($FinanceId)
    {
        $this->db->delete('tbl_finance', array('id' => $FinanceId));
        return $this->db->affected_rows();
    }
}
