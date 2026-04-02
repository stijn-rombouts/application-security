<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfilePictureController extends Controller
{
    /**
     * Display the specified profile picture.
     *
     * @param string $filename
     * @return \Illuminate\Http\Response
     */
    public function show($filename)
    {
        $disk = Storage::disk('profile_pictures');
        $user = Auth::user();

        switch (true) {
            case $user->isClient(): // Client can only view the profile picture of their mentor
                $mentor = User::find($user->mentor_id);
                if (
                    !$mentor ||
                    $filename !== $mentor->getRawProfilePictureName()
                ) {
                    abort(403);
                }
                break;

            case $user->isMentor(): // Mentor can only view their own profile picture
            case $user->isResearcher(): // Researcher can only view their own profile picture
                if ($filename !== $user->getRawProfilePictureName()) {
                    abort(403);
                }
                break;

            case $user->isAdmin(): // Admins can view their own profile picture and  all of the profile pictures of the mentors in their organisation
                if ($filename === $user->getRawProfilePictureName()) {
                    break;
                }
                $mentor = User::where('organisation_id', $user->organisation_id)
                    ->where('profile_picture_url', $filename)
                    ->whereHas('roles', function ($q) {
                        $q->where('role', Role::MENTOR);
                    })
                    ->first();
                if (!$mentor || $filename !== $mentor->getRawProfilePictureName()) {
                    abort(403);
                }
                break;

            case $user->isSuperAdmin(): // Superadmins can view their own profile picture and can view the profile pictures of all the admins
                if ($filename === $user->getRawProfilePictureName()) {
                    break;
                }
                $admin = User::where('organisation_id', $user->organisation_id)
                    ->where('profile_picture_url', $filename)
                    ->whereHas('roles', function ($q) {
                        $q->where('role', Role::ADMIN);
                    })
                    ->first();
                if (!$admin || $filename !== $admin->getRawProfilePictureName()) {
                    abort(403);
                }
                break;

            default:
                abort(403);
        }

        if (!$disk->exists($filename)) {
            abort(404);
        }

        $path = $disk->path($filename);
        return response()->file($path);
    }
}
