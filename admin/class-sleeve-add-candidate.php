<?php
//add new candidate, update candidate
class Sleeve_Add_Candidate {
    public function __construct($data) {
        $this->data = $data;
    }

    public function add_candidate() {
        $user_id = wp_insert_user(array(
            'user_login' => $this->data['username'],
            'user_pass'  => $this->data['password'],
            'user_email' => $this->data['email'],
            'role'       => 'candidate',
        ));

        if (is_wp_error($user_id)) {
            return $user_id;
        }

        // Add candidate meta data
        update_user_meta($user_id, 'first_name', $this->data['first_name']);
        update_user_meta($user_id, 'last_name', $this->data['last_name']);
        update_user_meta($user_id, 'phone', $this->data['phone']);

        return $user_id;
    }

    public function update_candidate($user_id) {
        $userdata = array(
            'ID'         => $user_id,
            'user_email' => $this->data['email'],
        );

        if (!empty($this->data['password'])) {
            $userdata['user_pass'] = $this->data['password'];
        }

        $result = wp_update_user($userdata);

        if (is_wp_error($result)) {
            return $result;
        }

        // Update candidate meta data
        update_user_meta($user_id, 'first_name', $this->data['first_name']);
        update_user_meta($user_id, 'last_name', $this->data['last_name']);
        update_user_meta($user_id, 'phone', $this->data['phone']);

        return $result;
    }
}
