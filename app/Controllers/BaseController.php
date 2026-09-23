<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;

abstract class BaseController
{
    /**
     * Render a view with layout
     */
    protected function render(string $view, array $data = [], ?string $layout = 'layouts/main'): Response
    {
        $html = View::render($view, $data, $layout);
        return new Response($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    /**
     * Return JSON response
     */
    protected function json(mixed $data, int $statusCode = 200, array $headers = []): Response
    {
        return Response::json($data, $statusCode, $headers);
    }

    /**
     * Return redirect response
     */
    protected function redirect(string $url, int $statusCode = 302): Response
    {
        return Response::redirect($url, $statusCode);
    }

    /**
     * Flash notification message and redirect
     */
    protected function redirectWith(string $url, string $type, string $message): Response
    {
        Session::flash($type, $message);
        return $this->redirect($url);
    }

    /**
     * Validate request inputs
     */
    protected function validate(Request $request, array $rules): array
    {
        $errors = [];
        $validated = [];

        foreach ($rules as $field => $ruleString) {
            $value = $request->input($field);
            $ruleList = is_array($ruleString) ? $ruleString : explode('|', $ruleString);

            foreach ($ruleList as $rule) {
                if ($rule === 'required' && (is_null($value) || $value === '')) {
                    $errors[$field][] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
                } elseif ($rule === 'email' && !empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field][] = 'A valid email address is required.';
                } elseif (str_starts_with($rule, 'min:') && !empty($value)) {
                    $min = (int) substr($rule, 4);
                    if (strlen((string) $value) < $min) {
                        $errors[$field][] = ucfirst(str_replace('_', ' ', $field)) . " must be at least {$min} characters.";
                    }
                } elseif (str_starts_with($rule, 'max:') && !empty($value)) {
                    $max = (int) substr($rule, 4);
                    if (strlen((string) $value) > $max) {
                        $errors[$field][] = ucfirst(str_replace('_', ' ', $field)) . " must not exceed {$max} characters.";
                    }
                }
            }

            if (!isset($errors[$field])) {
                $validated[$field] = is_string($value) ? trim($value) : $value;
            }
        }

        if (!empty($errors)) {
            Session::setOldInput($request->input());
            if ($request->isAjax()) {
                $this->json(['success' => false, 'errors' => $errors], 422)->send();
            } else {
                Session::flash('error', 'Please correct the validation errors below.');
                Session::set('_validation_errors', $errors);
                Response::redirect($_SERVER['HTTP_REFERER'] ?? '/')->send();
            }
            exit;
        }

        return $validated;
    }
}
