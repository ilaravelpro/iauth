<?php


namespace iLaravel\iAuth\Vendor;
use Carbon\Carbon;
use iLaravel\Core\iApp\Http\Requests\iLaravel as Request;
use iLaravel\iAuth\iApp\AuthTheory;
use Illuminate\Auth\AuthenticationException;

class AuthBridge
{
    public $user, $request, $method, $bridges, $theory;
    public function __construct(Request $request, $method, $user = null, $theory = 'auth')
    {
        $this->user = $user;
        $this->request = $request;
        $this->method = $method;
        $this->theory = $theory;
        $this->sortBridges();
    }

    public static function render(Request $request, $method, $user = null) {
        $self = new self($request, $method, $user);
        $methods = [];
        if (!count($self->bridges))
            throw new AuthenticationException('Not found Verify Method.');
        $theory = new AuthTheory();
        if (isset($self->user->id)) $theory->user_id = $self->user->id;
        $theory->theory = $self->theory;
        $theory->expired_at = isset($self->user->id) ? Carbon::now()->addMinutes(3) : Carbon::now()->addMinutes(10);
        $theory->key = $method;
        $theory->value = $request->input($method);
        $theory->save();
        if (in_array('mobile', $self->bridges)) {
            $theory->bridges()->create(['method' => 'mobile']);
            $methods[] = 'mobile';
        }
        if (in_array('email', $self->bridges)) {
            $theory->bridges()->create(['method' => 'email']);
            $methods[] = 'email';
        }
        return [$methods, $theory];
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
            return $bridge['status'] && in_array($this->theory, $bridge['theories']) && ($this->user ? isset($this->user->{$key}) && $this->user->{$key} : true);
        }, 1);
        return $key ? is_string($key) ? array_column($bridges, $key) : array_keys($bridges) : $bridges;
    }
}
