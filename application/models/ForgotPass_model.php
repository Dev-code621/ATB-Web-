<?php

/**
 * Created by PhpStorm.
 * User: zeus
 * Date: 2019/7/22
 * Time: 2:45 PM
 */
class ForgotPass_model extends MY_Model
{
    public function insertNewRecord($insArr) {
        if($this->db->insert(self::TABLE_FORGOT_PASS_LIST, $insArr)) {
            return $this->db->insert_id();
        }
        else {
            return -1;
        }
    }

    public function getForgotPassRequest($where = array()) {
        return $this->db->select('*')
            ->from(self::TABLE_FORGOT_PASS_LIST)
            ->where($where)
            ->get()
            ->result_array();
    }

    public function getUniqVerificationCode() {
        $inviteCode = "";
        while (true) {
            $inviteCode = self::generateVerificationCode();
            $existCodes = $this->getForgotPassRequest(array('request_verification_code' => $inviteCode, 'status' => 1));
            if(count($existCodes) == 0) {
                break;
            }
        }
        return $inviteCode;
    }

    public function generateVerificationCode() {
        $characters = '1234567890';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < 6; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    public function updateForgotPassRecord($set, $where) {
        $this->db->where($where);
        $this->db->update(self::TABLE_FORGOT_PASS_LIST, $set);
    }
    /*
     * Functions Using by mobile app
     */
    public function doForgotPassEmailVerify($user) {
        $this->updateForgotPassRecord(array('status' => 0, 'updated_at' => time()), array('user_id' => $user['id']));

        $newVerificationCode = $this->getUniqVerificationCode();
        $insArr = array(
            'user_id' => $user['id'],
            'email' => $user['user_email'],
            'request_verification_code' => $newVerificationCode,
            'status' => 1,
            'updated_at' => time(),
            'created_at' => time()
        );

        $this->insertNewRecord($insArr);
        return $newVerificationCode;
    }
}