<?php

namespace Helio\Invest\Controller\Traits;

use Helio\Invest\Model\User;
use Slim\Http\Request;

/**
 * Trait ParametrizedController
 * @package Helio\Panel\Controller\Traits
 *
 * @property array params
 */
trait ParametrizedController
{

    /**
     * @var array
     */
    protected $params;


    /**
     * @return bool
     */
    public function setupParams(): bool
    {
        if (!$this->params) {
            $a = json_decode($this->request->getBody(), true) ?? [];
            $b = $this->request->getParams() ?? [];
            $c = array_merge($a,$b);
            $this->params = array_merge(json_decode($this->request->getBody(), true) ?? [], $this->request->getParams() ?? []);
        }
        return true;
    }

    /**
     * @param array $params Array formated like <parameter name> => <type>
     *
     * NOTE: Only call this with SANITIZE Filters, VALIDATE will fail.
     *
     * @throws \RuntimeException
     */
    public function requiredParameterCheck(array $params): void
    {
        $this->optionalParameterCheck($params);

        foreach ($params as $key => $type) {
            if (!array_key_exists($key, $this->params) || $this->params[$key] === '') {
                throw new \RuntimeException("Param ${key} not set", 1545654109);
            }
        }
    }

    /**
     * @param array $params Array formated like <parameter name> => <type>
     *
     * NOTE: Only call this with SANITIZE Filters, VALIDATE will fail.
     *
     * @throws \RuntimeException
     *
     * TODO: Properly filter Params
     */
    public function optionalParameterCheck(array $params): void
    {
        foreach ($params as $key => $type) {
            if (!array_key_exists($key, $this->params)) {
                break;
            }

            if (!\is_array($type)) {
                $type = [$type];
            }
            foreach ($type as $currentType) {
                $test = filter_var($this->params[$key], $currentType);
                if ($test === false) {
                    throw new \RuntimeException("Param ${key} resulted in filter error", 1545654117);
                }
                if ($test !== $this->params[$key]) {
                    throw new \RuntimeException("Param ${key} has invalid characters", 1545654122);
                }
            }
        }
    }
}