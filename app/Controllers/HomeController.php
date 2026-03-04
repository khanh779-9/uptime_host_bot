<?php

declare(strict_types=1);

class HomeController extends Controller
{
    public function index(): void
    {
        if (!empty($_SESSION['user_id'])) {
            $this->redirect('monitor/index');
        }

        $this->view('home/index', ['hideTopNav' => true]);
    }
}
