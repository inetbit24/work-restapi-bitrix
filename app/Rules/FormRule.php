<?php

namespace App\Rules;

use Psr\Http\Message\ServerRequestInterface as Request;

use App\Rules\InterfaceFactoryRule;
use Valitron\Validator;

class FormRule implements InterfaceFactoryRule
{
    public Validator $validator;

    public Request $request;

    public mixed $params;

    public function __construct(Request $request)
    {
        $this->request = $request;

        $this->params = (array) $request->getParsedBody();

        $resourceFiles = $this->request->getUploadedFiles();
        if (!empty($resourceFiles)) {
            foreach ($resourceFiles as $keyFile => $slimUploadFile) {
                $this->params[$keyFile] = !empty($this->params[$keyFile])
                    ? array_merge($this->params[$keyFile],$slimUploadFile)
                    : $slimUploadFile;
            }
        }

        $this->validator = new Validator($this->params);
    }

    public function validate(): array
    {
        if (!$this->validator->validate()) {
            foreach ($this->validator->errors() as $field => $error) {
                throw new \Exception(current($error));
            }
        }

        return $this->params;
    }
}
