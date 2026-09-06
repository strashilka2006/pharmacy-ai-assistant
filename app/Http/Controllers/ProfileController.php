<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/** Порт personal.php. */
class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();

        return view('profile.show', [
            'user' => $user,
            'orders' => $user->orders()->latest()->get(),
            'viewed' => $this->viewedProducts($request),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return back()->with('status', 'Профиль сохранён');
    }

    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            // mimes проверяет реальный тип файла, а не расширение —
            // это замена ручной возне с exif_imagetype() в оригинале
            'avatar' => ['required', 'image', 'mimes:jpeg,png,webp', 'max:4096'],
        ]);

        $user = $request->user();

        if ($user->avatar) {
            Storage::disk('public')->delete('avatars/' . $user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');

        $user->update(['avatar' => basename($path)]);

        return back()->with('status', 'Аватар обновлён');
    }

    /** «Вы недавно смотрели» из куки. */
    private function viewedProducts(Request $request)
    {
        $ids = array_filter(explode(',', (string) $request->cookie('viewed')));

        if (! $ids) {
            return collect();
        }

        $ids = array_slice($ids, 0, 8);

        return Product::query()
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn (Product $p) => array_search((string) $p->id, $ids, true))
            ->values();
    }
}
