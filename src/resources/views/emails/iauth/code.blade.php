@component('mail::message', ['width' => '100%'])
    # {{ _t(ipreference("iauth.sessions.models.{$method}.title")) }}
@component('mail::panel')
    <strong>{{ _t('Dear :name,', ['name'=> $creator->fullname ? : _t('User')]) }}</strong>
    <div>{{ _t('Hello') }},</div>
    <div>{{ _t("Your ".ipreference("iauth.sessions.models.{$method}.message")." request was successful!") }}</div>
    <div>{{ _t("Disposable Code:") }} <strong>{{$bridge->pin}}</strong></div>
@endcomponent
    {{ _t('Thanks') }}.
@endcomponent
