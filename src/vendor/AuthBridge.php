<?php


namespace iLaravel\iAuth\Vendor;
use iLaravel\Core\iApp\Http\Requests\iLaravel as Request;
use Illuminate\Auth\AuthenticationException;

class AuthBridge
{
    public $model, $request, $method, $bridges, $theory;
    public function __construct(Request $request, $method, $model = null, $theory = 'auth')
    {
        $this->model = $model;
        $this->request = $request;
        $this->method = $method;
        $this->theory = $theory;
        $this->sortBridges();
    }

    public static function render(Request $request, $method, $model = null) {
        $self = new self($request, $method, $model);
        $methods = [];
        if (!count($self->bridges))
            throw new AuthenticationException('Not found Verify Method.');
        if (in_array('mobile', $self->bridges)) {

            $methods[] = 'mobile';
        }
        if (in_array('email', $self->bridges)) {
            $methods[] = 'email';
        }

        return $methods;
    }

    public function sortBridges() {
        $bridges = $activists = $this->getBridges(true);
        if (in_array(iauth('methods.verify.mode'), $activists))
            $bridges = [iauth('methods.verify.mode')];
        elseif (iauth('methods.verify.mode') !== 'all'){
            $bridges = [in_array($this->method, $activists) ? $this->method : iauth('methods.verify.other')];
            $bridges = count($bridges) ? $bridges : $activists;
        }
        $this->bridges = $bridges;
        return $bridges;
    }

    public function getBridges($key = false) {
        $bridges = array_filter(iauth('bridges'), function ($bridge, $key) {
            return $bridge['status'] && in_array($this->theory, $bridge['theories']) && ($this->model ? isset($this->model->{$key}) && $this->model->{$key} : true);
        }, 1);
        return $key ? is_string($key) ? array_column($bridges, $key) : array_keys($bridges) : $bridges;
    }
}
