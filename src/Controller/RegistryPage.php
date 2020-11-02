<?php declare(strict_types = 1);

namespace GyMadarasz\WebApp\Controller;

use GyMadarasz\WebApp\Service\Template;

class RegistryPage
{
    public function viewRegistry(Template $template): Template
    {
        return $template->create('registry.html.php');
    }
}
