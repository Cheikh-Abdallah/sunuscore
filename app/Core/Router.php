<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Routeur simple : associe méthode + URI à un callable.
 * Supporte les paramètres dynamiques {id}, {slug}.
 *
 * La comparaison se fait sur l'URI normalisée par Request::uri(),
 * qui a déjà retiré le préfixe du dossier public. Le routeur ne
 * gère donc que des chemins "propres" (/matches, /teams/3, ...).
 */
final class Router
{
    /** @var array<int, array{method:string, pattern:string, regex:string, handler:callable}> */
    private array $routes = [];

    public function get(string $pattern, callable $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, callable $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    public function put(string $pattern, callable $handler): void
    {
        $this->add('PUT', $pattern, $handler);
    }

    public function delete(string $pattern, callable $handler): void
    {
        $this->add('DELETE', $pattern, $handler);
    }

    private function add(string $method, string $pattern, callable $handler): void
    {
        // Construire la regex à l'avance (perf + clarté)
        // {id} devient (?P<id>[^/]+)
        $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        $this->routes[] = [
            'method'  => $method,
            'pattern' => $pattern,
            'regex'   => $regex,
            'handler' => $handler,
        ];
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $uri    = $request->uri();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            if (preg_match($route['regex'], $uri, $matches)) {
                // Ne garder que les paramètres nommés, dans l'ordre d'apparition
                $params = [];
                foreach ($matches as $key => $value) {
                    if (!is_int($key)) {
                        $params[] = $value;
                    }
                }
                call_user_func_array($route['handler'], $params);
                return;
            }
        }

        // Aucune route trouvée
        if ($request->isApi()) {
            Response::error('Ressource introuvable.', [], 404);
        }
        http_response_code(404);
        echo '404 - Page introuvable';
    }
}