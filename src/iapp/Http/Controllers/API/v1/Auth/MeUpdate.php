<?php


namespace iLaravel\iAuth\iApp\Http\Controllers\API\v1\Auth;

use Illuminate\Support\Facades\Hash;

trait MeUpdate
{
    public function me_update(\Illuminate\Http\Request $request)
    {
        $user = $this->model::find(auth()->id());
        if ($request->password) {
            $request->replace(['password' => Hash::make($request->password)]);
        }
        $update = [
            'name' => (string)$request->name,
            'family' => (string)$request->family,
            'website' => (string)$request->website,
            'gender' => (string)$request->gender
        ];
        if (isset($request->password))
            $update['password'] = $request->password;
        $avatar = $request->file('avatar');
        $request->files->remove('avatar');
        if ($avatar) {
            $attachment = File::upload($request, 'avatar');
            if ($attachment) {
                $update['avatar_id'] = $attachment->id;
                $this->statusMessage = $this->class_name() . " changed";
            }
            File::imageSize($attachment, 500);
            File::imageSize($attachment, 250);
            File::imageSize($attachment, 150);
        }
        $user->update($update);
        return $user;
    }
}
