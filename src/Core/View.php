<?php

namespace Framework\Core;

use eftec\bladeone\BladeOne;

class View
{
    private BladeOne $blade;
    private array $shared = [];

    public function __construct()
    {
        $viewPath = config("view.views");
        $cachePath = config("view.cache");

        $mode = config("app.debug")
            ? BladeOne::MODE_DEBUG
            : BladeOne::MODE_AUTO;

        $this->blade = new BladeOne($viewPath, $cachePath, $mode);
        $this->blade->setOptimize(false);

        $this->registerDirectives();
        $this->shareGlobalData();
    }

    public function render(string $view, array $data = []): string
    {
        $data = array_merge($this->shared, $data);
        return $this->blade->run($view, $data);
    }

    public function share(string $key, $value): void
    {
        $this->shared[$key] = $value;
    }

    private function registerDirectives(): void
    {
        // Diretiva @csrf
        $this->blade->directive("csrf", function () {
            return "<?php echo csrf_field(); ?>";
        });

        // Diretiva @old
        $this->blade->directive("old", function ($expression) {
            return "<?php echo old{$expression}; ?>";
        });
    }

    private function shareGlobalData(): void
    {
        $session = app("session");

        $this->share("errors", $session->getErrors());
        $this->share("old", $session->get("_old_input", []));

        // Flash messages
        if ($session->hasFlash("success")) {
            $this->share("success", $session->getFlash("success"));
        }
        if ($session->hasFlash("error")) {
            $this->share("error", $session->getFlash("error"));
        }
        if ($session->hasFlash("warning")) {
            $this->share("warning", $session->getFlash("warning"));
        }
        if ($session->hasFlash("info")) {
            $this->share("info", $session->getFlash("info"));
        }
    }
}
