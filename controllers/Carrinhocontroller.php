<?php
class CarrinhoController {
    public function index() {
        render('carrinho', ['titulo' => 'carrinho']);
    }
}