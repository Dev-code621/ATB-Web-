<?php

/**
 * Created by PhpStorm.
 * User: zeus
 * Date: 2019/7/10
 * Time: 6:14 PM
 */
class LoginHistory_model extends MY_Model
{
    public function insertNewLog($userId, $loginIp) {
        $insArr = array('user_id' => $userId,
            'login_timestamp' => time(),
            'login_date' => date('Y-m-d'),
            'login_ip' => $loginIp);
        $this->db->insert(self::TABLE_LOGIN_HISTORY, $insArr);
    }

    public function getLoginHistory($where = array()) {
        return $this->db->select('*')->from(self::TABLE_LOGIN_HISTORY)->where($where)->get()->result_array();
    }

    public function getLastLoginHistory($userId) {
       return $this->db->select('*')
            ->from(self::TABLE_LOGIN_HISTORY)
            ->where(array('user_id' => $userId))
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get()
            ->result_array();
    }


    /**
     **Dashboard APIs
     */

    public function getLastLoginTimeInDashBoard($userId) {
        $loginHistory = $this->getLastLoginHistory($userId);
        $now = time();
        $retVal = "Never Login";
        if(count($loginHistory) > 0) {
            return human_readable_date($loginHistory[0]['login_timestamp']);
        }
        return $retVal;
    }

    public function getLoginHistoryTimeInterval($userId, $startTimeString, $endTimeString) {
        $loginHistory = $this->db->select('*')->from(self::TABLE_LOGIN_HISTORY)
                                                ->where(array('login_date <' => $endTimeString, 'login_date>' => $startTimeString, 'user_id' => $userId))
                                                ->group_by('login_date')
                                                ->order_by('login_date', 'ASC')
                                                ->get()
                                                ->result_array();
        $insVal = array();
        if(count($loginHistory) > 30) {
            // count the number of month
            $startDate = new DateTime($startTimeString);
            $endDate = new DateTime($endTimeString);
            $interval = $endDate->diff($startDate);

            $differMonth = $interval->format("%d");

            if(intval($differMonth) > 1) {
                //make it as month array again
                $startYear = intval($startDate->format('Y'));
                $startMonth = intval($startDate -> format('m'));

                $endYear = intval($endDate -> format('Y'));
                $endMonth = intval($endDate -> format('m'));

                for($targetStartYear = $startYear; $targetStartYear < $endYear + 1 ; $targetStartYear ++) {
                    for($targetStartMonth = $startMonth; $targetStartMonth < 13; $targetStartMonth ++) {
                        if($targetStartMonth == $endMonth + 1 && $targetStartYear == $endYear) {
                            break;
                        }
                        if($targetStartMonth < 10) $targetStartMonthStr = '0'.$targetStartMonth;
                        else $targetStartMonthStr = strval($targetStartMonth);
                        $tmpResult = $this->db->select('COUNT(*) as counter')->from(self::TABLE_LOGIN_HISTORY)
                            ->where(array('user_id' => $userId))
                            ->like('login_date', $targetStartYear."-".$targetStartMonthStr, 'after')
                            ->get()
                            ->result_array();

                        $insVal = array();
                        array_push($insVal, array($targetStartYear."-".$targetStartMonthStr, $tmpResult[0]['counter']));
                    }
                }
            }
            else {
                 for($i = 0 ; $i < count($loginHistory); $i++) {
                     $tmpHistory = $this->getLoginHistory(array('user_id' => $userId, 'login_date' => $loginHistory[$i]['login_date']));
                     array_push($insVal, array($loginHistory[$i]['login_date'], count($tmpHistory)));
                 }
            }
        }
        else {
            for($i = 0 ; $i < count($loginHistory); $i++) {
                $tmpHistory = $this->getLoginHistory(array('user_id' => $userId, 'login_date' => $loginHistory[$i]['login_date']));
                array_push($insVal, array($loginHistory[$i]['login_date'], count($tmpHistory)));
            }
        }

        return $insVal;
    }
}