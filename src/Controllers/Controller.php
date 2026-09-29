<?php
namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;

abstract class Controller
{
    protected $request;
    protected $data = [];

    public function __construct()
    {
        $this->request = new Request();
    }

    protected function view(string $name, array $data = []): void
    {
        extract($data);
        
        $viewPath = __DIR__ . '/../../views/' . str_replace('.', '/', $name) . '.php';
        
        if (!file_exists($viewPath)) {
            throw new \Exception("View [{$name}] not found");
        }
        
        include $viewPath;
    }

    protected function json($data, int $statusCode = 200): void
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    protected function redirect(string $url, int $statusCode = 302): void
    {
        header('Location: ' . $url, true, $statusCode);
        exit;
    }

    protected function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? base_url();
        $this->redirect($referer);
    }

    protected function withErrors(array $errors): self
    {
        $this->data['errors'] = $errors;
        return $this;
    }

    protected function validate(array $rules): array
    {
        $errors = [];
        
        foreach ($rules as $field => $ruleString) {
            $fieldRules = explode('|', $ruleString);
            $value = $this->request->getBody($field);
            
            foreach ($fieldRules as $rule) {
                $error = $this->validateRule($field, $value, $rule);
                if ($error) {
                    $errors[$field][] = $error;
                }
            }
        }
        
        return $errors;
    }

    private function validateRule(string $field, $value, string $rule): ?string
    {
        $parts = explode(':', $rule, 2);
        $ruleName = $parts[0];
        $ruleParam = $parts[1] ?? null;
        
        switch ($ruleName) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    return trans('validation.required', ['field' => trans($field)]);
                }
                break;
            
            case 'email':
                if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return trans('validation.email', ['field' => trans($field)]);
                }
                break;
            
            case 'numeric':
                if ($value && !is_numeric($value)) {
                    return trans('validation.numeric', ['field' => trans($field)]);
                }
                break;
            
            case 'min':
                if ($value && strlen($value) < $ruleParam) {
                    return trans('validation.min', ['field' => trans($field), 'min' => $ruleParam]);
                }
                break;
            
            case 'max':
                if ($value && strlen($value) > $ruleParam) {
                    return trans('validation.max', ['field' => trans($field), 'max' => $ruleParam]);
                }
                break;
            
            case 'unique':
                if ($value) {
                    $model = $ruleParam;
                    $modelInstance = new $model();
                    $existing = $modelInstance->firstWhere($field, $value);
                    if ($existing) {
                        return trans('validation.unique', ['field' => trans($field)]);
                    }
                }
                break;
            
            case 'exists':
                if ($value) {
                    $model = $ruleParam;
                    $modelInstance = new $model();
                    $existing = $modelInstance->find($value);
                    if (!$existing) {
                        return trans('validation.exists', ['field' => trans($field)]);
                    }
                }
                break;
        }
        
        return null;
    }

    protected function getUser()
    {
        return $_SESSION['user'] ?? null;
    }

    protected function getUserId()
    {
        return $_SESSION['user']['id'] ?? null;
    }

    protected function getUserOfficeId()
    {
        return $_SESSION['user']['office_id'] ?? null;
    }

    protected function isAdmin(): bool
    {
        return ($this->getUser()['role'] ?? '') === 'admin';
    }

    protected function isAgent(): bool
    {
        return ($this->getUser()['role'] ?? '') === 'agent';
    }

    protected function canAccessOffice($officeId): bool
    {
        if ($this->isAdmin()) {
            return true;
        }
        return $this->getUserOfficeId() == $officeId;
    }
}
