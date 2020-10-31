<?php declare(strict_types = 1);

namespace Madsoft\App\Controller;

use Madsoft\App\Service\Config;
use Madsoft\App\Service\Template;

class ErrorPage
{
    private Config $config;
    private Template $template;

    public function __construct(Config $config, Template $template)
    {
        $this->config = $config;
        $this->template = $template;
    }

    /**
     * @return mixed
     */
    public function run()
    {
        $output = $this->template->create('error-page.html.php');
        $output->set('error', 'Request is not supported.');
        $output->set('base', $this->config->get('baseUrl'));
        return $output;
    }
}
