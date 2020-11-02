<?php declare(strict_types = 1);

namespace GyMadarasz\WebApp\Controller;

use GyMadarasz\WebApp\Service\Template;

class IndexPage
{
    public function viewIndex(Template $template): Template
    {
        return $template->create('index.html.php');
    }
}
