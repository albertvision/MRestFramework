<?php

namespace App;

class Index extends \App\MainClass {

    public function get($id = null) {
        return [
            'message' => 'Thanks for watching!',
            'videos' => [
                'count' => 14,
                'minutes_total' => 205,
                'average_mitutes_per_video' => 205/14
            ]
        ];
    }

    public function post() {
        return [
            'action' => 'add new'
        ];
    }

    public function put() {
        return [
            'action' => 'replace'
        ];
    }
    
    public function delete() {
        return [
            'action' => 'delete'
        ];
    }
}
