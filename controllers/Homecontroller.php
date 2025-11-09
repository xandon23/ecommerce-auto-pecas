<?php
class HomeController {
    public function index() {
        render('home', ['titulo' => 'Home']);
    }
}

