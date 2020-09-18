<?php
declare(strict_types=1);

namespace TestApp\Controller;

use Cake\Controller\Controller;

class TestAppController extends Controller
{
    public function initialize(): void
    {
        $this->loadComponent('Authentication.Authentication');
    }
}
