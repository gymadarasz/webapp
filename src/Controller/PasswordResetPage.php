<?php declare(strict_types = 1);

namespace Madsoft\App\Controller;

use Madsoft\App\Service\Template;

class PasswordResetPage
{
    private Template $template;

    public function __construct(Template $template)
    {
        $this->template = $template;
    }

    /**
     * @return mixed
     */
    public function run()
    {
        return $this->template->create('pwdreset.html.php');
    }
}
