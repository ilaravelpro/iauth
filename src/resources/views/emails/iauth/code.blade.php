@component('mail::message', ['width' => '100%'])
    # {{ _t("plugins.iauth.mail.sessions.{$method}.title") }}
@component('mail::panel')
    <strong>{{ _t('Dear :name,', ['name'=> $creator->fullname ? : _t('User')]) }}</strong>
    <div>{{ _t('Hello') }},</div>
    <div>{{ _t("plugins.iauth.mail.sessions.{$method}.messages") }}</div>
    <div>{{ _t("Disposable Code: :code", ['code' => $bridge->pin]) }}</div>
@endcomponent
    {{ _t('Thanks') }}.
@endcomponent
