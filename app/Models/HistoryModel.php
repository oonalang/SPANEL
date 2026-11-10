<?php

namespace App\Models;

use CodeIgniter\Model;

class HistoryModel extends Model
{
    protected $table      = 'history';
    protected $primaryKey = 'id_history';
    protected $allowedFields = ['keys_id', 'user_do', 'info'];
    protected $useTimestamps = true;
    
    public function getAll($limit = 10, $orderBy = "DESC")
    {
        return $this->where('user_do', (string) session('unames'))->limit($limit)
            ->orderBy('id_history', $orderBy)
            ->get()->getResultObject();
    }  
    public function DeleteAllHistory()
    {
        return $this->where('user_do', (string) session('unames'))->delete();
    }    
}
