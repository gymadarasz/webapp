<?php declare(strict_types = 1);

namespace Madsoft\App\Controller;

use Madsoft\App\Service\Template;

class ErrorPage
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
        $output = $this->template->create('error-page.html.php');
        $output->set('error', 'Requested public page is not supported.');
        return $output;
    }
}
